<?php
// Cards for the sliding sections on the home page (About us, Projects, Explore).
require_once __DIR__ . '/about_topics.php';

// First picture in a comma-separated list of uploads ('' when none is a picture).
function first_picture($list): string {
    foreach (array_filter(array_map('trim', explode(',', (string)$list))) as $f)
        if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) return $f;
    return '';
}

// One slide of a carousel. $c = title, summary, image, href, button.
function xc_card(array $c): string {
    $initial = mb_strtoupper(mb_substr(trim((string)preg_replace('/^(our|the|what we)\s+/i', '', (string)$c['title'])), 0, 1));
    $img = !empty($c['image']) ? '<img src="' . e($c['image']) . '" alt="" loading="lazy">' : '<span class="xc-ph" aria-hidden="true">' . e($initial) . '</span>';
    return '<article class="xc-card" role="group" aria-roledescription="slide"><a class="xc-img" href="' . e($c['href']) . '" tabindex="-1" aria-hidden="true">' . $img . '</a>'
         . '<div class="xc-body"><h3><a href="' . e($c['href']) . '">' . e($c['title']) . '</a></h3>'
         . (trim((string)($c['summary'] ?? '')) !== '' ? '<p>' . e($c['summary']) . '</p>' : '')
         . '<a class="btn" href="' . e($c['href']) . '">' . e($c['button']) . '</a></div></article>';
}
// A sliding section. Plays on its own; visitors can also use the arrows, dots or swipe.
function xc_section(string $title, array $cards, string $allHref = '', string $allLabel = ''): string {
    if (!$cards) return '';
    $o = '<section class="xc" data-xc aria-roledescription="carousel" aria-label="' . e($title) . '"><div class="sh"><h2>' . e($title) . '</h2>'
       . ($allHref !== '' ? '<a href="' . e($allHref) . '">' . e($allLabel) . '</a>' : '') . '</div>'
       . '<div class="xc-frame"><button type="button" class="xc-nav xc-prev" aria-label="Previous">&lsaquo;</button><div class="xc-viewport" tabindex="0"><div class="xc-track">';
    foreach ($cards as $c) $o .= xc_card($c);
    return $o . '</div></div><button type="button" class="xc-nav xc-next" aria-label="Next">&rsaquo;</button></div>'
         . '<div class="xc-bar"><span class="xc-dots"></span><button type="button" class="xc-pause" aria-label="Pause slideshow">Pause</button></div></section>';
}

function xc_about_cards(): array {
    $out = [];
    foreach (about_topics() as $t) $out[] = ['title' => $t['title'], 'summary' => $t['summary'], 'href' => '/about/' . $t['slug'],
        'image' => $t['image'] ?: ($t['banner'] ?: $t['side_image']), 'button' => 'Read about ' . $t['title']];
    return $out;
}
function xc_project_cards(): array {
    $out = [];
    foreach (q('SELECT * FROM projects ORDER BY id DESC LIMIT 10') as $p)
        $out[] = ['title' => $p['name'], 'summary' => $p['summary'] ?: ($p['category'] ?: ''), 'href' => '/projects#project-' . (int)$p['id'],
                  'image' => first_picture($p['photos']), 'button' => 'Read about ' . $p['name']];
    return $out;
}
// The other pages. Admin > Home page cards can change the picture, words, order or hide any of them.
function xc_explore_cards(): array {
    $ev = one('SELECT * FROM events WHERE date >= ? AND COALESCE(image,\'\') <> \'\' ORDER BY date LIMIT 1', [date('Y-m-d H:i')]);
    $book = one('SELECT cover FROM resources WHERE kind = \'book\' AND COALESCE(cover,\'\') <> \'\' AND COALESCE(hidden,0) = 0 ORDER BY id DESC LIMIT 1');
    $tract = one('SELECT image FROM tracts WHERE COALESCE(image,\'\') <> \'\' AND COALESCE(hidden,0) = 0 ORDER BY id DESC LIMIT 1');
    $church = one('SELECT photo FROM churches WHERE COALESCE(photo,\'\') <> \'\' LIMIT 1');
    $proj = one('SELECT photos FROM projects WHERE COALESCE(photos,\'\') <> \'\' ORDER BY id DESC LIMIT 1');
    $min = one('SELECT image FROM ministries WHERE COALESCE(image,\'\') <> \'\' ORDER BY COALESCE(sort_order,0), id LIMIT 1');
    $yt = yt_id(single('livestream')['youtube_url'] ?? '');
    $def = [   // page => [title, summary, href, picture, button, shown unless the admin adds a card for it]
        'projects'   => ['Projects', 'The schools, hospitals and mercy works we are building together.', '/projects', first_picture($proj['photos'] ?? ''), 'Read about Projects', 1],
        'resources'  => ['Resources', 'Videos, audios, books and tracts to help you grow.', '/resources', ($book['cover'] ?? '') ?: ($tract['image'] ?? ''), 'Browse Resources', 1],
        'events'     => ['Upcoming events', 'Conferences, prayer and outreach. Come and be part.', '/events', $ev['image'] ?? '', 'See Upcoming events', 1],
        'locations'  => ['Locations', 'Find a church near you and when we meet.', '/join-us', $church['photo'] ?? '', 'Find a Location', 1],
        'livestream' => ['Livestream', 'Join the service from anywhere in the world.', '/livestream', $yt ? 'https://i.ytimg.com/vi/' . $yt . '/hqdefault.jpg' : '', 'Watch the Livestream', 1],
        'ministries' => ['Ministries', 'Find a place to belong, serve and grow.', '/ministries', $min['image'] ?? '', 'Read about Ministries', 1],
        'prayer'     => ['Prayer request', 'Share what is on your heart. We will pray with you.', '/prayer', '', 'Send a Prayer request', 1],
        'give'       => ['Give', 'Support the work by mobile money or bank.', '/give', '', 'Read about Giving', 0],
        'contact'    => ['Contact', 'We are glad to hear from you.', '/contact', '', 'Contact us', 0],
    ];
    $rows = [];
    foreach (q('SELECT * FROM home_cards ORDER BY COALESCE(sort_order,0), id') as $r) if (!isset($rows[$r['page']])) $rows[$r['page']] = $r;
    $out = []; $i = 0;
    foreach ($def as $page => [$title, $sum, $href, $img, $btn, $on]) {
        $r = $rows[$page] ?? []; $i++;
        if (!empty($r['hidden']) || (!$on && !$r)) continue;
        $title = trim((string)($r['title'] ?? '')) ?: $title;
        $out[] = ['order' => !empty($r['sort_order']) ? (int)$r['sort_order'] : $i * 10, 'title' => $title,
                  'summary' => trim((string)($r['summary'] ?? '')) ?: $sum, 'href' => $href,
                  'image' => trim((string)($r['image'] ?? '')) ?: $img,
                  'button' => trim((string)($r['button_label'] ?? '')) ?: $btn];
    }
    usort($out, fn($a, $b) => $a['order'] <=> $b['order']);
    return $out;
}
