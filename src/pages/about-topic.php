<?php
require_once __DIR__ . '/../about_topics.php';
$topics = about_topics();
if (!isset($topics[$topic])) {
    http_response_code(404); head('Not found', '/about');
    echo '<div class="wrap"><h1>Page not found</h1><p><a href="/about">Back to About us</a></p></div>'; foot(); return;
}
$t = $topics[$topic]; $a = single('about');
$blocks = q('SELECT * FROM about_blocks WHERE topic = ? AND COALESCE(hidden,0) = 0 ORDER BY COALESCE(sort_order,0), id', [$topic]);
$bg = $t['banner'] ? ' style="background-image:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),url(\'' . e($t['banner']) . '\')"' : '';

// ---- One leader's full story: /about/leaders?leader=ID ----
if ($topic === 'leaders' && isset($_GET['leader'])) {
    $lead = ctype_digit((string)$_GET['leader']) ? one('SELECT * FROM leaders WHERE id = ? AND COALESCE(hidden,0) = 0', [(int)$_GET['leader']]) : [];
    if (!$lead) {
        http_response_code(404); head('Not found', '/about');
        echo '<div class="wrap"><h1>Leader not found</h1><p><a href="/about/leaders">Back to ' . e($t['title']) . '</a></p></div>'; foot(); return;
    }
    head($lead['name'], '/about');
    ?>
<section class="abn"<?= $bg ?>><div class="wrap">
  <p class="abback"><a href="/about/leaders">&larr; <?= e($t['title']) ?></a></p>
  <h1><?= e($lead['name']) ?></h1>
  <?php if (trim((string)$lead['position']) !== ''): ?><p class="abtag"><?= e($lead['position']) ?></p><?php endif; ?>
  <span class="abline" aria-hidden="true"></span>
</div></section>
<div class="wrap">
<?php
    $media = $lead['photo'] ? '<img class="abimg" src="' . e($lead['photo']) . '" alt="' . e($lead['name']) . '">'
        : '<div class="ld-ph" aria-hidden="true">' . e(about_initials($lead['name'])) . '</div>';
    $story = about_paragraphs($lead['story'] ?: $lead['bio']) ?: '<p class="muted">The full story will be added soon.</p>';
    if (lines($lead['responsibilities'])) $story .= '<h2>Responsibilities</h2><ul>' . implode('', array_map(fn($r) => '<li>' . e($r) . '</li>', lines($lead['responsibilities']))) . '</ul>';
    echo about_block($story, $media, 0);
    $others = array_filter(about_leaders(), fn($l) => (int)$l['id'] !== (int)$lead['id']);
    if ($others): ?>
  <section class="xm" aria-label="Other leaders"><h2>Other leaders</h2><div class="xm-grid">
    <?php foreach ($others as $o): ?><div class="xm-item"><a href="/about/leaders?leader=<?= (int)$o['id'] ?>"><?= e($o['name']) ?></a><?php if (trim((string)$o['position']) !== ''): ?><p><?= e($o['position']) ?></p><?php endif; ?><span class="xm-rule" aria-hidden="true"></span></div><?php endforeach; ?>
  </div></section>
<?php endif; ?>
</div>
<?php foot(); return;
}

head($t['title'], '/about');
$introHtml = $t['intro'] !== '' ? '<blockquote class="hist-intro">' . nl2br(e($t['intro'])) . '</blockquote>' : '';
$sideImg = $t['side_image'] ? '<img class="abimg" src="' . e($t['side_image']) . '" alt="' . e($t['title']) . '">' : '';

// ---- Our story: header picture, then picture beside (introduction + story) ----
if ($topic === 'story') {
    if ($t['banner']): ?><div class="hist-banner" style="background-image:url('<?= e($t['banner']) ?>')" role="img" aria-label="<?= e($t['title']) ?>"></div><?php endif; ?>
<div class="hist-rule" aria-hidden="true"></div>
<div class="wrap hist<?= $sideImg === '' ? ' no-side' : '' ?>">
  <?php if ($sideImg !== ''): ?><div class="hist-side"><?= $sideImg ?></div><?php endif; ?>
  <div class="hist-main">
    <p class="abback"><a href="/about">&larr; About us</a></p>
    <h1><?= e($t['title']) ?></h1>
    <?php if ($t['tagline'] !== ''): ?><p class="muted"><?= e($t['tagline']) ?></p><?php endif; ?>
    <?= $introHtml ?>
    <div class="hist-text"><?= about_paragraphs($t['body'] !== '' ? $t['body'] : ($a['story'] ?? '')) ?: '<p class="muted">Add the church story in the admin under About topics (Main text).</p>' ?></div>
    <?php if (lines($a['timeline'] ?? '')): ?>
      <h2>Along the way</h2><ol class="timeline">
      <?php foreach (lines($a['timeline']) as $l): [$y, $ev] = parts($l, 2); ?><li><b><?= e($y) ?></b> <?= e($ev) ?></li><?php endforeach; ?></ol>
    <?php endif; ?>
  </div>
</div>
<div class="wrap">
<?php
    foreach ($blocks as $i => $b) {
        $text = (trim((string)$b['heading']) !== '' ? '<h2>' . e($b['heading']) . '</h2>' : '') . about_paragraphs($b['body']);
        echo about_block($text, $b['image'] ? '<img class="abimg" src="' . e($b['image']) . '" alt="' . e($b['heading']) . '">' : '', $i);
    }
    echo about_more($topics, $topic); ?>
</div>
<?php foot(); return;
}

