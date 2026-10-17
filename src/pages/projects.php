<?php
head('Projects', '/projects');
$cat = (string)($_GET['category'] ?? '');
$cats = entities()['projects']['fields']['category'][3];
$list = in_array($cat, $cats, true) ? q('SELECT * FROM projects WHERE category = ?', [$cat]) : q('SELECT * FROM projects');
?>
<div class="wrap"><h1>Church projects</h1>
<p class="tabs"><a href="/projects"<?= $cat ? '' : ' class="on"' ?>>All</a><?php foreach ($cats as $c) echo '<a href="/projects?category=' . urlencode($c) . '"' . ($c === $cat ? ' class="on"' : '') . '>' . e($c) . '</a>'; ?></p>
<?php foreach ($list as $p): $photos = array_filter(explode(',', (string)$p['photos'])); ?>
  <article class="card wide" id="project-<?= (int)$p['id'] ?>">
    <p class="muted"><?= e(implode(' | ', array_filter([$p['category'], $p['location'], $p['status']]))) ?></p>
    <h2><?= e($p['name']) ?></h2>
    <?php if ($photos): ?><div class="slides" data-slides>
      <?php foreach ($photos as $i => $ph): ?><img src="<?= e($ph) ?>" alt="<?= e($p['name']) ?> photo <?= $i + 1 ?>"<?= $i ? ' hidden' : '' ?>><?php endforeach; ?>
      <?php if (count($photos) > 1): ?><div class="slide-nav"><button type="button" data-prev aria-label="Previous photo">&lsaquo;</button><span data-count>1 / <?= count($photos) ?></span><button type="button" data-next aria-label="Next photo">&rsaquo;</button></div><?php endif; ?>
    </div><?php endif; ?>
    <?php foreach (preg_split('/\R{2,}/', trim((string)$p['description'])) as $para) if ($para !== '') echo '<p class="prose">' . nl2br(e($para)) . '</p>'; ?>
    <?php if ($p['coordinator']): ?><p><b>Coordinator:</b> <?= e($p['coordinator']) ?></p><?php endif; ?>
    <?php if (lines($p['updates'])): ?><h3>Project updates</h3><ul><?php foreach (lines($p['updates']) as $l): [$w, $n] = parts($l, 2); ?><li><span class="muted"><?= e($w) ?>:</span> <?= e($n) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <a class="btn sm" href="/give">Give</a>
  </article>
<?php endforeach; if (!$list): ?><p class="muted">No projects yet. Add one in the admin.</p><?php endif; ?>
</div>
<?php foot();
