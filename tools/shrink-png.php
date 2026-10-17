<?php
// Run once:  C:\xampp1\php\php.exe tools\shrink-png.php
// Big PNG pictures without transparency are re-saved as JPEG data under the SAME file name,
// so no database or page needs to change (browsers read the real image type, not the name).
// Pictures with transparency (logos, icons) are left alone. Make a backup of public\uploads first.
if (!function_exists('imagecreatefrompng')) { exit("GD is not enabled.\n"); }
$dir = __DIR__ . '/../public/uploads'; $max = 1600; $min = 300 * 1024; $saved = 0;
function has_alpha($im): bool {
    $w = imagesx($im); $h = imagesy($im);
    for ($y = 0; $y < $h; $y += max(1, intdiv($h, 40))) for ($x = 0; $x < $w; $x += max(1, intdiv($w, 40)))
        if ((imagecolorat($im, $x, $y) >> 24) & 0x7F) return true;
    return false;
}
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
    $p = $f->getPathname();
    if (strtolower(pathinfo($p, PATHINFO_EXTENSION)) !== 'png') continue;
    $before = filesize($p); if ($before < $min) continue;
    $info = @getimagesize($p); if (!$info || $info[2] !== IMAGETYPE_PNG) continue;   // already converted earlier
    $im = @imagecreatefrompng($p); if (!$im) continue;
    if (has_alpha($im)) { imagedestroy($im); echo basename($p), ": has transparency, skipped\n"; continue; }
    if (imagesx($im) > $max) { $s = imagescale($im, $max); imagedestroy($im); $im = $s; }
    $bg = imagecreatetruecolor(imagesx($im), imagesy($im)); imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
    imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im)); imagedestroy($im);
    $tmp = $p . '.tmp'; imagejpeg($bg, $tmp, 80); imagedestroy($bg);
    if (filesize($tmp) < $before) { $after = filesize($tmp); rename($tmp, $p); $saved += $before - $after; echo basename($p), ': ', round($before / 1024), ' KB -> ', round($after / 1024), " KB\n"; }
    else unlink($tmp);
}
echo 'Saved ', round($saved / 1048576, 1), " MB\n";