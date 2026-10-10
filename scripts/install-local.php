<?php
/** First disposable local install only. SPDX-License-Identifier: GPL-2.0-or-later */
if (!defined('WP_CLI') || !WP_CLI || !getenv('JVT_LOCAL_PASSWORD')) { exit(1); }
$port = (int) getenv('JVT_LOCAL_PORT');
if ($port < 1 || $port > 65535) { WP_CLI::error('A valid loopback setup port is required.'); }
$url = 'http://127.0.0.1:' . $port;
define('WP_INSTALLING', true);
define('WP_SITEURL', $url);
require_once dirname(__DIR__) . '/private/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
if (is_blog_installed()) { WP_CLI::error('Existing database preserved; install refused.'); }
wp_install('jvuorela.com', 'local-owner', 'owner@example.invalid', false, '', getenv('JVT_LOCAL_PASSWORD'));
update_option('siteurl', $url);
update_option('home', $url);
if (!is_blog_installed()) { WP_CLI::error('Local database installation did not complete.'); }
if (file_put_contents(dirname(__DIR__) . '/private/local-auth.json', wp_json_encode(['username'=>'local-owner','password'=>getenv('JVT_LOCAL_PASSWORD')])) === false) { WP_CLI::error('Unable to save local development credentials privately.'); }
WP_CLI::success('Loopback-only site installed; credentials in ignored private/local-auth.json.');
