<?php
// ---- helpers (WhatsApp link for books, paragraphs for tracts) ----
if (!function_exists('wa_number')) {
    function wa_number($v): string {
        $v = (string)$v;
        if (preg_match('~wa\.me/(\d+)~', $v, $m)) $v = $m[1];           // accepts a pasted wa.me link
        $d = preg_replace('/\D+/', '', $v);
        if (preg_match('/^0\d{9}$/', $d)) $d = '256' . substr($d, 1);    // 0785 123 456 -> 256785123456
        return strlen($d) >= 9 ? $d : '';
    }
    function book_wa(array $r, string $default): string {
        $n = wa_number($r['whatsapp'] ?? '') ?: wa_number($default);
        if ($n === '') return '';
        $msg = trim((string)($r['whatsapp_msg'] ?? '')) ?: 'Hello, I would like the book "' . $r['title'] . '".';
        return 'https://wa.me/' . $n . '?text=' . rawurlencode($msg);
    }
    function paragraphs($t): string {
        $out = '';
        foreach (preg_split('/\R{2,}/', trim((string)$t)) as $p) if (trim($p) !== '') $out .= '<p>' . nl2br(e(trim($p))) . '</p>';
        return $out;
    }
}

$kind = (string)($_GET['kind'] ?? ''); $qs = trim((string)($_GET['q'] ?? ''));
$tract = ($tid = (int)($_GET['tract'] ?? 0)) ? one('SELECT * FROM tracts WHERE id = ? AND COALESCE(hidden,0) = 0', [$tid]) : [];

head($tract ? $tract['title'] : 'Resources', '/resources');

// ---- reading a single tract ----
if ($tract): ?>
<div class="wrap"><p><a href="/resources?kind=tract">&larr; All tracts</a></p>
  <article class="tract-read">
    <h1><?= e($tract['title']) ?></h1>
    <p class="muted"><?= e(implode(' · ', array_filter([$tract['read_time'], $tract['summary']]))) ?></p>
    <?php if ($tract['file']): ?><p><a class="btn" href="<?= e($tract['file']) ?>" download>Download this tract</a></p><?php endif; ?>
    <?php if ($tract['image']): ?><img class="tract-img" src="<?= e($tract['image']) ?>" alt=""><?php endif; ?>
    <div class="prose"><?= paragraphs($tract['body']) ?></div>
    <?php if (!trim((string)$tract['body']) && $tract['file']): ?><iframe class="pdfview" src="<?= e($tract['file']) ?>" title="<?= e($tract['title']) ?>"></iframe><?php endif; ?>
  </article>
</div>
<?php foot(); return; endif;

// ---- list pages ----
$set = single('site_settings');
$default = (string)($set['books_whatsapp'] ?? '') ?: (string)($set['whatsapp_url'] ?? '');
$like = $qs !== '' ? ["%$qs%", "%$qs%"] : [];

$res = []; $books = []; $tracts = [];
if (in_array($kind, ['', 'video', 'audio', 'book'], true)) {
    $sql = 'SELECT * FROM resources WHERE COALESCE(hidden,0) = 0'; $p = [];
    if (in_array($kind, ['video', 'audio', 'book'], true)) { $sql .= ' AND kind = ?'; $p[] = $kind; }
    if ($qs !== '') { $sql .= ' AND (title LIKE ? OR speaker LIKE ?)'; array_push($p, ...$like); }
    foreach (q($sql . ' ORDER BY date DESC, id DESC', $p) as $r) { if ($r['kind'] === 'book') $books[] = $r; else $res[] = $r; }
}
if (in_array($kind, ['', 'tract'], true)) {
    $sql = 'SELECT * FROM tracts WHERE COALESCE(hidden,0) = 0'; $p = [];
    if ($qs !== '') { $sql .= ' AND (title LIKE ? OR summary LIKE ?)'; array_push($p, ...$like); }
    $tracts = q($sql . ' ORDER BY COALESCE(sort_order,0), id DESC', $p);
}
?>
<div class="wrap"><h1>Resources</h1>
<form class="inline" method="get"><input name="q" value="<?= e($qs) ?>" placeholder="Search by title or speaker"><?php if ($kind) echo '<input type="hidden" name="kind" value="' . e($kind) . '">'; ?><button class="btn">Search</button></form>
<p class="tabs"><?php foreach ([''=>'All','video'=>'Video','audio'=>'Audio','book'=>'Books','tract'=>'Tracts'] as $k => $l) echo '<a href="/resources' . ($k ? "?kind=$k" : '') . '"' . ($k === $kind ? ' class="on"' : '') . '>' . $l . '</a>'; ?></p>

