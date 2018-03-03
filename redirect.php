<?php

require_once('../../config.php');

// require_login();

$authplugin = get_auth_plugin('casent');

// If properly logged in, jump back to front entrance.
if (!empty($authplugin->config->logout_return_url)) {
    redirect(str_replace('%WWWROOT%', $CFG->wwwroot, $authplugin->config->logout_return_url));
} else {
    redirect($CFG->wwwroot);
}