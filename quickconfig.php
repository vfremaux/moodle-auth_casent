<?php

require('../../config.php');

$context = context_system::instance();
$url = new moodle_url('/auth/casent/quickconfig.php');

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('locallogout', 'auth_casent'));
$PAGE->set_heading(get_string('locallogout', 'auth_casent'));

echo $OUTPUT->header();

require_login();
require_capability('moodle/site:config', context_system::instance());

echo '<pre>';
// Convert all cas users.

$sql = "
    UPDATE 
        {user}
    SET
        auth = 'casent'
    WHERE
        auth = 'cas'
";
$DB->execute($sql);
mtrace("Cas Users Udated");

// Copy cas config
$casconfig = get_config('auth/cas');
foreach ($casconfig as $key => $value) {
    if ($key != 'version') {
        set_config($key, $value, 'auth/casent');
    }
}
mtrace("Cas Settings copied");

// Change cas call into auth stack.

$authstack = get_config('moodle', 'auth');
$stack = explode(',', $authstack);

$newstack = array();
foreach($stack as $st) {
    if ($st != 'cas') {
        $newstack[] = $st;
    }
}
if (!in_array('casent',$newstack)) $newstack[] = 'casent';
$authstack = implode(',', $newstack);

set_config('auth', $authstack);
mtrace("Cas Auth enabled with $authstack");

// Change auth setting in ent_installer.
set_config('real_used_auth', 'casent', 'local_ent_installer');
mtrace("Cas Ent Installer patched");

// ensure last version.
$plugin = new StdClass();
require_once($CFG->dirroot.'/auth/casent/version.php');
set_config('version', $plugin->version, 'auth_casent');

echo '</pre>';

echo $OUTPUT->continue_button(new moodle_url('/admin/auth_config.php', array('auth' => 'casent')));

echo $OUTPUT->footer();