// ---- Every other section: banner, key numbers, then text + picture rows ----
?>
<section class="abn"<?= $bg ?>><div class="wrap">
  <p class="abback"><a href="/about">&larr; About us</a></p>
  <h1><?= e($t['title']) ?></h1>
  <?php if ($t['tagline'] !== ''): ?><p class="abtag"><?= e($t['tagline']) ?></p><?php endif; ?>
  <span class="abline" aria-hidden="true"></span>
</div></section>
<div class="wrap">
<?php if (lines($t['numbers'])): ?>
  <div class="kn"><?php foreach (lines($t['numbers']) as $l): [$n, $lab] = parts($l, 2); ?><div><b><?= e($n) ?></b><span><?= e($lab) ?></span></div><?php endforeach; ?></div>
<?php endif;
echo $introHtml;

$i = 0;
if (in_array($topic, ['vision', 'mission', 'goals'], true)) {
    if ($t['body'] !== '') $text = about_paragraphs($t['body']);
    else $text = $topic === 'goals'
        ? (lines($a['goals'] ?? '') ? '<ul>' . implode('', array_map(fn($g) => '<li>' . e($g) . '</li>', lines($a['goals']))) . '</ul>' : '')
        : about_paragraphs($a[$topic] ?? '');
    [$kind, $up] = about_upload($a[$topic . '_file'] ?? '', $t['title']);
    if ($kind === 'pdf') { $text .= $up; $up = ''; }
    $media = $sideImg !== '' ? $sideImg : $up;
    if ($text === '' && $media === '') $text = '<p class="muted">Not added yet. Add it in the admin under About page.</p>';
    echo about_block($text, $media, $i++);
} elseif ($topic === 'beliefs') {
    if ($t['body'] !== '' || $sideImg !== '') echo about_block(about_paragraphs($t['body']), $sideImg, $i++);
    if (lines($a['beliefs'] ?? '')) {
        echo '<div class="grid">';
        foreach (lines($a['beliefs']) as $l) { [$bt, $bs] = parts($l, 2); echo '<div class="card"><h3>' . e($bt) . '</h3><p class="muted">' . e($bs) . '</p></div>'; }
        echo '</div>';
    } elseif ($t['body'] === '' && $sideImg === '') echo '<p class="muted">Add beliefs in the admin under About page.</p>';
} elseif ($topic === 'leaders') {
    if ($t['body'] !== '' || $sideImg !== '') echo about_block(about_paragraphs($t['body']), $sideImg, $i++);
    // One picture + short text per leader; "Read more" opens that leader's full story.
    $leaders = about_leaders();
    foreach ($leaders as $l) {
        $href = '/about/leaders?leader=' . (int)$l['id'];
        $text = '<h2>' . e($l['name']) . '</h2>' . (trim((string)$l['position']) !== '' ? '<p class="ld-pos">' . e($l['position']) . '</p>' : '')
              . about_paragraphs(about_excerpt($l['bio'] ?: $l['story'], 320))
              . '<p><a class="btn" href="' . $href . '">Read more about ' . e($l['name']) . ' &rarr;</a></p>';
        $media = '<a href="' . $href . '" aria-label="Read more about ' . e($l['name']) . '">' . ($l['photo'] ? '<img class="abimg" src="' . e($l['photo']) . '" alt="' . e($l['name']) . '">'
               : '<div class="ld-ph" aria-hidden="true">' . e(about_initials($l['name'])) . '</div>') . '</a>';
        echo about_block($text, $media, $i++);
    }
    if (!$leaders) echo '<p class="muted">Add leaders in the admin under Leaders to show them here.</p>';
} else {
    if ($t['body'] !== '' || $sideImg !== '') echo about_block(about_paragraphs($t['body']), $sideImg, $i++);
}

foreach ($blocks as $b) {
    $text = (trim((string)$b['heading']) !== '' ? '<h2>' . e($b['heading']) . '</h2>' : '') . about_paragraphs($b['body']);
    $media = $b['image'] ? '<img class="abimg" src="' . e($b['image']) . '" alt="' . e($b['heading']) . '">' : '';
    echo about_block($text, $media, $i++);
}
echo about_more($topics, $topic);
?>
</div>
<?php foot();
