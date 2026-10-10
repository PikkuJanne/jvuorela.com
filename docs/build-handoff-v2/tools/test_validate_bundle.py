"""Meaningful negative checks for the handoff validator; standard library only."""
from pathlib import Path
import importlib.util
import json
import shutil
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location('validator', ROOT/'tools/validate_bundle.py')
v = importlib.util.module_from_spec(spec)
spec.loader.exec_module(v)

class ValidationTests(unittest.TestCase):
    def setUp(self):
        self.temp=tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.root=Path(self.temp.name)/'bundle'
        shutil.copytree(ROOT,self.root,ignore=shutil.ignore_patterns('__pycache__','*.pyc'))
    def edit(self,name,fn):
        p=self.root/name; d=json.loads(p.read_text());fn(d);p.write_text(json.dumps(d))
    def failures(self,integrity=False):
        return v.validate(self.root,integrity=integrity)[0]
    def test_shipped_structure(self):
        self.assertEqual(self.failures(),[])
    def test_dependency_cycle(self):
        self.edit('TASKS.json',lambda d:d['tasks'][0]['depends_on'].append('T23'))
        self.assertTrue(any('cycle' in e for e in self.failures()))
    def test_wrong_repository_alias(self):
        self.edit('content/tools.json',lambda d:d['tools'][5].update(repo_url='https://github.com/PikkuJanne/WinImageNormalizer'))
        self.assertTrue(any('repository alias' in e for e in self.failures()))
    def test_missing_required_art(self):
        self.edit('assets/manifest.json',lambda d:d['slots'].pop(0))
        self.assertTrue(any('slot' in e for e in self.failures()))
    def test_mapped_asset_belongs_to_wrong_tool(self):
        self.edit('content/tools.json',lambda d:d['tools'][0]['asset_slots'].update(hero='winpdfmerger-character'))
        self.assertTrue(any('wrong asset ownership' in e for e in self.failures()))
    def test_missing_acceptance(self):
        self.edit('ACCEPTANCE.json',lambda d:d['cases'].pop())
        self.assertTrue(any('acceptance mapping' in e for e in self.failures()))
    def test_source_path_escape(self):
        self.edit('assets/manifest.json',lambda d:d['slots'][0].update(source_file='../outside.png'))
        self.assertTrue(any('unsafe source path' in e for e in self.failures()))
    def test_tampered_manifest_payload(self):
        (self.root/'START_HERE.md').write_text('Changed')
        self.assertTrue(any('hash mismatch START_HERE.md' in e for e in self.failures(integrity=True)))
    def test_manifest_path_escape(self):
        self.edit('BUNDLE_MANIFEST.json',lambda d:d['files'].append({'path':'../outside','size_bytes':0,'sha256':'0'*64}))
        self.assertTrue(any('unsafe path' in e for e in self.failures(integrity=True)))
    def test_public_github_cannot_be_optional(self):
        self.edit('content/site.json',lambda d:d['repository_policy'].update(visibility_required='private'))
        self.assertTrue(any('public open-source' in e for e in self.failures()))
    def test_tool_repository_art_forbidden(self):
        self.edit('assets/manifest.json',lambda d:d.update(tool_repository_assets_allowed=True))
        self.assertTrue(any('sources forbidden' in e for e in self.failures()))
    def test_unlisted_source_rejected(self):
        self.edit('assets/manifest.json',lambda d:d['slots'][0].update(source_file='PS Visuals/old.png'))
        self.assertTrue(any('owner inventory' in e for e in self.failures()))
    def test_cleanup_cannot_rewrite_history(self):
        self.edit('repositories/tool-cleanup.json',lambda d:d.update(history_rewrite_authorized=True))
        self.assertTrue(any('destructive scope' in e for e in self.failures()))
    def test_cleanup_repo_alias_preserved(self):
        self.edit('repositories/tool-cleanup.json',lambda d:d['repositories'][5].update(repo_url='https://github.com/PikkuJanne/WinImageNormalizer'))
        self.assertTrue(any('cleanup: wrong repository' in e for e in self.failures()))
    def test_final_handover_depends_on_cleanup(self):
        self.edit('TASKS.json',lambda d:next(t for t in d['tasks'] if t['id']=='T23')['depends_on'].remove('T27'))
        self.assertTrue(any('final handover must depend' in e for e in self.failures()))
    def test_false_asset_verification_rejected(self):
        self.edit('assets/manifest.json',lambda d:d['slots'][0]['verification'].update(status='verified'))
        self.assertTrue(any('verified without source evidence' in e for e in self.failures()))

if __name__ == '__main__':
    unittest.main()
