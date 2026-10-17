<?php
require_once __DIR__ . '/about_topics.php';

// Small line icons for the top menu (Feather style, drawn inline so no extra files are needed).
function nav_icon(string $n): string {
    static $p = [
        'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'join' => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>',
        'about' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'projects' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'connect' => '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'chev' => '<polyline points="6 9 12 15 18 9"/>',
    ];
    return '<svg class="ni" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$n] ?? '') . '</svg>';
}

// The whole top menu in one place.
// Each item: label, href (null = opens its dropdown only), icon, pages (the $active values that belong to it), kids.
function nav_items(): array {
    $about = [['label' => 'Overview', 'href' => '/about']];
    foreach (about_topics() as $t) {
        $kid = ['label' => $t['title'], 'href' => '/about/' . $t['slug']];
        if ($t['slug'] === 'leaders') {
            $kid['kids'] = [];
            foreach (about_leaders() as $l) $kid['kids'][] = ['label' => trim((string)$l['position']) ?: $l['name'], 'href' => '/about/leaders?leader=' . (int)$l['id']];
        }
        $about[] = $kid;
    }
    return [
        ['label' => 'Home', 'href' => '/', 'icon' => 'home', 'pages' => ['/']],
        ['label' => 'Join us', 'href' => '/join-us', 'icon' => 'join', 'pages' => ['/join-us', '/livestream'], 'kids' => [
            ['label' => 'Locations', 'href' => '/join-us'], ['label' => 'Livestream', 'href' => '/livestream']]],
        ['label' => 'About us', 'href' => '/about', 'icon' => 'about', 'pages' => ['/about'], 'kids' => $about],
        ['label' => 'Ministries', 'href' => '/ministries', 'icon' => 'heart', 'pages' => ['/ministries']],
        ['label' => 'Resources', 'href' => '/resources', 'icon' => 'download', 'pages' => ['/resources'], 'kids' => [
            ['label' => 'All resources', 'href' => '/resources'], ['label' => 'Videos', 'href' => '/resources?kind=video'],
            ['label' => 'Audios', 'href' => '/resources?kind=audio'], ['label' => 'Books', 'href' => '/resources?kind=book'],
            ['label' => 'Tracts', 'href' => '/resources?kind=tract']]],
        ['label' => 'Projects', 'href' => '/projects', 'icon' => 'projects', 'pages' => ['/projects'], 'kids' => array_merge(
    [['label' => 'All projects', 'href' => '/projects']],
    array_map(fn($c) => ['label' => $c, 'href' => '/projects?category=' . rawurlencode($c)],
        entities()['projects']['fields']['category'][3] ?? [])
    )],
        ['label' => 'Connect', 'href' => null, 'icon' => 'connect', 'pages' => ['/contact', '/events', '/prayer'], 'kids' => [
            ['label' => 'Contact', 'href' => '/contact'], ['label' => 'Upcoming events', 'href' => '/events'],
            ['label' => 'Prayer request', 'href' => '/prayer']]],
    ];
}
function nav_render(array $kids, string $cls): string {
    $o = '<ul class="' . $cls . '">';
    foreach ($kids as $k) {
        $sub = !empty($k['kids']);
        $o .= '<li' . ($sub ? ' class="has-sub"' : '') . '><a href="' . e($k['href']) . '">' . e($k['label']) . '</a>';
        if ($sub) $o .= '<button type="button" class="dd-toggle" aria-expanded="false" aria-label="Show ' . e($k['label']) . ' menu">' . nav_icon('chev') . '</button>' . nav_render($k['kids'], 'dd-sub');
        $o .= '</li>';
    }
    return $o . '</ul>';
}

