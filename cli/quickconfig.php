<?php
global $CLI_VMOODLE_PRECHECK;

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);
$CLI_VMOODLE_PRECHECK = true; // force first config to be minimal

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

if (!isset($CFG->dirroot)) {
    die ('$CFG->dirroot must be explicitely defined in moodle config.php for this script to be used');
}

require_once($CFG->dirroot.'/lib/clilib.php');         // cli only functions

// now get cli options
list($options, $unrecognized) = cli_get_params(
    array(
        'non-interactive'   => false,
        'host'              => false,
        'help'              => false
    ),
    array(
        'h' => 'help'
    )
);

$interactive = empty($options['non-interactive']);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Command line Moodle quick CAS ENT config.

Options:
--non-interactive     No interactive questions or confirmations
--host                Switches to this host virtual configuration before processing
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/cli/upgrade.php --host=http://my.virtual.moodle.org
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

if (!empty($options['host'])) {
    // Arms the vmoodle switching.
    echo('Arming for '.$options['host']."\n"); // mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
echo('Config check : playing for '.$CFG->wwwroot."\n");

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
echo "Cas Users Udated\n";

// Copy cas config
$casconfig = get_config('auth/cas');
foreach ($casconfig as $key => $value) {
    if ($key != 'version') {
        set_config($key, $value, 'auth/casent');
    }
}
echo "Cas Settings copied\n";

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
