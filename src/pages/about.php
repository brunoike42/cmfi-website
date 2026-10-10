<?php
// Shows an uploaded image, or a download button for a PDF. Returns '' when nothing was uploaded.
if (!function_exists('upload_view')) {
    function upload_view($f, string $label): string {
        $f = (string)$f; if ($f === '') return '';
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
            return '<a href="' . e($f) . '" target="_blank" rel="noopener"><img class="up-img" src="' . e($f) . '" alt="' . e($label) . '"></a>';
        if ($ext === 'pdf')
            return '<p><a class="btn sm ghost-dark" href="' . e($f) . '" download>Download ' . e($label) . ' (PDF)</a></p>';
        return '';
    }
}
head('About us', '/about');
$a = single('about');
$leaders = q('SELECT * FROM leaders ORDER BY sort_order, id');
?>
<div class="wrap"><h1>About us</h1>
<div class="two">
  <div><h2>Who we are</h2><p><?= nl2br(e($a['story'] ?? 'Add the church story in the admin under About page.')) ?></p></div>
  <ol class="timeline"><?php foreach (lines($a['timeline'] ?? '') as $l): [$y, $ev] = parts($l, 2); ?><li><b><?= e($y) ?></b> <?= e($ev) ?></li><?php endforeach; ?></ol>
</div>
<div class="grid three">
  <div class="card tint"><h2></h2>
    <?php if (trim((string)($a['vision'] ?? '')) !== ''): ?><p><?= nl2br(e($a['vision'])) ?></p><?php endif; ?>
    <?= upload_view($a['vision_file'] ?? '', 'Vision') ?>
    <?php if (trim((string)($a['vision'] ?? '')) === '' && empty($a['vision_file'])): ?><p class="muted">Not added yet.</p><?php endif; ?></div>
  <div class="card tint"><h2></h2>
    <?php if (trim((string)($a['mission'] ?? '')) !== ''): ?><p><?= nl2br(e($a['mission'])) ?></p><?php endif; ?>
    <?= upload_view($a['mission_file'] ?? '', 'Mission') ?>
    <?php if (trim((string)($a['mission'] ?? '')) === '' && empty($a['mission_file'])): ?><p class="muted">Not added yet.</p><?php endif; ?></div>
  <div class="card tint"><h2></h2>
    <?php if (lines($a['goals'] ?? '')): ?><ul><?php foreach (lines($a['goals']) as $g) echo '<li>' . e($g) . '</li>'; ?></ul><?php endif; ?>
    <?= upload_view($a['goals_file'] ?? '', 'Goals') ?>
    <?php if (!lines($a['goals'] ?? '') && empty($a['goals_file'])): ?><p class="muted">Not added yet.</p><?php endif; ?></div>
</div>
<?php if (lines($a['beliefs'] ?? '')): ?><h2>What we believe</h2><div class="grid">
<?php foreach (lines($a['beliefs']) as $l): [$t, $s] = parts($l, 2); ?><div class="card"><h3><?= e($t) ?></h3><p class="muted"><?= e($s) ?></p></div><?php endforeach; ?></div><?php endif; ?>
<h2>Leadership</h2><div class="grid three">
<?php foreach ($leaders as $l): ?>
  <article class="card">
    <?php if ($l['photo']): ?><img class="avatar" src="<?= e($l['photo']) ?>" alt="<?= e($l['name']) ?>">
    <?php else: ?><div class="avatar ph" aria-hidden="true"><?= e(implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(explode(' ', $l['name']), 0, 2)))) ?></div><?php endif; ?>
    <h3><?= e($l['name']) ?></h3><p class="muted"><?= e($l['position']) ?></p><p><?= e($l['bio']) ?></p>
    <ul><?php foreach (lines($l['responsibilities']) as $r) echo '<li>' . e($r) . '</li>'; ?></ul>
  </article>
<?php endforeach; ?></div>
<?php if (!$leaders): ?><p class="muted">Add leaders in the admin to show them here.</p><?php endif; ?>
</div>
<?php foot();