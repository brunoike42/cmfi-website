<?php
declare(strict_types=1);
$config = require __DIR__ . '/../config.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

function cfg(string $k) { global $config; return $config[$k]; }
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function entities(): array { static $e; return $e ??= require __DIR__ . '/entities.php'; }

function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $c = cfg('mysql');
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
    $dsn = fn(bool $withDb) => 'mysql:host=' . $c['host'] . ';port=' . $c['port'] . ($withDb ? ';dbname=' . $c['name'] : '') . ';charset=utf8mb4';
    try {
        try {
            $pdo = new PDO($dsn(true), $c['user'], $c['pass'], $opts);
        } catch (PDOException $ex) {
            if (!str_contains($ex->getMessage(), 'Unknown database')) throw $ex;
            // First run: create the database, then connect to it.
            $tmp = new PDO($dsn(false), $c['user'], $c['pass'], $opts);
            $tmp->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', $c['name']) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo = new PDO($dsn(true), $c['user'], $c['pass'], $opts);
        }
         $sig = md5_file(__DIR__ . '/entities.php');
   if (($_SESSION['schema_ok'] ?? '') !== $sig) { install($pdo); $_SESSION['schema_ok'] = $sig; }
    } catch (PDOException $ex) {
        error_log('DB error: ' . $ex->getMessage());
        http_response_code(500);
        exit('Could not connect to the database. Check config.php and that MySQL is running.' . (cfg('debug') ? '<br><br>' . e($ex->getMessage()) : ''));
    }
    return $pdo;
}
function install(PDO $pdo): void {
    $tail = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $pdo->exec('CREATE TABLE IF NOT EXISTS `users` (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL)' . $tail);
    $type = fn(array $s) => in_array($s[0], ['number', 'check']) ? 'INT NULL' : 'TEXT NULL';
    foreach (entities() as $t => $d) {
        $cols = ['id INT AUTO_INCREMENT PRIMARY KEY', 'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP'];
        foreach ($d['fields'] as $f => $s) $cols[] = "`$f` " . $type($s);
        $pdo->exec("CREATE TABLE IF NOT EXISTS `$t` (" . implode(',', $cols) . ')' . $tail);
        $have = array_column($pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(), 'Field');
        foreach ($d['fields'] as $f => $s) if (!in_array($f, $have, true)) $pdo->exec("ALTER TABLE `$t` ADD COLUMN `$f` " . $type($s));
    }
}

function q(string $sql, array $p = []): array { $s = db()->prepare($sql); $s->execute($p); return $s->fetchAll(); }
function one(string $sql, array $p = []): array { return q($sql, $p)[0] ?? []; }
function run(string $sql, array $p = []): void { db()->prepare($sql)->execute($p); }
function single(string $t): array { return one("SELECT * FROM $t WHERE id = 1"); }

function redirect(string $to): never { header('Location: ' . $to); exit; }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . csrf() . '">'; }
function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['_csrf'] ?? ''))) { http_response_code(400); exit('Session expired. Go back and try again.'); }
}
function flash(string $msg, string $type = 'ok'): void { $_SESSION['flash'][] = [$type, $msg]; }
function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

function lines($t): array { return array_values(array_filter(array_map('trim', preg_split('/\R/', (string)$t)), 'strlen')); }
function parts($line, int $n): array { return array_pad(array_map('trim', explode('|', (string)$line, $n)), $n, ''); }
function yt_id($url): ?string { return preg_match('~(?:v=|youtu\.be/|embed/|live/)([\w-]{11})~', (string)$url, $m) ? $m[1] : null; }
function fmt_date($d, bool $time = false): string { return $d ? date($time ? 'j M Y, g:i A' : 'j M Y', strtotime((string)$d)) : ''; }

// Shrinks a freshly uploaded picture: max 1600px wide, JPEG for photos and opaque PNGs. Transparent PNGs stay PNG.
// Returns the (possibly new) file path. If anything is unavailable or fails, the original file is kept untouched.
function optimize_image(string $path, int $max = 1600): string {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'], true) || !function_exists('imagecreatefromjpeg') || filesize($path) < 200 * 1024) return $path;
    $im = $ext === 'png' ? @imagecreatefrompng($path) : @imagecreatefromjpeg($path);
    if (!$im) return $path;
    if ($ext !== 'png' && function_exists('exif_read_data')) {            // phone photos: keep them the right way up
        $o = @exif_read_data($path)['Orientation'] ?? 1;
        $rot = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
        if ($rot && ($r = imagerotate($im, $rot, 0))) { imagedestroy($im); $im = $r; }
    }
    if (imagesx($im) > $max) { $s = imagescale($im, $max); if ($s) { imagedestroy($im); $im = $s; } }
    $alpha = false;
    if ($ext === 'png') {
        $w = imagesx($im); $h = imagesy($im);
        for ($y = 0; $y < $h && !$alpha; $y += max(1, intdiv($h, 40))) for ($x = 0; $x < $w; $x += max(1, intdiv($w, 40)))
            if ((imagecolorat($im, $x, $y) >> 24) & 0x7F) { $alpha = true; break; }
    }
    $tmp = $path . '.tmp'; $out = $path;
    if ($alpha) { imagealphablending($im, false); imagesavealpha($im, true); imagepng($im, $tmp, 9); }
    else {
        $bg = imagecreatetruecolor(imagesx($im), imagesy($im)); imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im)); imagedestroy($im); $im = $bg;
        imagejpeg($im, $tmp, 80); $out = preg_replace('/\.(png|jpeg)$/i', '.jpg', $path);
    }
    imagedestroy($im);
    if (is_file($tmp) && filesize($tmp) < filesize($path)) { @unlink($path); rename($tmp, $out); return $out; }
    @unlink($tmp); return $path;
}

function save_upload(array $f, array $ok): ?string {
    if (($f['error'] ?? 4) !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $ok, true) || $f['size'] > 50 * 1024 * 1024) return null;
    @mkdir(cfg('uploads'), 0775, true);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = cfg('uploads') . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) return null;
    return '/uploads/' . basename(optimize_image($dest));
    }
