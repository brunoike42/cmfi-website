<?php
require_once __DIR__ . '/../about_topics.php';
head('About us', '/about');
$a = single('about'); $topics = about_topics();
$intro = trim((string)($a['about_intro'] ?? '')) ?: 'Discover our story, vision, mission and the work God has entrusted to us.';
?>
<div class="wrap"><h1>About us</h1>
<p class="aintro"><?= e($intro) ?></p>
<div class="atg">
<?php foreach ($topics as $t): ?>
  <a class="atc" href="/about/<?= e($t['slug']) ?>">
    <span class="atimg"><?php if ($t['image']): ?><img src="<?= e($t['image']) ?>" alt=""><?php else: ?>
      <span class="rph"><?= e(mb_strtoupper(mb_substr(trim((string)(preg_split('/\s+/', trim($t['title'])) ?: [''])[count(preg_split('/\s+/', trim($t['title'])) ?: [''])-1]), 0, 1))) ?></span><?php endif; ?></span>
    <span class="atb"><b><?= e($t['title']) ?></b>
      <?php if ($t['summary'] !== ''): ?><span class="atsum"><?= e($t['summary']) ?></span><?php endif; ?>
      <i>Read about <?= e($t['title']) ?> &rarr;</i></span>
  </a>
<?php endforeach; ?>
</div>
<?php if (!$topics): ?><p class="muted">All About topics are hidden. Show them again in the admin under About topics.</p><?php endif; ?>
</div>
<?php foot(); ?>