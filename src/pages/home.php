<?php
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
$sermons  = q("SELECT * FROM resources WHERE kind = 'video' AND youtube_url <> '' ORDER BY date DESC, id DESC LIMIT 3");
$events   = q('SELECT * FROM events WHERE date >= ? ORDER BY date LIMIT 4', [date('Y-m-d H:i')]);
$ministries = q('SELECT * FROM ministries ORDER BY sort_order, id LIMIT 6');
$projects = q('SELECT * FROM projects LIMIT 4');
$stories  = q('SELECT * FROM testimonies ORDER BY id DESC LIMIT 2');
$news     = q('SELECT * FROM news ORDER BY date DESC LIMIT 3');
$nChurch  = (int)one('SELECT COUNT(*) c FROM churches')['c'];
$nProject = (int)one('SELECT COUNT(*) c FROM projects')['c'];
$goals    = lines($about['goals'] ?? '');
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
  <?php if (count($slides) > 1): ?>
  <div class="hs-controls">
    <button type="button" data-dir="-1" aria-label="Previous slide">&lsaquo;</button>
    <span class="hs-dots"><?php foreach ($slides as $i => $s): ?><button type="button" data-go="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>></button><?php endforeach; ?></span>
    <button type="button" data-dir="1" aria-label="Next slide">&rsaquo;</button>
    <button type="button" class="hs-pause" data-pause aria-label="Pause slideshow">Pause</button>
  </div>
  <?php endif; ?>
</section>

<div class="wrap">
  <nav class="quick" aria-label="Quick links">
    <a href="/join-us"><b>I'm new</b><span>Join us</span></a>
    <a href="/resources"><b>Resources</b><span>Sermons and teaching</span></a>
    <a href="/prayer"><b>Prayer</b><span>We will pray with you</span></a>
    <a href="/give"><b>Give</b><span>Mobile money and bank</span></a>
  </nav>

  <div class="sh"><h2>Your first Sunday</h2><a href="/join-us">Join us</a></div>
  <ol class="steps">
    <li><b>Pick a center</b><span>Choose the center nearest to you.</span></li>
    <li><b>We welcome you</b><span>Friendly greeters will meet you at the door.</span></li>
    <li><b>Connect</b><span>Stay for fellowship and find a way to serve.</span></li>
  </ol>

  <?php if ($churches): ?>
  <div class="sh"><h2>Our centers</h2><a href="/join-us">All centers</a></div>
  <div class="grid">
    <?php foreach ($churches as $c): ?>
      <article class="card">
        <h3><?= e($c['name']) ?></h3>
        <p class="muted"><?= e($c['town']) ?><?= $c['address'] ? ', ' . e($c['address']) : '' ?></p>
        <?php if ($c['service_times']): ?><p><?= e($c['service_times']) ?></p><?php endif; ?>
        <?php if (preg_match('~^https?://~i', (string)$c['maps_url'])): ?><a href="<?= e($c['maps_url']) ?>" target="_blank" rel="noopener noreferrer">Get directions</a><?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($sermons): ?>
  <div class="sh"><h2>Latest sermons</h2><a href="/resources?kind=video">Resources</a></div>
  <div class="grid">
    <?php foreach ($sermons as $r): $id = yt_id($r['youtube_url']); if (!$id) continue; ?>
      <a class="card sermon" href="/resources?kind=video">
        <span class="thumb" style="background-image:url('https://i.ytimg.com/vi/<?= e($id) ?>/hqdefault.jpg')"><i aria-hidden="true">&#9654;</i></span>
        <b><?= e($r['title']) ?></b>
        <span class="muted"><?= e(implode(', ', array_filter([$r['speaker'], fmt_date($r['date'])]))) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
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
  <div class="sh"><h2>Church projects</h2><a href="/projects">Projects</a></div>
  <div class="grid">
  <?php foreach ($projects as $p): ?>
    <article class="card tint"><h3><?= e($p['name']) ?></h3><p class="muted"><?= e($p['summary']) ?></p></article>
  <?php endforeach; if (!$projects): ?><p class="muted">Projects will appear here once you add them in the admin.</p><?php endif; ?>
  </div>

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

  <!-- Vision, mission and goals come from Pages > About page in the admin -->
  <div class="sh"><h2>Our vision, mission and goals</h2><a href="/about">About us</a></div>
  <div class="grid three vmg">
    <div class="card"><h3>Our vision</h3><?php if (trim((string)($about['vision'] ?? '')) !== ''): ?><p><?= nl2br(e($about['vision'])) ?></p><?php else: ?><p><a href="/about">Read our vision</a></p><?php endif; ?></div>
    <div class="card"><h3>Our mission</h3><?php if (trim((string)($about['mission'] ?? '')) !== ''): ?><p><?= nl2br(e($about['mission'])) ?></p><?php else: ?><p><a href="/about">Read our mission</a></p><?php endif; ?></div>
    <div class="card"><h3>Our goals</h3><?php if ($goals): ?><ul><?php foreach ($goals as $g) echo '<li>' . e($g) . '</li>'; ?></ul><?php else: ?><p><a href="/about">Read our goals</a></p><?php endif; ?></div> </div>

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
