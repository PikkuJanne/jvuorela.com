<?php
/** Loopback development router only. SPDX-License-Identifier: GPL-2.0-or-later */
$root = getenv('JVT_WP_ROOT');
if (!$root || !is_file($root . '/wp-load.php')) { http_response_code(503); exit('Local WordPress root unavailable'); }
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = realpath($root . $path);
if ($path !== '/' && $file && str_starts_with(str_replace('\\', '/', $file), rtrim(str_replace('\\', '/', realpath($root)), '/') . '/') && (is_file($file) || (is_dir($file) && is_file($file . '/index.php')))) { return false; }
require $root . '/index.php';
