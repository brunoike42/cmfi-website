<?php
// Admin dashboard: login, first-time setup, and generic list/add/edit/delete for every entity.
$parts = array_values(array_filter(explode('/', substr($path, 6))));
$ents = entities();

function a_head(string $t, bool $nav = true): void {
    global $ents;
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">';
    echo '<title>' . e($t) . ' | Admin</title><link rel="stylesheet" href="/assets/style.css"></head><body>';
    if (!$nav) { echo '<div class="auth">'; return; }
    echo '<div class="admin"><aside><a href="/admin"><b style="margin-top:0">Dashboard</b></a>';
   $groups = ['Content' => ['slides', 'churches', 'leaders', 'ministries', 'events', 'resources', 'projects', 'tracts', 'testimonies', 'news'], 'Pages' =>
    ['about', 'livestream', 'giving', 'site_settings'], 'Inbox' => ['prayer_requests', 'messages', 'event_registrations', 'ministry_interests', 'subscribers']];
    foreach ($groups as $g => $list) { echo '<b>' . $g . '</b>'; foreach ($list as $k) echo '<a href="/admin/' . $k . '">' . e($ents[$k]['label']) . '</a>'; }
    echo '<b>Account</b><a href="/" target="_blank">View site</a><a href="/admin/logout">Log out</a></aside><section>';
    foreach (flashes() as [$ty, $m]) echo '<p class="flash ' . $ty . '" role="status">' . e($m) . '</p>';
}
function a_foot(): void { echo '</section></div></body></html>'; }

function a_input(string $f, array $s, $v): string {
    $n = e($f); $l = e($s[1]) . (!empty($s[2]) ? ' *' : '');
    $h = ($s[0] !== 'select' && !empty($s[3]) && is_string($s[3])) ? '<small>' . e($s[3]) . '</small>' : '';
    switch ($s[0]) {
        case 'textarea': return "<label>$l$h<textarea name=\"$n\" rows=\"5\">" . e($v) . '</textarea></label>';
        case 'select':
            $o = '<option value=""></option>'; foreach ($s[3] as $x) $o .= '<option' . ($x == $v ? ' selected' : '') . '>' . e($x) . '</option>';
            return "<label>$l<select name=\"$n\">$o</select></label>";
        case 'check': return "<label class=\"chk\"><input type=\"checkbox\" name=\"$n\"" . ($v ? ' checked' : '') . "> $l</label>";
        case 'number': return "<label>$l<input type=\"number\" name=\"$n\" value=\"" . e($v) . '"></label>';
        case 'date': return "<label>$l<input type=\"date\" name=\"$n\" value=\"" . e(substr((string)$v, 0, 10)) . '"></label>';
        case 'datetime': return "<label>$l<input type=\"datetime-local\" name=\"$n\" value=\"" . e(str_replace(' ', 'T', substr((string)$v, 0, 16))) . '"></label>';
        case 'image': case 'file': case 'images':
            $cur = $v ? '<small>Current: ' . e(basename(explode(',', $v)[0])) . ($s[0] === 'images' ? ' (' . count(explode(',', $v)) . ' file(s))' : '') . '</small>' : '';
            $multi = $s[0] === 'images' ? ' multiple' : '';
            return "<label>$l$h$cur<input type=\"file\" name=\"$n" . ($multi ? '[]' : '') . "\"$multi></label>";
        default: return "<label>$l$h<input type=\"text\" name=\"$n\" value=\"" . e($v) . '"></label>';
    }
}

