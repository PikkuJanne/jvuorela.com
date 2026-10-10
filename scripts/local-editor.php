<?php
/** Disposable editor acceptance account. SPDX-License-Identifier: GPL-2.0-or-later */
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'http://127.0.0.1:8088') { exit(1); }
$password = wp_generate_password(32, false);
$user = get_user_by('login', 'jvt-editor');
if ($user) { $id = $user->ID; wp_set_password($password, $id); }
else { $id = wp_insert_user(['user_login'=>'jvt-editor','user_pass'=>$password,'user_email'=>'editor@example.invalid','role'=>'editor']); }
if (is_wp_error($id)) { WP_CLI::error('Disposable editor creation failed.'); }
file_put_contents(dirname(__DIR__) . '/private/editor-auth.json', wp_json_encode(['username'=>'jvt-editor','password'=>$password]));
WP_CLI::success('Local editor account ready; credentials stored in ignored private file.');
