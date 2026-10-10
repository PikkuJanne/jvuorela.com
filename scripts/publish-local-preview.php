<?php
/** Disposable loopback preview only. SPDX-License-Identifier: GPL-2.0-or-later */
if (!defined('WP_CLI') || !WP_CLI || !current_user_can('manage_options') || !in_array(wp_parse_url(home_url('/'), PHP_URL_HOST), ['127.0.0.1','localhost','::1'], true)) { throw new RuntimeException('Only disposable loopback preview may use this helper.'); }
update_option('blog_public', 0);
$pages = get_posts(['post_type'=>'page','post_status'=>'draft','numberposts'=>-1,'meta_key'=>'_jvt_content_id']);
foreach ($pages as $page) { wp_update_post(['ID'=>$page->ID,'post_status'=>'publish']); }
WP_CLI::success('Project drafts visible only on local loopback; search indexing remains disabled.');
