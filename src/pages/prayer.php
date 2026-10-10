<?php
head('Prayer requests', '/prayer');
?>
<div class="wrap"><h1>Prayer requests</h1>
<p class="muted">Share what is on your heart. Only authorised church staff can read your request.</p>
<?php form_open('prayer_requests');
field('name', 'Name (optional)'); field('contact', 'Phone or email (optional)');
field('category', 'Category', 'select', entities()['prayer_requests']['fields']['category'][3]);
field('request', 'How can we pray for you?', 'textarea'); field('confidential', 'Keep my request confidential', 'check'); ?>
<button class="btn">Send prayer request</button></form></div>
<?php foot();