function a_save(string $t, array $d, array $cur, ?int $id): array {
    $vals = []; $err = [];
    foreach ($d['fields'] as $f => $s) {
        $in = $_POST[$f] ?? '';
        switch ($s[0]) {
            case 'check': $vals[$f] = isset($_POST[$f]) ? 1 : 0; break;
            case 'number': $vals[$f] = $in === '' ? null : (int)$in; break;
            case 'datetime': $vals[$f] = $in === '' ? '' : str_replace('T', ' ', (string)$in); if (!empty($s[2]) && $vals[$f] === '') $err[] = $s[1] . ' is required'; break;
            case 'image': case 'file':
                $vals[$f] = $cur[$f] ?? '';
                if (!empty($_FILES[$f]['name'])) { $u = save_upload($_FILES[$f], $s[0] === 'image' ? ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'mov'] : ['pdf', 'mp3', 'm4a', 'wav', 'jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm']);
                 if ($u) $vals[$f] = $u; else $err[] = $s[1] . ': file type not allowed or too large'; }
                break;
            case 'images':
                $all = array_filter(explode(',', (string)($cur[$f] ?? '')));
                foreach ($_FILES[$f]['name'] ?? [] as $i => $nm) if ($nm !== '') { $u = save_upload(['name' => $nm, 'tmp_name' => $_FILES[$f]['tmp_name'][$i], 'error' => $_FILES[$f]['error'][$i], 'size' => $_FILES[$f]['size'][$i]],
                 ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'mov'  ]); if ($u) $all[] = $u; else $err[] = 'A photo was not allowed or too large'; }
                $vals[$f] = implode(',', $all); break;
            default:
                $vals[$f] = trim((string)$in);
                if (!empty($s[2]) && $vals[$f] === '') $err[] = $s[1] . ' is required';
        }
    }
    if ($err) return $err;
    $cols = array_keys($vals);
    if ($id) run("UPDATE `$t` SET " . implode(',', array_map(fn($c) => "`$c` = ?", $cols)) . ' WHERE id = ?', [...array_values($vals), $id]);
    else run("INSERT INTO `$t` (" . implode(',', array_map(fn($c) => "`$c`", $cols)) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')', array_values($vals));
    return [];
}

$post = $_SERVER['REQUEST_METHOD'] === 'POST';
$hasUsers = (int)one('SELECT COUNT(*) c FROM users')['c'] > 0;

// First-time setup
if (!$hasUsers) {
    if ($post) {
        check_csrf(); $em = trim((string)($_POST['email'] ?? '')); $pw = (string)($_POST['password'] ?? '');
        if (!filter_var($em, FILTER_VALIDATE_EMAIL) || strlen($pw) < 8) flash('Enter a valid email and a password of at least 8 characters.', 'bad');
        else { run('INSERT INTO users (email, password) VALUES (?, ?)', [$em, password_hash($pw, PASSWORD_DEFAULT)]); $_SESSION['uid'] = (int)db()->lastInsertId(); flash('Admin account created.'); redirect('/admin'); }
    }
    a_head('Create admin', false); echo '<h1>Create the admin account</h1><p class="muted">This is shown once. Use a strong password.</p>';
    foreach (flashes() as [$t, $m]) echo '<p class="flash bad">' . e($m) . '</p>';
    echo '<form method="post" class="form">' . csrf_field() . '<label>Email<input name="email" type="email" required></label><label>Password (8+ characters)<input name="password" type="password" required minlength="8"></label><button class="btn">Create account</button></form></div></body></html>';
    exit;
}

// Login
if (($parts[0] ?? '') === 'logout') { session_destroy(); redirect('/admin'); }
if (empty($_SESSION['uid'])) {
    if ($post) {
        check_csrf(); $u = one('SELECT * FROM users WHERE email = ?', [trim((string)($_POST['email'] ?? ''))]);
        if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password'])) { session_regenerate_id(true); $_SESSION['uid'] = (int)$u['id']; redirect('/admin'); }
        flash('Wrong email or password.', 'bad'); redirect('/admin');
    }
    a_head('Log in', false); echo '<h1>Admin log in</h1>';
    foreach (flashes() as [$t, $m]) echo '<p class="flash bad">' . e($m) . '</p>';
    echo '<form method="post" class="form">' . csrf_field() . '<label>Email<input name="email" type="email" required></label><label>Password<input name="password" type="password" required></label><button class="btn">Log in</button></form></div></body></html>';
    exit;
}

// Dashboard
if (!$parts) {
    a_head('Dashboard'); echo '<h1>Dashboard</h1><div class="grid">';
    foreach ($ents as $k => $d) { if (!empty($d['single'])) continue; echo '<a class="card tint" href="/admin/' . $k . '" style="text-decoration:none"><b>' . e($d['label']) . '</b><br><span class="big">' . (int)one("SELECT COUNT(*) c FROM $k")['c'] . '</span></a>'; }
    echo '</div><p class="muted">Pages such as About, Livestream and Giving are edited under Pages in the menu.</p>'; a_foot(); exit;
}

