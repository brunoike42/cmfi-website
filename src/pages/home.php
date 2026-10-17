<?php
require_once __DIR__ . '/../home_cards.php';
head('Home', '/');

// Links typed in the admin: only /relative, http(s), mailto and tel are allowed.
function safe_link($u): string {
    $u = trim((string)$u);
    if ($u !== '' && $u[0] === '/' && !str_starts_with($u, '//')) return $u;
    return preg_match('~^(https?://|mailto:|tel:)~i', $u) ? $u : '';
}

$slides   = q('SELECT * FROM slides WHERE COALESCE(`hidden`, 0) = 0 ORDER BY sort_order, id');
if (!$slides) $slides = [[
    'headline' => 'A church family for every season of life.', 'subtitle' => 'Join us on Sunday, or watch from anywhere.',
    'image' => '', 'video' => '', 'youtube_url' => '', 'btn1_label' => 'Find a center near you', 'btn1_url' => '/join-us',
    'btn2_label' => 'Watch live', 'btn2_url' => '/livestream']];

$live     = !empty(single('livestream')['is_live']);
$about    = single('about');
$set      = single('site_settings');
$churches = q('SELECT * FROM churches ORDER BY town LIMIT 6');
$sermons  = q("SELECT * FROM resources WHERE kind = 'video' AND youtube_url <> '' ORDER BY date DESC, id DESC LIMIT 9");
$events   = q('SELECT * FROM events WHERE date >= ? ORDER BY date LIMIT 4', [date('Y-m-d H:i')]);
$ministries = q('SELECT * FROM ministries ORDER BY sort_order, id LIMIT 6');
$stories  = q('SELECT * FROM testimonies ORDER BY id DESC LIMIT 2');
$news     = q('SELECT * FROM news ORDER BY date DESC LIMIT 3');
$nChurch  = (int)one('SELECT COUNT(*) c FROM churches')['c'];
$nProject = (int)one('SELECT COUNT(*) c FROM projects')['c'];
$faqs = [
    ['What should I expect on my first Sunday?', 'A warm welcome, uplifting worship and a practical message. Come as you are.'],
    ['Can I join online?', 'Yes. Every service is streamed live from the Livestream page.'],
    ['How can I give?', 'Give by mobile money or bank transfer. The Give page has the details.'],
];
?>
<!-- Slideshow: every slide is managed under Home slideshow in the admin -->
<section class="hs" data-hero aria-roledescription="carousel" aria-label="Highlights" tabindex="0">
  <?php foreach ($slides as $i => $s):
      $yt = yt_id($s['youtube_url'] ?? '');
      $type = $yt ? 'youtube' : (!empty($s['video']) ? 'video' : 'image');
      $b1 = safe_link($s['btn1_url'] ?? ''); $b2 = safe_link($s['btn2_url'] ?? '');
  ?>
  <article class="hs-slide<?= $i === 0 ? ' is-active' : '' ?>" data-type="<?= $type ?>" role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= count($slides) ?>">
    <div class="hs-media">
      <?php if ($type === 'video'): ?>
        <video muted loop playsinline preload="<?= $i === 0 ? 'auto' : 'metadata' ?>"<?= !empty($s['image']) ? ' poster="' . e($s['image']) . '"' : '' ?>><source src="<?= e($s['video']) ?>"></video>
      <?php else: ?>
        <?php if (!empty($s['image'])): ?><img src="<?= e($s['image']) ?>" alt=""<?= $i === 0 ? '' : ' loading="lazy"' ?>><?php endif; ?>
        <?php if ($type === 'youtube'): ?><div class="hs-yt" data-yt="<?= e($yt) ?>"></div><?php endif; ?>
      <?php endif; ?>
    </div>
    <div class="hs-shade"></div>
    <div class="wrap hs-text">
      <?php if ($live && $i === 0): ?><span class="live">Live now</span><?php endif; ?>
      <h1><?= e($s['headline']) ?></h1>
      <?php if (!empty($s['subtitle'])): ?><p><?= e($s['subtitle']) ?></p><?php endif; ?>
      <p>
        <?php if (!empty($s['btn1_label']) && $b1): ?><a class="btn" href="<?= e($b1) ?>"><?= e($s['btn1_label']) ?></a><?php endif; ?>
        <?php if (!empty($s['btn2_label']) && $b2): ?><a class="btn ghost" href="<?= e($b2) ?>"><?= e($s['btn2_label']) ?></a><?php endif; ?>
      </p>
    </div>
  </article>
  <?php endforeach; ?>
