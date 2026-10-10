<?php
// Handles every public form (prayer, contact, registration, ministry sign-up).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/');
check_csrf();
$ent = entities()[$m[1]] ?? null;
if (!$ent || empty($ent['public'])) { http_response_code(404); exit('Not found'); }
$back = (string)($_POST['back'] ?? '/');
if ($back === '' || $back[0] !== '/' || str_starts_with($back, '//')) $back = '/';
if (!empty($_POST['website'])) redirect($back); // spam trap

$vals = []; $err = [];
foreach ($ent['public'] as $f) {
    $s = $ent['fields'][$f];
    if ($s[0] === 'check') { $vals[$f] = isset($_POST[$f]) ? 1 : 0; continue; }
    $v = trim((string)($_POST[$f] ?? ''));
    if ($s[0] === 'number') { $vals[$f] = $v === '' ? null : max(0, (int)$v); continue; }
    if (!empty($s[2]) && $v === '') $err[] = $s[1] . ' is required';
    if (in_array($f, ['email'], true) && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) $err[] = 'Enter a valid email address';
    $vals[$f] = mb_substr($v, 0, 3000);
}
if (!empty($ent['need_contact']) && ($vals['phone'] ?? '') === '' && ($vals['email'] ?? '') === '') $err[] = 'Enter a phone number or email so we can reach you';
if ($err) { flash(implode('. ', $err), 'bad'); redirect($back); }

$cols = array_keys($vals);
run('INSERT INTO `' . $m[1] . '` (' . implode(',', array_map(fn($c) => "`$c`", $cols)) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')', array_values($vals));
flash($ent['thanks']);
redirect($back);