function head(string $title, string $active = ''): void {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . e($title) . ' | ' . e(cfg('site_name')) . '</title><link rel="stylesheet" href="/assets/style.css"><style>' . file_get_contents(__DIR__ . '/ui/extra.css') . '</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    </head><body>';
       echo '<header class="top"><nav class="wrap"><a class="brand" href="/"><img src="/assets/logo.png" alt="">' . e(cfg('site_name')) . '</span><sp">UGANDA</span></span></a><button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-menu" aria-label="Menu"><i></i></button><ul id="site-menu">';
    foreach (nav_items() as $it) {
        $on = in_array($active, $it['pages'], true);
        $kids = $it['kids'] ?? [];
        $inner = nav_icon($it['icon']) . '<span>' . e($it['label']) . '</span>';
        if (!$kids) { echo '<li><a href="' . e($it['href']) . '"' . ($on ? ' class="on"' : '') . '>' . $inner . '</a></li>'; continue; }
        echo '<li class="has-dd' . ($on ? ' on' : '') . '">';
        if ($it['href'] !== null) echo '<a href="' . e($it['href']) . '"' . ($on ? ' class="on"' : '') . '>' . $inner . '</a><button type="button" class="dd-toggle" aria-expanded="false" aria-label="Show ' . e($it['label']) . ' menu">' . nav_icon('chev') . '</button>';
        else echo '<button type="button" class="dd-parent' . ($on ? ' on' : '') . '" aria-expanded="false">' . $inner . nav_icon('chev') . '</button>';
        echo nav_render($kids, 'dd') . '</li>';
    }
    echo '<li><a class="btn sm" href="/give">Give</a></li></ul></nav></header><main>';
    foreach (flashes() as [$t, $m]) echo '<div class="wrap"><p class="flash ' . $t . '" role="status">' . e($m) . '</p></div>';
}
function foot(): void {
    $s = single('site_settings');
    $web = fn($u) => preg_match('~^https?://~i', (string)$u) ? (string)$u : '';
   $img = !empty($s['footer_bg']) ? '<img class="foot-bg" src="' . e($s['footer_bg']) . '" alt="" loading="lazy">' : '';
   $social = array_filter([
        ['Facebook',  'fa-facebook-f', $web($s['facebook_url']  ?? '')],
        ['WhatsApp',  'fa-whatsapp',   $web($s['whatsapp_url']  ?? '')],
        ['TikTok',    'fa-tiktok',     $web($s['tiktok_url']    ?? '')],
        ['X',         'fa-x-twitter',  $web($s['x_url']         ?? '')],
        ['YouTube',   'fa-youtube',    $web($s['youtube_url']   ?? '')],
        ['Instagram', 'fa-instagram',  $web($s['instagram_url'] ?? '')],
   ], fn($x) => $x[2] !== '');
   $ic = fn($c) => '<i class="fa-solid ' . $c . '" aria-hidden="true"></i>';
    echo '</main><footer class="sitefoot">' . $img . '<div class="wrap"><div class="foot-panel">';
    echo '<div><h2>About ' . e(cfg('site_name')) . '</h2><p>' . nl2br(e($s['footer_about'] ?? '')) . '</p>';
    echo '</div><nav aria-label="Explore"><h2>Explore</h2><ul><li><a href="/">' . $ic('fa-house') . 'Home</a></li><li><a href="/join-us">' . $ic('fa-user-plus') . 'Join us</a></li><li><a href="/about">' . $ic('fa-circle-info') . 'About us</a></li><li><a href="/ministries">' . $ic('fa-heart') . 'Ministries</a></li><li><a href="/resources">' . $ic('fa-book-open') . 'Resources</a></li></ul></nav>';
    echo '<nav aria-label="Connect"><h2>Connect</h2><ul><li><a href="/livestream">' . $ic('fa-video') . 'Livestream</a></li><li><a href="/events">' . $ic('fa-calendar-days') . 'Events</a></li><li><a href="/projects">' . $ic('fa-diagram-project') . 'Projects</a></li><li><a href="/prayer">' . $ic('fa-hands-praying') . 'Prayer</a></li><li><a href="/give">' . $ic('fa-hand-holding-heart') . 'Give</a></li><li><a href="/contact">' . $ic('fa-envelope') . 'Contact</a></li></ul></nav>';
    echo '<div><h2>Contact</h2><ul>';
    if (!empty($s['footer_address'])) echo '<li>' . $ic('fa-location-dot') . '<span>' . nl2br(e($s['footer_address'])) . '</span></li>';
    if (!empty($s['footer_phone'])) echo '<li><a href="tel:' . e(preg_replace('/[^\d+]/', '', $s['footer_phone'])) . '">' . $ic('fa-phone') . e($s['footer_phone']) . '</a></li>';
    if (!empty($s['footer_email'])) echo '<li><a href="mailto:' . e($s['footer_email']) . '">' . $ic('fa-envelope') . e($s['footer_email']) . '</a></li>';
    if (!empty($s['footer_service'])) echo '<li>' . $ic('fa-clock') . '<span>' . e($s['footer_service']) . '</span></li>';
    echo '</ul>';
    if ($social) { echo '<ul class="foot-social">'; foreach ($social as [$l, $c, $u]) echo '<li><a href="' . e($u) . '" target="_blank" rel="noopener noreferrer" aria-label="' . e($l) . '" title="' . e($l) . '"><i class="fa-brands ' . $c . '" aria-hidden="true"></i></a></li>'; echo '</ul>'; }
    echo '</div></div><p class="foot-base">&copy; ' . date('Y') . ' ' . e(cfg('site_name')) . '. All rights reserved. </p></div></footer><script src="/assets/app.js"></script><script>' . file_get_contents(__DIR__ . '/ui/extra.js') . '</script></body></html>';
}

// A public form that posts to /submit/{entity}. $extra are hidden values.
function form_open(string $ent, array $extra = []): void {
    echo '<form method="post" action="/submit/' . e($ent) . '" class="form">' . csrf_field();
    echo '<input type="hidden" name="back" value="' . e($_SERVER['REQUEST_URI']) . '">';
    echo '<input class="hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">';
    foreach ($extra as $k => $v) echo '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
}
function field(string $name, string $label, string $type = 'text', array $opts = []): void {
    $l = e($label);
    if ($type === 'textarea') echo "<label>$l<textarea name=\"$name\" rows=\"5\"></textarea></label>";
    elseif ($type === 'select') { echo "<label>$l<select name=\"$name\">"; foreach ($opts as $o) echo '<option>' . e($o) . '</option>'; echo '</select></label>'; }
    elseif ($type === 'check') echo "<label class=\"chk\"><input type=\"checkbox\" name=\"$name\" checked> $l</label>";
    else echo "<label>$l<input name=\"$name\" type=\"$type\"></label>";
}