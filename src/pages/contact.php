<?php
head('Contact us', '/contact');

// ---- settings used below (all optional; page still works if they are empty) ----
$s   = single('site_settings');
$web = fn($u) => preg_match('~^https?://~i', (string)$u) ? (string)$u : '';
$tagline = trim((string)($s['contact_tagline'] ?? '')) ?: "We'd love to hear from you.";

// contact details (same text as before)
$address = '742 Rubaga Road , Kampala, Uganda';
$phone   = '+256 772888183';
$email   = 'cmfiug@yahoo.com';
$hours   = 'Mon to Fri, 9:00 AM to 5:00 PM';

// map: use the Google Maps link from Site settings if given, otherwise show the address
$map = trim((string)($s['map_embed_url'] ?? ''));
if (preg_match('~src="([^"]+)"~i', $map, $m)) $map = html_entity_decode($m[1]);   // accepts a pasted <iframe ...>
if (!preg_match('~^https://(www\.)?google\.com/maps/embed~i', $map)) $map = 'https://www.google.com/maps?q=' . rawurlencode($address) . '&output=embed';

// follow-us links (filled in under Site settings)
$social = array_filter([
  ['Facebook',  'fa-facebook-f', $web($s['facebook_url']  ?? '')],
  ['WhatsApp',  'fa-whatsapp',   $web($s['whatsapp_url']  ?? '')],
  ['TikTok',    'fa-tiktok',     $web($s['tiktok_url']    ?? '')],
  ['X',         'fa-x-twitter',  $web($s['x_url']         ?? '')],
  ['Instagram', 'fa-instagram',  $web($s['instagram_url'] ?? '')],
  ['YouTube', 'fa-youtube', $web($s['youtube_url'] ?? '')],
], fn($x) => $x[2] !== '');
?>
<div class="wrap"><h1>Contact us</h1>
<p class="ct-sub"><?= e($tagline) ?></p>
<div class="two">
<div><h2>Send us a message</h2>
<?php form_open('messages'); field('name', 'Full name'); field('email', 'Email', 'email'); field('phone', 'Phone');
field('reason', 'Reason for contacting us', 'select', entities()['messages']['fields']['reason'][3]); field('message', 'Message', 'textarea'); ?>
<button class="btn">Send message</button></form></div>
<div><h2>Christian Missionaries Fellowship International</h2>
<ul class="ct-list">
  <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><?= e($address) ?></span></li>
  <li><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= e($phone) ?></a></li>
  <li><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li>
  <li><i class="fa-regular fa-clock" aria-hidden="true"></i><span class="muted"><?= e($hours) ?></span></li>
</ul>
<p class="muted"><b>Go and Make Disciples of All Nations</b></p>

<div class="ct-map"><iframe src="<?= e($map) ?>" title="Map to our church office" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>

<?php if ($social): ?>
<div class="ct-follow"><span>Follow us</span>
  <?php foreach ($social as [$label, $icon, $url]): ?>
    <a href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($label) ?>" title="<?= e($label) ?>"><i class="fa-brands <?= e($icon) ?>" aria-hidden="true"></i></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div></div></div>
<?php foot();