$k = $parts[0]; $d = $ents[$k] ?? null;
if (!$d) { http_response_code(404); a_head('Not found'); echo '<h1>Not found</h1>'; a_foot(); exit; }
$act = $parts[1] ?? ''; $id = ctype_digit($act) ? (int)$act : null;

// Single-page settings: always edit row 1
if (!empty($d['single'])) {
    if (!single($k)) run("INSERT INTO $k (id) VALUES (1)");
    $cur = single($k);
    if ($post) { check_csrf(); $er = a_save($k, $d, $cur, 1); if ($er) flash(implode('. ', $er), 'bad'); else flash('Saved.'); redirect('/admin/' . $k); }
    a_head($d['label']); echo '<h1>' . e($d['label']) . '</h1><form method="post" enctype="multipart/form-data" class="f">' . csrf_field();
    foreach ($d['fields'] as $f => $s) echo a_input($f, $s, $cur[$f] ?? '');
    echo '<button class="btn">Save</button></form>'; a_foot(); exit;
}

// Delete
if ($post && ($parts[2] ?? '') === 'delete' && $id) { check_csrf(); run("DELETE FROM $k WHERE id = ?", [$id]); flash('Deleted.'); redirect('/admin/' . $k); }

// Add / edit (not for inbox)
if (empty($d['inbox']) && ($act === 'new' || $id)) {
    $cur = $id ? one("SELECT * FROM $k WHERE id = ?", [$id]) : [];
    if ($id && !$cur) { http_response_code(404); exit('Not found'); }
    if ($post) {
        check_csrf(); $er = a_save($k, $d, $cur, $id);
        if ($er) { flash(implode('. ', $er), 'bad'); redirect($_SERVER['REQUEST_URI']); }
        flash('Saved.'); redirect('/admin/' . $k);
    }
    a_head($d['label']); echo '<h1>' . ($id ? 'Edit' : 'Add') . ' ' . e(strtolower(rtrim($d['label'], 's'))) . '</h1><form method="post" enctype="multipart/form-data" class="f">' . csrf_field();
    foreach ($d['fields'] as $f => $s) echo a_input($f, $s, $cur[$f] ?? '');
    echo '<p><button class="btn">Save</button> <a href="/admin/' . $k . '">Cancel</a></p></form>'; a_foot(); exit;
}

// List
a_head($d['label']); echo '<h1>' . e($d['label']) . '</h1>';
if (empty($d['inbox'])) echo '<p><a class="btn sm" href="/admin/' . $k . '/new">Add new</a></p>';
$order = isset($d['fields']['date']) ? 'date DESC' : (isset($d['fields']['sort_order']) ? 'sort_order, id' : 'id DESC');
$rows = q("SELECT * FROM $k ORDER BY $order");
$cols = $d['cols'] ?? array_keys($d['fields']);
echo '<table><tr>'; foreach ($cols as $c) echo '<th>' . e($d['fields'][$c][1]) . '</th>'; if (!empty($d['inbox'])) echo '<th>Received</th>'; echo '<th></th></tr>';
foreach ($rows as $r) {
    echo '<tr>';
    foreach ($cols as $c) { $s = $d['fields'][$c]; $val = $s[0] === 'check' ? ($r[$c] ? 'Yes' : 'No') : ($s[0] === 'image' && $r[$c] ? '<img class="thumb" src="' . e($r[$c]) . '" alt="">' : nl2br(e($r[$c]))); echo '<td>' . $val . '</td>'; }
    if (!empty($d['inbox'])) echo '<td>' . e(fmt_date($r['created_at'], true)) . '</td>';
    echo '<td>' . (empty($d['inbox']) ? '<a href="/admin/' . $k . '/' . $r['id'] . '">Edit</a> ' : '')
       . '<form method="post" action="/admin/' . $k . '/' . $r['id'] . '/delete" style="display:inline" onsubmit="return confirm(\'Delete this?\')">' . csrf_field() . '<button class="btn sm danger">Delete</button></form></td></tr>';
}
echo '</table>'; if (!$rows) echo '<p class="muted">Nothing here yet.</p>';
a_foot();
