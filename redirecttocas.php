<?php

require_once('../../config.php');

// require_login();

$authplugin = get_auth_plugin('casent');

redirect('https://'.$authplugin->config->hostname);