</section>

<div class="wrap">
  <nav class="quick" aria-label="Quick links">
    <a href="/join-us"><b>I'm new</b><span>Join us</span></a>
    <a href="/resources"><b>Resources</b><span>Sermons and teaching</span></a>
    <a href="/prayer"><b>Prayer</b><span>We will pray with you</span></a>
    <a href="/give"><b>Give</b><span>Mobile money and bank</span></a>
  </nav>

  <!-- About us: one sliding card per About section (picture, name, "Read about ..." button). Edit under Pages > About topics -->
  <?= xc_section('About us', xc_about_cards(), '/about', 'All about us') ?>

  <div class="sh"><h2>Your first Sunday</h2><a href="/join-us">Join us</a></div>
  <ol class="steps">
    <li><b>Pick a center</b><span>Choose the center nearest to you.</span></li>
    <li><b>We welcome you</b><span>Friendly greeters will meet you at the door.</span></li>
    <li><b>Connect</b><span>Stay for fellowship and find a way to serve.</span></li>
  </ol>
<?php if ($churches):
  $ch_ic = [
    'pin'   => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
    'user'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
    'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
    'map'   => '<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/>',
  ];
  $ch_svg = fn(string $n) => '<svg class="ch-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $ch_ic[$n] . '</svg>';
?>
  <div class="sh"><h2>Our centers</h2><a href="/join-us">All centers</a></div>
  <div class="ch-grid">
    <?php foreach ($churches as $c):
        $rows = [
          ['pin',   implode(', ', array_filter([trim((string)($c['town'] ?? '')), trim((string)($c['address'] ?? ''))]))],
          ['user',  trim((string)($c['leader'] ?? ''))],
          ['clock', trim((string)($c['service_times'] ?? ''))],
          ['phone', trim((string)($c['phone'] ?? ''))],
        ]; ?>
      <article class="ch-card">
        <h3><?= e($c['name']) ?></h3>
        <ul class="ch-rows">
          <?php foreach ($rows as [$ic, $tx]): if ($tx === '') continue; ?>
            <li><?= $ch_svg($ic) ?><span><?= e($tx) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <div class="ch-foot">
          <?php if (preg_match('~^https?://~i', (string)($c['maps_url'] ?? ''))): ?>
            <a href="<?= e($c['maps_url']) ?>" target="_blank" rel="noopener noreferrer"><?= $ch_svg('map') ?>View on Google Maps</a>
          <?php else: ?><span class="muted">Map link coming soon</span><?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php
  $sm = [];
  foreach ($sermons as $r) { $vid = yt_id($r['youtube_url']); if ($vid) $sm[] = [$vid, $r]; }
  if ($sm): ?>
  <div class="sh"><h2>Latest sermons</h2><a href="/resources?kind=video">Resources</a></div>
  <section class="xc" data-xc aria-label="Latest sermons">
    <div class="xc-frame">
      <button type="button" class="xc-nav xc-prev" aria-label="Previous sermons">&lsaquo;</button>
      <div class="xc-viewport" tabindex="0">
        <div class="xc-track">
          <?php foreach ($sm as [$vid, $r]): ?>
            <article class="xc-card">
              <a class="xc-img" href="/resources?kind=video" aria-label="Watch <?= e($r['title']) ?>"><img src="https://i.ytimg.com/vi/<?= e($vid) ?>/hqdefault.jpg" alt="" loading="lazy"></a>
              <div class="xc-body">
                <h3><a href="/resources?kind=video"><?= e($r['title']) ?></a></h3>
                <p><?= e(implode(', ', array_filter([$r['speaker'], fmt_date($r['date'])]))) ?></p>
                <a class="btn sm" href="/resources?kind=video">Watch</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
      <button type="button" class="xc-nav xc-next" aria-label="Next sermons">&rsaquo;</button>
    </div>
    <div class="xc-bar"><div class="xc-dots"></div><button type="button" class="xc-pause" aria-label="Pause slideshow">Pause</button></div>
  </section>
  <?php endif; ?>

  <div class="sh"><h2>Upcoming events</h2><a href="/events">All events</a></div>
  <ul class="rows evt">
  <?php foreach ($events as $ev): $ts = strtotime($ev['date']); ?>
    <li><time class="dbadge" datetime="<?= e(date('c', $ts)) ?>"><b><?= date('j', $ts) ?></b><?= date('M', $ts) ?></time>
      <div><strong><?= e($ev['title']) ?></strong><span><?= e(fmt_date($ev['date'], true)) ?><?= $ev['venue'] ? ', ' . e($ev['venue']) : '' ?></span></div></li>
  <?php endforeach; if (!$events): ?><li class="muted">No upcoming events yet. Add one in the admin.</li><?php endif; ?>
  </ul>

  <?php if ($ministries): ?>
  <div class="sh"><h2>A place for everyone</h2><a href="/ministries">Ministries</a></div>
  <div class="grid">
    <?php foreach ($ministries as $m): ?>
      <a class="card" href="/ministries" style="text-decoration:none">
        <h3><?= e($m['name']) ?></h3>
        <?php if ($m['target_group']): ?><p class="muted">For <?= e($m['target_group']) ?></p><?php endif; ?>
        <p><?= e(mb_strimwidth((string)$m['description'], 0, 110, '...')) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<section class="stats" aria-label="At a glance"><div class="wrap">
  <div><b><?= max(1, $nChurch) ?></b><span><?= $nChurch === 1 ? 'Church' : 'Churches' ?></span></div>
  <?php if (!empty($set['stat_members'])): ?><div><b><?= e($set['stat_members']) ?></b><span>Members</span></div><?php endif; ?>
  <div><b><?= $nProject ?></b><span><?= $nProject === 1 ? 'Project' : 'Projects' ?></span></div>
