<?php
// One definition drives the database, the admin forms and the public forms.
// field => [type, label, required, options-or-help]
$yn = ['Central', 'Eastern', 'Northern', 'Western'];
return [
  'churches' => ['label' => 'Churches', 'cols' => ['name', 'town', 'leader'], 'fields' => [
    'name' => ['text', 'Name', 1], 'town' => ['text', 'Town', 1], 'region' => ['select', 'Region', 0, $yn],
    'address' => ['text', 'Address'], 'leader' => ['text', 'Leader'],
    'service_times' => ['text', 'Service times', 0, 'e.g. Sun 9:00 AM, Wed 6:00 PM'], 'phone' => ['text', 'Phone'],
    'maps_url' => ['text', 'Google Maps link', 0, 'Optional'], 'photo' => ['image', 'Photo']]],
  'leaders' => ['label' => 'Leaders', 'cols' => ['name', 'position', 'photo', 'sort_order'], 'fields' => [
    'name' => ['text', 'Name', 1],
    'position' => ['text', 'Position', 1, 'Shown in the menu too, e.g. Founding leader, International leader, National leader of Uganda'],
    'photo' => ['image', 'Photo'],
    'bio' => ['textarea', 'Short description', 0, 'A few lines, shown next to the photo on the Our leaders page'],
    'story' => ['textarea', 'Full story', 0, 'Opens when a visitor clicks Read more. Blank line between paragraphs'],
    'responsibilities' => ['textarea', 'Responsibilities', 0, 'One per line'],
    'sort_order' => ['number', 'Order', 0, 'Smaller numbers show first'], 'hidden' => ['check', 'Hide this leader']]],
  'ministries' => ['label' => 'Ministries', 'cols' => ['name', 'target_group', 'leader'], 'fields' => [
    'name' => ['text', 'Name', 1], 'target_group' => ['text', 'For (e.g. Youth, Women)'], 'description' => ['textarea', 'Description', 1],
    'image' => ['image', 'Image'], 'leader' => ['text', 'Leader'], 'meeting_day' => ['text', 'Meeting day'],
    'meeting_time' => ['text', 'Meeting time'], 'location' => ['text', 'Location'], 'contact' => ['text', 'Contact person'],
    'activities' => ['textarea', 'Activities', 0, 'One per line'], 'sort_order' => ['number', 'Order']]],
  'events' => ['label' => 'Events', 'cols' => ['title', 'type', 'date'], 'fields' => [
    'title' => ['text', 'Title', 1], 'type' => ['select', 'Type', 0, ['Conference', 'Youth', 'Prayer', 'Outreach', 'Special service']],
    'date' => ['datetime', 'Date and time', 1], 'venue' => ['text', 'Venue'], 'organizer' => ['text', 'Organizer'],
    'description' => ['textarea', 'Description'], 'image' => ['image', 'Image'],
    'registration_required' => ['check', 'Registration required']]],
  'resources' => ['label' => 'Resources', 'cols' => ['title', 'kind', 'speaker', 'date'], 'fields' => [
    'title' => ['text', 'Title', 1], 'kind' => ['select', 'Kind', 1, ['video', 'audio', 'book']], 'speaker' => ['text', 'Speaker or book author'],
    'date' => ['date', 'Date'], 'youtube_url' => ['text', 'YouTube link', 0, 'For videos. Plays inside the site'],
    'file' => ['file', 'Audio file or book PDF'], 'was_livestream' => ['check', 'Past livestream'],
    'description' => ['textarea', 'Description'],
    'cover' => ['image', 'Book cover image', 0, 'Books only'],
    'whatsapp' => ['text', 'WhatsApp number for this book', 0, 'Books only. e.g. 256785123456 or 0785123456. Leave empty to use the default in Site settings'],
    'whatsapp_msg' => ['text', 'WhatsApp message', 0, 'Books only. Leave empty for: Hello, I would like the book "Title".'],
    'hidden' => ['check', 'Hide from the site']]],
  'tracts' => ['label' => 'Tracts', 'cols' => ['title', 'image', 'sort_order', 'hidden'], 'fields' => [
    'title' => ['text', 'Title', 1], 'summary' => ['text', 'Short summary'],
    'image' => ['image', 'Tract image'], 'read_time' => ['text', 'Reading time', 0, 'e.g. 3 minutes'],
    'body' => ['textarea', 'Full text (read on the site)', 0, 'Blank line between paragraphs'],
    'file' => ['file', 'PDF to download', 0, 'Visitors can download this'],
    'sort_order' => ['number', 'Order', 0, 'Smaller numbers show first'], 'hidden' => ['check', 'Hide from the site']]],

  'projects' => ['label' => 'Projects', 'cols' => ['name', 'category', 'status'], 'fields' => [
    'name' => ['text', 'Name', 1], 'category' => ['select', 'Category', 1, ['Schools', 'Hospitals', 'Mercy Works', 'Auxiliary base']],
    'status' => ['select', 'Status', 0, ['Planned', 'Ongoing', 'Completed']], 'location' => ['text', 'Location'],
    'coordinator' => ['text', 'Coordinator'], 'summary' => ['text', 'One-line summary'],
    'description' => ['textarea', 'Detailed description', 0, 'Blank line between paragraphs'],
    'photos' => ['images', 'Photos', 0, 'Several allowed. New photos are added to existing ones'],
    'updates' => ['textarea', 'Updates', 0, 'One per line: when|note  e.g. Sep 2026|Roofing started']]],
  'about' => ['label' => 'About page', 'single' => 1, 'fields' => [
    'about_intro' => ['text', 'Intro line under the About us title', 0, 'One short sentence. Leave empty for the default'],
    'story' => ['textarea', 'Who we are'], 'timeline' => ['textarea', 'Timeline', 0, 'One per line: year|event'],
    'vision' => ['textarea', 'Vision (type it)'], 'vision_file' => ['file', 'Vision upload (image or PDF)', 0, 'Optional. Use this instead of typing, or together with the text'],
    'mission' => ['textarea', 'Mission (type it)'], 'mission_file' => ['file', 'Mission upload (image or PDF)', 0, 'Optional. Use this instead of typing, or together with the text'],
    'goals' => ['textarea', 'Goals (type it)', 0, 'One per line'], 'goals_file' => ['file', 'Goals upload (image or PDF)', 0, 'Optional. Use this instead of typing, or together with the text'],
    'beliefs' => ['textarea', 'What we believe', 0, 'One per line: title|statement']]],

    'about_topics' => ['label' => 'About topics', 'cols' => ['slug', 'title', 'image', 'sort_order', 'hidden'], 'fields' => [
    'slug' => ['select', 'Which topic', 1, ['story', 'vision', 'mission', 'goals', 'beliefs', 'leaders']],
    'title' => ['text', 'Card and page title', 0, 'Leave empty for the default, e.g. Our vision'],
    'summary' => ['text', 'One-line summary on the card', 0, 'Leave empty to use the start of the text'],
    'image' => ['image', 'Card image'], 'banner' => ['image', 'Banner image at the top of the page'],
    'tagline' => ['text', 'Tagline under the page title'],
    'intro' => ['textarea', 'Introductory text', 0, 'Shown first as a highlighted quote, above the main text (e.g. on Our story)'],
    'body' => ['textarea', 'Main text', 0, 'e.g. the full story. Blank line between paragraphs. Leave empty to use the text under About page'],
    'side_image' => ['image', 'Picture next to the main text'],
    'key_numbers' => ['textarea', 'Key numbers', 0, 'One per line: number|label, e.g. 250|Nations'],
    'sort_order' => ['number', 'Order', 0, 'Smaller numbers show first'], 'hidden' => ['check', 'Hide this topic (card and page)']]],
  'about_blocks' => ['label' => 'About page sections', 'cols' => ['topic', 'heading', 'image', 'sort_order', 'hidden'], 'fields' => [
    'topic' => ['select', 'Show on which topic page', 1, ['story', 'vision', 'mission', 'goals', 'beliefs', 'leaders']],
    'heading' => ['text', 'Heading'], 'body' => ['textarea', 'Text', 0, 'Blank line between paragraphs'],
    'image' => ['image', 'Picture', 0, 'Optional. Pictures switch sides from block to block'],
    'sort_order' => ['number', 'Order', 0, 'Smaller numbers show first'], 'hidden' => ['check', 'Hide this section']]],
  'home_cards' => ['label' => 'Home page cards', 'cols' => ['page', 'title', 'image', 'sort_order', 'hidden'], 'fields' => [
    'page' => ['select', 'Which page', 1, ['projects', 'resources', 'events', 'locations', 'livestream', 'ministries', 'prayer', 'give', 'contact']],
    'title' => ['text', 'Card title', 0, 'Leave empty for the default, e.g. Projects'],
    'summary' => ['text', 'Short text', 0, 'Leave empty for the default'],
    'image' => ['image', 'Head picture', 0, 'Leave empty to use a picture from that page (a project photo, book cover, church photo...)'],
    'button_label' => ['text', 'Button text', 0, 'Leave empty for: Read about <title>'],
    'sort_order' => ['number', 'Order', 0, 'Smaller numbers show first'], 'hidden' => ['check', 'Hide this card']]],
  'livestream' => ['label' => 'Livestream', 'single' => 1, 'fields' => [
    'youtube_url' => ['text', 'YouTube live or video link'], 'is_live' => ['check', 'We are live now'],
    'next_service' => ['text', 'Next service', 0, 'e.g. Sunday, 10:00 AM'], 'facebook_url' => ['text', 'Facebook link'],
    'youtube_channel_url' => ['text', 'YouTube channel link']]],
  'giving' => ['label' => 'Giving', 'single' => 1, 'fields' => [
    'intro' => ['textarea', 'Why we give'],
    'funds' => ['textarea', 'Funds', 0, 'One per line: name|description|online payment link (optional)'],
    'methods' => ['textarea', 'Ways to give', 0, 'One per line: type|label|account name|number|instructions']]],
  'prayer_requests' => ['label' => 'Prayer requests', 'inbox' => 1, 'public' => ['name', 'contact', 'category', 'request', 'confidential'],
    'thanks' => 'Request received. Our team will pray with you.', 'fields' => [
    'name' => ['text', 'Name'], 'contact' => ['text', 'Contact'], 'category' => ['select', 'Category', 0, ['Personal', 'Family', 'Health', 'Education', 'Work', 'Spiritual growth', 'Thanksgiving', 'Other']],
    'request' => ['textarea', 'Request', 1], 'confidential' => ['check', 'Confidential']]],
  'messages' => ['label' => 'Messages', 'inbox' => 1, 'public' => ['name', 'email', 'phone', 'reason', 'message'],
    'thanks' => 'Message sent. The church office will reply soon.', 'fields' => [
    'name' => ['text', 'Name', 1], 'email' => ['text', 'Email'], 'phone' => ['text', 'Phone'],
    'reason' => ['select', 'Reason', 0, ['General inquiry', 'Prayer request', 'Pastoral counseling', 'Event inquiry', 'Ministry inquiry', 'Giving inquiry', 'Other']],
    'message' => ['textarea', 'Message', 1]]],
  'event_registrations' => ['label' => 'Event registrations', 'inbox' => 1, 'public' => ['event', 'name', 'phone', 'email', 'people'], 'need_contact' => 1,
    'thanks' => 'You are registered. See you there.', 'fields' => [
    'event' => ['text', 'Event'], 'name' => ['text', 'Name', 1], 'phone' => ['text', 'Phone'], 'email' => ['text', 'Email'], 'people' => ['number', 'People']]],
  'ministry_interests' => ['label' => 'Ministry sign-ups', 'inbox' => 1, 'public' => ['ministry', 'name', 'phone', 'email'], 'need_contact' => 1,
    'thanks' => 'Thank you. The ministry team will be in touch.', 'fields' => [
    'ministry' => ['text', 'Ministry'], 'name' => ['text', 'Name', 1], 'phone' => ['text', 'Phone'], 'email' => ['text', 'Email']]],
  
      'slides' => ['label' => 'Home slideshow', 'cols' => ['headline', 'image', 'sort_order', 'hidden'], 'fields' => [
    'headline' => ['text', 'Headline', 1], 'subtitle' => ['text', 'Subtitle'],
    'image' => ['image', 'Photo (or cover image for a video)'],
    'video' => ['file', 'Video file', 0, 'MP4 or WebM, up to 50 MB. Leave empty for a photo slide'],
    'youtube_url' => ['text', 'YouTube link', 0, 'Optional. Used instead of the video file'],
    'btn1_label' => ['text', 'Button 1 text'], 'btn1_url' => ['text', 'Button 1 link', 0, 'e.g. /join-us or https://...'],
    'btn2_label' => ['text', 'Button 2 text'], 'btn2_url' => ['text', 'Button 2 link'],
    'sort_order' => ['number', 'Order', 0, 'Smaller numbers show first'], 'hidden' => ['check', 'Hide this slide']]],
  'testimonies' => ['label' => 'Testimonies', 'cols' => ['name', 'role'], 'fields' => [
    'name' => ['text', 'Name', 1], 'role' => ['text', 'Role or town'], 'quote' => ['textarea', 'Testimony', 1]]],
  'news' => ['label' => 'News', 'cols' => ['title', 'date'], 'fields' => [
    'title' => ['text', 'Title', 1], 'date' => ['date', 'Date', 1], 'summary' => ['text', 'One-line summary'],
    'link' => ['text', 'Link', 0, 'e.g. /events or https://...']]],
  'site_settings' => ['label' => 'Site settings', 'single' => 1, 'fields' => [
    'footer_about' => ['textarea', 'Footer: about us'], 'footer_bg' => ['image', 'Footer background photo'],
    'footer_address' => ['text', 'Address'], 'footer_phone' => ['text', 'Phone'], 'footer_email' => ['text', 'Email'],
    'footer_service' => ['text', 'Service time', 0, 'e.g. Sundays, 9:00 AM'],
    'facebook_url' => ['text', 'Facebook link'], 'youtube_url' => ['text', 'YouTube link'],
    'instagram_url' => ['text', 'Instagram link'], 'whatsapp_url' => ['text', 'WhatsApp link'],
    'contact_tagline' => ['text', 'Contact page: line under the title', 0, 'Leave empty for: We\'d love to hear from you.'],
    'map_embed_url' => ['text', 'Contact page: map link', 0, 'Google Maps > Share > Embed a map > paste the iframe or its src link. Leave empty to show the map from the address'],
        'tiktok_url' => ['text', 'TikTok link'],
    'x_url' => ['text', 'X (Twitter) link'],
    'books_whatsapp' => ['text', 'Default WhatsApp number for books', 0, 'e.g. 256785123456 or
      0785123456. Used when a book has no number of its own. If empty, the WhatsApp link above is used'],
    'stat_members' => ['text', 'Members figure on the home page', 0, 'e.g. 5,000+']]],
  'subscribers' => ['label' => 'Email subscribers', 'inbox' => 1, 'public' => ['email'],
    'thanks' => 'Thank you. You are subscribed.', 'fields' => ['email' => ['text', 'Email', 1]]],





    ];
