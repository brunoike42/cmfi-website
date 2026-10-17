<?php
// Makes MP4 videos start playing sooner WITHOUT changing quality.
// It only moves the file's index to the front ("-c copy" = no re-encoding, the picture and sound are copied bit for bit).
// Run once:  C:\xampp1\php\php.exe tools\faststart.php
// Needs FFmpeg (ffmpeg.exe on PATH, or put its full path in $ffmpeg below). Make a backup of public\uploads first.
$ffmpeg = 'ffmpeg';
$dir = __DIR__ . '/../public/uploads';
exec(escapeshellarg($ffmpeg) . ' -version 2>&1', $o, $rc);
if ($rc !== 0) exit("FFmpeg was not found. Install it (ffmpeg.org) and add it to PATH, or set \$ffmpeg at the top of this file to its full path.\n");

// true = the index is already at the front, false = it is at the end (slow start), null = could not tell
function moov_first(string $p): ?bool {
    $fh = @fopen($p, 'rb'); if (!$fh) return null;
    $size = filesize($p); $pos = 0;
    while ($pos + 8 <= $size) {
        fseek($fh, $pos); $h = fread($fh, 8); if (strlen($h) < 8) break;
        $len = unpack('N', substr($h, 0, 4))[1]; $type = substr($h, 4, 4);
        if ($type === 'moov') { fclose($fh); return true; }
        if ($type === 'mdat') { fclose($fh); return false; }
        if ($len === 1) { $len = unpack('J', fread($fh, 8))[1]; } elseif ($len === 0) break;
        if ($len < 8) break;
        $pos += $len;
    }
    fclose($fh); return null;
}

$done = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
    $p = $f->getPathname();
    if (!in_array(strtolower(pathinfo($p, PATHINFO_EXTENSION)), ['mp4', 'm4v'], true)) continue;
    $state = moov_first($p);
    if ($state !== false) { echo basename($p), $state === true ? ": already fast, skipped\n" : ": could not read, skipped\n"; continue; }
    $tmp = $p . '.fast.mp4';
    $cmd = escapeshellarg($ffmpeg) . ' -y -v error -i ' . escapeshellarg($p) . ' -map 0:v? -map 0:a? -c copy -movflags +faststart -f mp4 ' . escapeshellarg($tmp) . ' 2>&1';
    exec($cmd, $out, $rc);
    if ($rc === 0 && is_file($tmp) && filesize($tmp) > filesize($p) * 0.95 && moov_first($tmp) === true) {
        rename($tmp, $p); $done++; echo basename($p), ": fixed (", round(filesize($p) / 1048576, 1), " MB, quality unchanged)\n";
    } else { @unlink($tmp); echo basename($p), ": FFmpeg could not process it, original kept\n"; }
}
echo "Done. $done video(s) fixed.\n";