</div></section>

<div class="wrap">
  <!-- Projects slideshow (real projects, edit under Content > Projects) and the "more to discover" slideshow (Pages > Home page cards) -->
  <?= xc_section('Church projects', xc_project_cards(), '/projects', 'All projects') ?>
  <?= xc_section('More to discover', xc_explore_cards()) ?>

  <?php if ($stories || $news): ?>
  <div class="sh"><h2>Lives changed and church news</h2></div>
  <div class="grid">
    <?php foreach ($stories as $st): ?>
      <blockquote class="card tint quote"><p><?= e($st['quote']) ?></p><footer><?= e($st['name']) ?><?= $st['role'] ? ', ' . e($st['role']) : '' ?></footer></blockquote>
    <?php endforeach; ?>
    <?php foreach ($news as $n): $nl = safe_link($n['link'] ?? '') ?: '/events'; ?>
      <a class="card" href="<?= e($nl) ?>" style="text-decoration:none"><small class="muted"><?= e(fmt_date($n['date'])) ?></small><h3><?= e($n['title']) ?></h3><p class="muted"><?= e($n['summary']) ?></p></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="grid cta">
    <div class="card tint"><h3>Need prayer?</h3><p>Share your request with our team.</p><a class="btn" href="/prayer">Submit a prayer request</a></div>
    <div class="card tint"><h3>Support the work</h3><p>Give by mobile money or bank.</p><a class="btn" href="/give">Give now</a></div>
    <div class="card tint"><h3>Have a question?</h3><p>We are glad to hear from you.</p><a class="btn" href="/contact">Contact us</a></div>
  </div>

  <div class="sh"><h2>Good to know</h2></div>
  <div class="prose">
  <?php foreach ($faqs as [$qn, $an]): ?><details><summary><?= e($qn) ?></summary><p><?= e($an) ?></p></details><?php endforeach; ?>
  </div>

  <div class="sh"><h2>Stay connected</h2></div>
  <p class="muted">Get church news and event reminders by email.</p>
  <?php form_open('subscribers'); field('email', 'Email address', 'email'); ?><button class="btn">Subscribe</button></form>
</div>
<?php foot();