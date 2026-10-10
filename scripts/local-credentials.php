<?php
/** Disposable local test account only. SPDX-License-Identifier: GPL-2.0-or-later */
if (!defined('WP_CLI') || !WP_CLI || !str_starts_with(home_url(), 'http://127.0.0.1:')) { exit(1); }
$pass = wp_generate_password(32, false);
wp_set_password($pass, 1);
$file = dirname(__DIR__) . '/private/local-auth.json';
file_put_contents($file, wp_json_encode(['username'=>'local-owner','password'=>$pass]));
WP_CLI::success('Disposable local credentials rotated and stored in ignored private storage.');
