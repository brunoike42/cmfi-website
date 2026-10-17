<?php
head('Join us', '/join-us');
$qs = trim((string)($_GET['q'] ?? '')); $rg = (string)($_GET['region'] ?? '');
$sql = 'SELECT * FROM churches WHERE 1=1'; $p = [];
if ($qs !== '') { $sql .= ' AND (name LIKE ? OR town LIKE ? OR leader LIKE ?)'; $p = ["%$qs%", "%$qs%", "%$qs%"]; }
if ($rg !== '') { $sql .= ' AND region = ?'; $p[] = $rg; }
$churches = q($sql . ' ORDER BY town', $p);

// small line icons for the card rows
$ch_ic = [
  'pin'   => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
  'user'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
  'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
  'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
  'map'   => '<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/>',
];
$ch_svg = fn(string $n) => '<svg class="ch-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $ch_ic[$n] . '</svg>';
?>
<div class="wrap"><h1>Locations</h1><p class="muted">Find a church near you and join us on Sunday.</p>
<form class="inline" method="get">
  <input name="q" value="<?= e($qs) ?>" placeholder="Search by name, town or leader">
  <select name="region"><option value="">All regions</option><?php foreach (entities()['churches']['fields']['region'][3] as $o) echo '<option' . ($o === $rg ? ' selected' : '') . '>' . e($o) . '</option>'; ?></select>
  <button class="btn">Search</button>
</form>
<p class="muted"><?= count($churches) ?> <?= count($churches) === 1 ? 'church' : 'churches' ?></p>
<div class="ch-grid">
<?php foreach ($churches as $c):
    $rows = [
      ['pin',   implode(', ', array_filter([trim((string)($c['town'] ?? '')), trim((string)($c['address'] ?? ''))]))],
      ['user',  trim((string)($c['leader'] ?? ''))],
      ['clock', trim((string)($c['service_times'] ?? ''))],
      ['phone', trim((string)($c['phone'] ?? ''))],
    ]; ?>
  <article class="ch-card">
    <?php if ($c['photo']): ?><img class="cover" src="<?= e($c['photo']) ?>" alt="<?= e($c['name']) ?>" loading="lazy"><?php endif; ?>
    <h2><?= e($c['name']) ?></h2>
    <ul class="ch-rows">
      <?php foreach ($rows as [$ic, $tx]): if ($tx === '') continue; ?>
        <li><?= $ch_svg($ic) ?><span><?= e($tx) ?></span></li>
      <?php endforeach; ?>
    </ul>
    <div class="ch-foot">
      <?php if (preg_match('~^https?://~i', (string)$c['maps_url'])): ?>
        <a href="<?= e($c['maps_url']) ?>" target="_blank" rel="noopener noreferrer"><?= $ch_svg('map') ?>View on Google Maps</a>
      <?php else: ?><span class="muted">Map link coming soon</span><?php endif; ?>
    </div>
  </article>
<?php endforeach; ?>
</div>
<?php if (!$churches): ?><p class="muted">No churches match your search. Try a different town or name.</p><?php endif; ?>
<h2>Online church</h2><p>Can't make it in person? <a href="/livestream">Join the service from anywhere</a>.</p>
</div>
<?php foot();