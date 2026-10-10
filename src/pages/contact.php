<?php
head('Contact us', '/contact');
?>
<div class="wrap"><h1>Contact us</h1><div class="two">
<div><h2>Send us a message</h2>
<?php form_open('messages'); field('name', 'Full name'); field('email', 'Email', 'email'); field('phone', 'Phone');
field('reason', 'Reason for contacting us', 'select', entities()['messages']['fields']['reason'][3]); field('message', 'Message', 'textarea'); ?>
<button class="btn">Send message</button></form></div>
<div><h2>Christian Missionaries Fellowship International</h2><p>742 Rubaga Road , Kampala, Uganda</p><p>+256 772888183</p><p>cmfiug@yahoo.com</p><p class="muted">Mon to Fri, 9:00 AM to 5:00 PM</p>
<p class="muted"><b>Go and Make Disciples of All Nations</b></p></div></div></div>
<?php foot();
