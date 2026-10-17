<?php
// Run once: C:\xampp1\php\php.exe tools\shrink.php
// Needs the GD extension (php.ini: extension=gd). Keeps file names, so nothing breaks.
$dirs = [__DIR__ . '/../public/uploads', __DIR__ . '/../public/assets'];
$max = 1600; $saved = 0;
foreach ($dirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        $p = $f->getPathname(); $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) continue;
        $before = filesize($p); if ($before < 300 * 1024) continue;
        $im = $ext === 'png' ? @imagecreatefrompng($p) : @imagecreatefromjpeg($p);
        if (!$im) continue;
        imagealphablending($im, false); imagesavealpha($im, true);
        if (imagesx($im) > $max) { $s = imagescale($im, $max); imagedestroy($im); $im = $s; imagesavealpha($im, true); }
        $tmp = $p . '.tmp';
        $ext === 'png' ? imagepng($im, $tmp, 9) : imagejpeg($im, $tmp, 78);
        imagedestroy($im);
        if (filesize($tmp) < $before) { rename($tmp, $p); $saved += $before - filesize($p); echo basename($p), ': ', round($before / 1024), ' KB -> ', round(filesize($p) / 1024), " KB\n"; }
        else unlink($tmp);
    }
}
echo 'Saved ', round($saved / 1048576, 1), " MB\n";