<?php
function head(string $title, string $active = ''): void {
    $nav = ['/' => 'Home', '/join-us' => 'Join us', '/about' => 'About us', '/ministries' => 'Ministries', '/resources' => 'Resources',
            '/livestream' => 'Livestream', '/events' => 'Events', '/projects' => 'Projects', '/prayer' => 'Prayer', '/contact' => 'Contact'];
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . e($title) . ' | ' . e(cfg('site_name')) . '</title><link rel="stylesheet" href="/assets/style.css"></head><body>';
       echo '<header class="top"><nav class="wrap"><a class="brand" href="/"><img src="/assets/logo.png" alt="Church logo">' . e(cfg('site_name')) . '</span><span class="country">UGANDA</span></span></a><button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-menu" aria-label="Menu"><i></i></button><ul id="site-menu">';
       foreach ($nav as $h => $l) echo '<li><a href="' . $h . '"' . ($h === $active ? ' class="on"' : '') . '>' . e($l) . '</a></li>';
    echo '<li><a class="btn sm" href="/give">Give</a></li></ul></nav></header><main>';
    foreach (flashes() as [$t, $m]) echo '<div class="wrap"><p class="flash ' . $t . '" role="status">' . e($m) . '</p></div>';
}
function foot(): void {
    $s = single('site_settings');
    $web = fn($u) => preg_match('~^https?://~i', (string)$u) ? (string)$u : '';
   $img = !empty($s['footer_bg']) ? '<img class="foot-bg" src="' . e($s['footer_bg']) . '" alt="" loading="lazy">' : '';
   $social = array_filter(['Facebook' => $web($s['facebook_url'] ?? ''), 'YouTube' => $web($s['youtube_url'] ?? ''),
                            'Instagram' => $web($s['instagram_url'] ?? ''), 'WhatsApp' => $web($s['whatsapp_url'] ?? '')]);
    echo '</main><footer class="sitefoot">' . $img . '<div class="wrap"><div class="foot-panel">';
    echo '<div><h2>About ' . e(cfg('site_name')) . '</h2><p>' . nl2br(e($s['footer_about'] ?? '')) . '</p>';
    if ($social) { echo '<ul class="foot-social">'; foreach ($social as $l => $u) echo '<li><a href="' . e($u) . '" target="_blank" rel="noopener noreferrer">' . e($l) . '</a></li>'; echo '</ul>'; }
    echo '</div><nav aria-label="Explore"><h2>Explore</h2><ul><li><a href="/">Home</a></li><li><a href="/join-us">Join us</a></li><li><a href="/about">About us</a></li><li><a href="/ministries">Ministries</a></li><li><a href="/resources">Resources</a></li></ul></nav>';
    echo '<nav aria-label="Connect"><h2>Connect</h2><ul><li><a href="/livestream">Livestream</a></li><li><a href="/events">Events</a></li><li><a href="/projects">Projects</a></li><li><a href="/prayer">Prayer</a></li><li><a href="/give">Give</a></li><li><a href="/contact">Contact</a></li></ul></nav>';
    echo '<div><h2>Contact</h2><ul>';
    if (!empty($s['footer_address'])) echo '<li>' . nl2br(e($s['footer_address'])) . '</li>';
    if (!empty($s['footer_phone'])) echo '<li><a href="tel:' . e(preg_replace('/[^\d+]/', '', $s['footer_phone'])) . '">' . e($s['footer_phone']) . '</a></li>';
    if (!empty($s['footer_email'])) echo '<li><a href="mailto:' . e($s['footer_email']) . '">' . e($s['footer_email']) . '</a></li>';
    if (!empty($s['footer_service'])) echo '<li>' . e($s['footer_service']) . '</li>';
    echo '</ul></div></div><p class="foot-base">&copy; ' . date('Y') . ' ' . e(cfg('site_name')) . '. All rights reserved. </p></div></footer><script src="/assets/app.js"></script></body></html>';
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
