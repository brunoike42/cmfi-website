<?php
head('Join us', '/join-us');
$qs = trim((string)($_GET['q'] ?? '')); $rg = (string)($_GET['region'] ?? '');
$sql = 'SELECT * FROM churches WHERE 1=1'; $p = [];
if ($qs !== '') { $sql .= ' AND (name LIKE ? OR town LIKE ? OR leader LIKE ?)'; $p = ["%$qs%", "%$qs%", "%$qs%"]; }
if ($rg !== '') { $sql .= ' AND region = ?'; $p[] = $rg; }
$churches = q($sql . ' ORDER BY town', $p);
?>
<div class="wrap"><h1>Our churches</h1>
<form class="inline" method="get">
  <input name="q" value="<?= e($qs) ?>" placeholder="Search by name, town or leader">
  <select name="region"><option value="">All regions</option><?php foreach (entities()['churches']['fields']['region'][3] as $o) echo '<option' . ($o === $rg ? ' selected' : '') . '>' . e($o) . '</option>'; ?></select>
  <button class="btn">Search</button>
</form>
<p class="muted"><?= count($churches) ?> <?= count($churches) === 1 ? 'church' : 'churches' ?></p>
<div class="grid">
<?php foreach ($churches as $c): ?>
  <article class="card">
    <?php if ($c['photo']): ?><img class="cover" src="<?= e($c['photo']) ?>" alt="<?= e($c['name']) ?>"><?php endif; ?>
    <h2><?= e($c['name']) ?></h2>
    <p class="muted"><?= e($c['town']) ?><?= $c['address'] ? ', ' . e($c['address']) : '' ?></p>
    <?php if ($c['leader']): ?><p><b>Leader:</b> <?= e($c['leader']) ?></p><?php endif; ?>
    <?php if ($c['service_times']): ?><p><b>Services:</b> <?= e($c['service_times']) ?></p><?php endif; ?>
    <?php if ($c['phone']): ?><p><b>Phone:</b> <?= e($c['phone']) ?></p><?php endif; ?>
    <?php if (preg_match('~^https?://~i', (string)$c['maps_url'])): ?><a href="<?= e($c['maps_url']) ?>" target="_blank" rel="noopener noreferrer">View on Google Maps</a>
    <?php else: ?><span class="muted">Map link coming soon</span><?php endif; ?>
  </article>
<?php endforeach; ?>
</div>
<?php if (!$churches): ?><p class="muted">No churches match your search. Try a different town or name.</p><?php endif; ?>
<h2>Online church</h2><p>Can't make it in person? <a href="/livestream">Join the service from anywhere</a>.</p>
</div>
<?php foot();
