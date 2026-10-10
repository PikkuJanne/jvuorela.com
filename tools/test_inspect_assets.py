"""Source-boundary and corrupted/ambiguous input regression checks; synthetic fixtures only."""
from pathlib import Path
import importlib.util
import json
import struct
import subprocess
import sys
import tempfile
import unittest
import zlib

ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location('intake', ROOT/'tools/inspect_assets.py')
m = importlib.util.module_from_spec(spec); spec.loader.exec_module(m)


def png(width=1):
    def chunk(kind, body):
        return struct.pack('>I', len(body))+kind+body+struct.pack('>I', zlib.crc32(kind+body)&0xffffffff)
    return m.PNG_MAGIC+chunk(b'IHDR',struct.pack('>IIBBBBB',width,1,8,6,0,0,0))+chunk(b'IDAT',zlib.compress(b'\0'+b'\0\0\0\xff'*width))+chunk(b'IEND',b'')


class IntakeTests(unittest.TestCase):
    def setUp(self):
        self.tmp=tempfile.TemporaryDirectory();self.addCleanup(self.tmp.cleanup);self.root=Path(self.tmp.name)
        (self.root/'Website Assets').mkdir();(self.root/'PS Visuals').mkdir()
        self.file=self.root/'Website Assets/test.png';self.file.write_bytes(png())
        self.manifest={'expected_source_file_count':1,'slots':[{'slot_id':'test','source_file':'Website Assets/test.png','source_aliases':[],'expected_source_bytes':len(png())}]}
    def test_valid_read_only_intake(self):
        before=self.file.read_bytes();r=m.inspect(self.root,self.manifest)
        self.assertFalse(r['errors']);self.assertFalse(r['warnings']);self.assertEqual(self.file.read_bytes(),before)
        self.assertTrue(r['results'][0]['metadata']['alpha_channel_or_transparency_chunk'])
    def test_missing_source_has_no_fallback(self):
        self.file.unlink();self.assertTrue(m.inspect(self.root,self.manifest)['errors'])
    def test_corrupt_crc_rejected(self):
        b=bytearray(png());b[-1]^=1;self.file.write_bytes(b)
        self.assertTrue(any('CRC' in e for e in m.inspect(self.root,self.manifest)['errors']))
    def test_escape_rejected(self):
        self.manifest['slots'][0]['source_file']='Website Assets/../elsewhere.png'
        self.assertTrue(m.inspect(self.root,self.manifest)['errors'])
    def test_repository_source_rejected(self):
        self.manifest['slots'][0]['source_file']='WinBookSplit/assets/test.png'
        self.assertTrue(m.inspect(self.root,self.manifest)['errors'])
    def test_symlink_rejected(self):
        target=self.root/'outside.png';target.write_bytes(png());self.file.unlink()
        try:self.file.symlink_to(target)
        except OSError:self.skipTest('symlink creation not supported')
        self.assertTrue(any('symlink' in e for e in m.inspect(self.root,self.manifest)['errors']))
    def test_corrected_alias_accepted(self):
        alias=self.root/'Website Assets/alias.png';self.file.rename(alias)
        self.manifest['slots'][0]['source_aliases']=['Website Assets/alias.png']
        self.assertFalse(m.inspect(self.root,self.manifest)['errors'])
    def test_conflicting_alias_rejected(self):
        (self.root/'Website Assets/alias.png').write_bytes(png(2))
        self.manifest['slots'][0]['source_aliases']=['Website Assets/alias.png']
        self.assertTrue(any('conflicting' in e for e in m.inspect(self.root,self.manifest)['errors']))
    def test_size_change_requires_review(self):
        self.manifest['slots'][0]['expected_source_bytes']=1
        self.assertEqual(m.inspect(self.root,self.manifest)['status'],'REVIEW')
    def test_ico_png_and_bounds(self):
        b=png();ico=struct.pack('<HHH',0,1,1)+struct.pack('<BBBBHHII',1,1,0,0,1,32,len(b),22)+b
        self.assertEqual(m.ico_metadata(ico)['embedded_images'][0]['width'],1)
        with self.assertRaises(ValueError):m.ico_metadata(ico[:-1])
    def test_report_cannot_overwrite_source(self):
        manifest=self.root/'manifest.json';manifest.write_text(json.dumps(self.manifest))
        before=self.file.read_bytes()
        p=subprocess.run([sys.executable,str(ROOT/'tools/inspect_assets.py'),'--asset-root',str(self.root),'--manifest',str(manifest),'--output',str(self.file)],capture_output=True)
        self.assertNotEqual(p.returncode,0);self.assertEqual(self.file.read_bytes(),before)


if __name__=='__main__':unittest.main()
