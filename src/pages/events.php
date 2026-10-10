<?php
head('Events', '/events');
$past = ($_GET['view'] ?? '') === 'past'; $now = date('Y-m-d H:i');
$events = q($past ? 'SELECT * FROM events WHERE date < ? ORDER BY date DESC' : 'SELECT * FROM events WHERE date >= ? ORDER BY date', [$now]);
?>
<div class="wrap"><h1>Events</h1>
<p class="tabs"><a href="/events"<?= $past ? '' : ' class="on"' ?>>Upcoming</a><a href="/events?view=past"<?= $past ? ' class="on"' : '' ?>>Past events</a></p>
<?php foreach ($events as $ev): ?>
  <article class="card wide">
    <?php if ($ev['image']): ?><img class="cover" src="<?= e($ev['image']) ?>" alt=""><?php endif; ?>
    <h2><?= e($ev['title']) ?> <?php if ($ev['type']): ?><small class="pill"><?= e($ev['type']) ?></small><?php endif; ?></h2>
    <p class="muted"><?= e(fmt_date($ev['date'], true)) ?><?= $ev['venue'] ? ', ' . e($ev['venue']) : '' ?><?= $ev['organizer'] ? ' | ' . e($ev['organizer']) : '' ?></p>
    <p><?= nl2br(e($ev['description'])) ?></p>
    <?php if (!$past && $ev['registration_required']): ?>
      <details><summary>Register</summary>
      <?php form_open('event_registrations', ['event' => $ev['title']]); field('name', 'Full name'); field('phone', 'Phone'); field('email', 'Email', 'email'); field('people', 'Number of people', 'number'); ?>
      <button class="btn">Register</button></form></details>
    <?php elseif (!$past): ?><p class="muted">No registration needed. Just come along.</p><?php endif; ?>
  </article>
<?php endforeach; if (!$events): ?><p class="muted">No events to show yet.</p><?php endif; ?>
</div>
<?php foot();
