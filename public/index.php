<?php
declare(strict_types=1);
$path = rtrim((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . $path)) return false;

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

if (str_starts_with($path, '/admin')) { require __DIR__ . '/../src/admin.php'; exit; }
if (preg_match('#^/submit/([a-z_]+)$#', $path, $m)) { require __DIR__ . '/../src/submit.php'; exit; }

$pages = ['/' => 'home', '/join-us' => 'join-us', '/about' => 'about', '/ministries' => 'ministries', '/resources' => 'resources',
          '/livestream' => 'livestream', '/events' => 'events', '/projects' => 'projects', '/prayer' => 'prayer',
          '/give' => 'give', '/contact' => 'contact'];
if (!isset($pages[$path])) { http_response_code(404); head('Not found'); echo '<div class="wrap"><h1>Page not found</h1><p><a href="/">Go home</a></p></div>'; foot(); exit; }
require __DIR__ . '/../src/pages/' . $pages[$path] . '.php';
