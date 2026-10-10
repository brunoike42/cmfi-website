<?php
head('Ministries', '/ministries');
$list = q('SELECT * FROM ministries ORDER BY sort_order, id');
?>
<div class="wrap"><h1>Ministries</h1><p class="muted">Find a place to belong, serve and grow.</p>
<div class="grid">
<?php foreach ($list as $m): ?>
  <article class="card">
    <?php if ($m['image']): ?><img class="cover" src="<?= e($m['image']) ?>" alt="<?= e($m['name']) ?>"><?php endif; ?>
    <h2><?= e($m['name']) ?></h2>
    <?php if ($m['target_group']): ?><p class="muted">For <?= e($m['target_group']) ?></p><?php endif; ?>
    <p><?= e($m['description']) ?></p>
    <?php if ($m['leader']): ?><p><b>Leader:</b> <?= e($m['leader']) ?></p><?php endif; ?>
    <?php if ($m['meeting_day'] || $m['meeting_time']): ?><p><b>Meets:</b> <?= e(trim($m['meeting_day'] . ', ' . $m['meeting_time'], ', ')) ?></p><?php endif; ?>
    <?php if ($m['location']): ?><p><b>Where:</b> <?= e($m['location']) ?></p><?php endif; ?>
    <?php if ($m['contact']): ?><p><b>Contact:</b> <?= e($m['contact']) ?></p><?php endif; ?>
    <ul><?php foreach (lines($m['activities']) as $a) echo '<li>' . e($a) . '</li>'; ?></ul>
    <details><summary>Join this ministry</summary>
      <?php form_open('ministry_interests', ['ministry' => $m['name']]); field('name', 'Full name'); field('phone', 'Phone'); field('email', 'Email', 'email'); ?>
      <button class="btn">Join this ministry</button></form>
    </details>
  </article>
<?php endforeach; ?></div>
<?php if (!$list): ?><p class="muted">No ministries yet. Add one in the admin.</p><?php endif; ?>
</div>
<?php foot();
