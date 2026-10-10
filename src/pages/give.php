<?php
head('Give');
$g = single('giving');
?>
<div class="wrap"><h1>Give</h1><?php if (!empty($g['intro'])): ?><p class="prose"><?= nl2br(e($g['intro'])) ?></p><?php endif; ?>
<h2>Where your gift goes</h2><div class="grid">
<?php foreach (lines($g['funds'] ?? '') as $l): [$n, $d, $u] = parts($l, 3); ?>
  <article class="card tint"><h3><?= e($n) ?></h3><p class="muted"><?= e($d) ?></p>
  <?php if (preg_match('~^https?://~i', $u)): ?><a class="btn sm" href="<?= e($u) ?>" target="_blank" rel="noopener noreferrer">Give online</a><?php endif; ?></article>
<?php endforeach; ?></div>
<?php if (!lines($g['funds'] ?? '')): ?><p class="muted">Add funds in the admin under Giving.</p><?php endif; ?>
<h2>Ways to give</h2><div class="grid">
<?php foreach (lines($g['methods'] ?? '') as $l): [$t, $lab, $acc, $num, $ins] = parts($l, 5); ?>
  <article class="card"><p class="muted"><?= e($t) ?></p><h3><?= e($lab) ?></h3><p class="big"><?= e($num) ?></p>
  <?php if ($acc): ?><p>Account name: <?= e($acc) ?></p><?php endif; ?><p class="muted"><?= e($ins) ?></p></article>
<?php endforeach; ?></div>
<?php if (!lines($g['methods'] ?? '')): ?><p class="muted">Add mobile money or bank details in the admin under Giving.</p><?php endif; ?>
</div>
<?php foot();
