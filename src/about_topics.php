<?php
// Shared helpers for the About hub (/about) and the topic pages (/about/vision, /about/story ...).

function about_defaults(): array {          // slug => [card title, default one-line summary ('' = taken from the text)]
    return [
        'story'   => ['Our story', 'How we began and how God has led us'],
        'vision'  => ['Our vision', ''],
        'mission' => ['Our mission', ''],
        'goals'   => ['Our goals', ''],
        'beliefs' => ['What we believe', 'The truths we hold together'],
        'leaders' => ['Our leaders', 'The people who serve you'],
    ];
}
function about_excerpt($t, int $n = 90): string {
    $t = trim((string)preg_replace('/\s+/', ' ', (string)$t));
    return mb_strlen($t) > $n ? rtrim(mb_substr($t, 0, $n - 1)) . '…' : $t;
}
function about_paragraphs($t): string {
    $out = '';
    foreach (preg_split('/\R{2,}/', trim((string)$t)) as $p) if (trim($p) !== '') $out .= '<p>' . nl2br(e(trim($p))) . '</p>';
    return $out;
}
// Visible topics, in order. Admin rows (About topics) override the defaults; hidden ones are left out.
function about_topics(): array {
    $a = single('about'); $rows = []; $out = []; $i = 0;
    foreach (q('SELECT * FROM about_topics ORDER BY COALESCE(sort_order,0), id') as $r) if (!isset($rows[$r['slug']])) $rows[$r['slug']] = $r;
    foreach (about_defaults() as $slug => [$title, $sum]) {
        $r = $rows[$slug] ?? []; $i++;
        if (!empty($r['hidden'])) continue;
        if ($sum === '') $sum = about_excerpt($slug === 'goals' ? implode(' · ', lines($a['goals'] ?? '')) : ($a[$slug] ?? ''));
        $out[$slug] = [
            'slug' => $slug,
            'title' => trim((string)($r['title'] ?? '')) ?: $title,
            'summary' => trim((string)($r['summary'] ?? '')) ?: $sum,
            'image' => (string)($r['image'] ?? ''), 'banner' => (string)($r['banner'] ?? ''),
            'tagline' => trim((string)($r['tagline'] ?? '')), 'numbers' => (string)($r['key_numbers'] ?? ''),
            'intro' => trim((string)($r['intro'] ?? '')), 'body' => trim((string)($r['body'] ?? '')), 'side_image' => (string)($r['side_image'] ?? ''),
            'order' => !empty($r['sort_order']) ? (int)$r['sort_order'] : $i * 10,
        ];
    }
    uasort($out, fn($x, $y) => $x['order'] <=> $y['order']);
    return $out;
}
// An uploaded file: ['img', html] for pictures, ['pdf', html] for a download button, ['', ''] for nothing.
function about_upload($f, string $label): array {
    $f = (string)$f; if ($f === '') return ['', ''];
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
        return ['img', '<a href="' . e($f) . '" target="_blank" rel="noopener"><img class="abimg" src="' . e($f) . '" alt="' . e($label) . '"></a>'];
    if ($ext === 'pdf') return ['pdf', '<p><a class="btn" href="' . e($f) . '" download>Download ' . e($label) . ' (PDF)</a></p>'];
    return ['', ''];
}
// One text + picture row. Pictures alternate sides; no picture = one wide column.
function about_block(string $text, string $media, int $i): string {
    $cls = 'abk' . ($media === '' ? ' solo' : ($i % 2 ? ' flip' : ''));
    return '<div class="' . $cls . '"><div class="abt">' . $text . '</div>' . ($media !== '' ? '<div class="abm">' . $media . '</div>' : '') . '</div>';
}
// Visible leaders, in order (used by the menu, the Our leaders page and the home page).
function about_leaders(): array {
    return q('SELECT * FROM leaders WHERE COALESCE(hidden,0) = 0 ORDER BY COALESCE(sort_order,0), id');
}
function about_initials($name): string {
    return implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim((string)$name)) ?: [''], 0, 2)));
}
// "Explore more": the other About sections as a small title link + a line of text, shown at the bottom of every About page.
function about_more(array $topics, string $current): string {
    $items = '';
    foreach ($topics as $o) {
        if ($o['slug'] === $current) continue;
        $items .= '<div class="xm-item"><a href="/about/' . e($o['slug']) . '">' . e($o['title']) . '</a>'
                . ($o['summary'] !== '' ? '<p>' . e($o['summary']) . '</p>' : '') . '<span class="xm-rule" aria-hidden="true"></span></div>';
    }
    return $items === '' ? '' : '<section class="xm" aria-label="Explore more"><h2>Explore more</h2><div class="xm-grid">' . $items . '</div></section>';
}
