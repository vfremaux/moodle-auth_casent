<?php

require_once('../../config.php');

$deadcode = optional_param('code', 0, PARAM_INT);
$context = context_system::instance();
$url = new moodle_url('/auth/casent/deadend.php');

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('locallogout', 'auth_casent'));
$PAGE->set_heading(get_string('locallogout', 'auth_casent'));

echo $OUTPUT->header();

if ($deadcode == 1) {
    echo $OUTPUT->heading(get_string('nocasconnect', 'auth_casent'));

    echo '<div class="local-logout-message">';
    echo '<div class="logout-message">';
    echo get_string('nocasconnectmessage', 'auth_casent');
    echo '</div>';

    // Notifying admin
    $adminuser = $DB->get_record('user', array('username' => 'admin', 'mnethostid' => $CFG->mnet_localhost_id));
    $title = get_string('nocasconnectmailtitle_tpl', 'auth_casent');
    $body = get_string('nocasconnectmailbody_tpl', 'auth_casent');

    $title = str_replace('%%SITE%%', $SITE->shortname, $title);
    $body = str_replace('%%SITE%%', $SITE->shortname, $body);

    email_to_user($adminuser, $adminuser, $title, $body);
} else if ($deadcode == 2) {
    echo $OUTPUT->heading(get_string('unknownincominguser', 'auth_casent'));

    echo '<div class="local-logout-message">';
    echo '<div class="logout-message">';
    echo get_string('unknownincomingmessage', 'auth_casent');
    echo '</div>';

    // Notifying admin
    $adminuser = $DB->get_record('user', array('username' => 'admin', 'mnethostid' => $CFG->mnet_localhost_id));
    $title = get_string('unknownincomingmailtitle_tpl', 'auth_casent');
    $body = get_string('unknownincomingmailbody_tpl', 'auth_casent');

    $username = optional_param('username', '', PARAM_TEXT);

    $title = str_replace('%%SITE%%', $SITE->shortname, $title);
    $body = str_replace('%%SITE%%', $SITE->shortname, $body);
    $body = str_replace('%%USERNAME%%', $SITE->shortname, $username);

    email_to_user($adminuser, $adminuser, $title, $body);
} else {

    echo $OUTPUT->heading(get_string('locallogouttitle', 'auth_casent'));

    echo '<div class="local-logout-message">';
    echo '<div class="logout-message">';
    echo get_string('locallogoutmessage', 'auth_casent');
    echo '</div>';

    echo '<div class="logout-notice">';
    echo get_string('locallogoutmessage2', 'auth_casent');
    echo '</div>';
    echo '</div>';
}

echo $OUTPUT->footer();
