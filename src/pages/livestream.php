<?php
head('Livestream', '/livestream');
$l = single('livestream'); $id = yt_id($l['youtube_url'] ?? '');
$past = q('SELECT * FROM resources WHERE was_livestream = 1 ORDER BY date DESC LIMIT 12');
?>
<div class="wrap"><h1>Livestream <?php if (!empty($l['is_live'])): ?><span class="live">Live now</span><?php endif; ?></h1>
<?php if (empty($l['is_live']) && !empty($l['next_service'])): ?><p class="muted">Next live service: <?= e($l['next_service']) ?></p><?php endif; ?>
<?php if ($id): ?><div class="video"><iframe src="https://www.youtube-nocookie.com/embed/<?= e($id) ?>" title="Church livestream" allowfullscreen></iframe></div>
<?php else: ?><p class="card tint">No stream is set up yet. Add the YouTube link in the admin under Livestream.</p><?php endif; ?>
<p><?php foreach (['youtube_channel_url' => 'Watch on YouTube', 'facebook_url' => 'Watch on Facebook'] as $k => $t) if (preg_match('~^https?://~i', (string)($l[$k] ?? ''))) echo '<a href="' . e($l[$k]) . '" target="_blank" rel="noopener noreferrer">' . $t . '</a> &nbsp; '; ?></p>
<h2>Previous livestreams</h2><div class="grid">
<?php foreach ($past as $r): $pid = yt_id($r['youtube_url']); ?>
  <h3><?= e($r['title']) ?></h3>
  <p class="muted"><?= e(fmt_date($r['date'])) ?></p>
  <article class="card"><?php if ($pid): ?><div class="video"><iframe src="https://www.youtube-nocookie.com/embed/<?= e($pid) ?>" title="<?= e($r['title']) ?>" loading="lazy" allowfullscreen></iframe></div><?php endif; ?></article>
<?php endforeach; ?></div>
<?php if (!$past): ?><p class="muted">Past services appear here. Tick "Past livestream" on a video in Resources.</p><?php endif; ?>
</div>
<?php foot();