<?php if ($res): ?>
<div class="rs-grid">
<?php foreach ($res as $r): $id = $r['kind'] === 'video' ? yt_id($r['youtube_url']) : null; ?>
  <article class="rs-card">
    <?php if ($id): ?><div class="video yt-lite" data-yt="<?= e($id) ?>" data-title="<?= e($r['title']) ?>"><button type="button" class="yt-play" aria-label="Play <?= e($r['title']) ?>"><img src="https://i.ytimg.com/vi/<?= e($id) ?>/hqdefault.jpg" alt="" loading="lazy"><span class="yt-btn" aria-hidden="true"></span></button></div><?php endif; ?>
    <?php if ($r['kind'] === 'audio' && $r['file']): ?><audio controls preload="none" src="<?= e($r['file']) ?>"></audio><?php endif; ?>
    <div class="rs-body">
      <h2><?= e($r['title']) ?></h2>
      <p class="muted"><?= e(implode(', ', array_filter([$r['speaker'], fmt_date($r['date'])]))) ?></p>
      <?php if (trim((string)$r['description']) !== ''): ?><details class="rdesc"><summary>Read description</summary><div><?= paragraphs($r['description']) ?></div></details><?php endif; ?>
    </div>
  </article>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($books): ?>
  <?php if ($kind === ''): ?><h2 class="rsec">Books</h2><?php endif; ?>
  <p class="muted">Tap a book to chat with us on WhatsApp about getting a copy.</p>
  <div class="rgrid">
  <?php foreach ($books as $b): $wa = book_wa($b, $default);
        $cover = $b['cover'] ? '<img src="' . e($b['cover']) . '" alt="">' : '<span class="rph">' . e(mb_substr((string)$b['title'], 0, 1)) . '</span>'; ?>
    <article class="rcard rbook">
      <?php if ($wa): ?><a class="rcover" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="<?= e($b['title']) ?> on WhatsApp"><?= $cover ?></a>
      <?php else: ?><div class="rcover"><?= $cover ?></div><?php endif; ?>
      <div class="rbody">
        <?php if ($wa): ?><a class="btn rtitle" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= e($b['title']) ?></a>
        <?php else: ?><span class="btn rtitle"><?= e($b['title']) ?></span><?php endif; ?>
        <?php if (trim((string)$b['description']) !== ''): ?>
          <details class="rdesc"><summary>Read description</summary><div><?= paragraphs($b['description']) ?></div></details>
        <?php endif; ?>
        <?php if ($wa): ?><a class="btn wa" href="<?= e($wa) ?>" target="_blank" rel="noopener">Chat on WhatsApp</a><?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($tracts): ?>
  <?php if ($kind === ''): ?><h2 class="rsec">Tracts</h2><?php endif; ?>
  <p class="muted">Read a tract here, or download it to share.</p>
  <div class="rgrid">
  <?php foreach ($tracts as $t): $href = '/resources?tract=' . (int)$t['id']; ?>
    <article class="rcard">
      <a class="rcover" href="<?= $href ?>" aria-label="Read <?= e($t['title']) ?>"><?= $t['image'] ? '<img src="' . e($t['image']) . '" alt="">' : '<span class="rph">' . e(mb_substr((string)$t['title'], 0, 1)) . '</span>' ?></a>
      <div class="rbody">
        <h3><a href="<?= $href ?>"><?= e($t['title']) ?></a></h3>
        <?php if ($t['read_time']): ?><p class="muted"><?= e($t['read_time']) ?></p><?php endif; ?>
        <?php if ($t['summary']): ?><p><?= e($t['summary']) ?></p><?php endif; ?>
        <a class="btn sm" href="<?= $href ?>">Read</a>
        <?php if ($t['file']): ?><a class="btn sm ghost-dark" href="<?= e($t['file']) ?>" download>Download</a><?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!$res && !$books && !$tracts): ?><p class="muted">Nothing matches your search. Try another title or speaker.</p><?php endif; ?>
</div>
<?php foot(); ?>