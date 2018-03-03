<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Authentication Plugin: CAS Authentication for ENT (French Public Education)
 *
 * Authentication using CAS (Central Authentication Server).
 *
 * @author Martin Dougiamas
 * @author Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth_casent
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/auth/ldap/auth.php');
require_once($CFG->dirroot.'/auth/cas/CAS/CAS.php');
require_once($CFG->dirroot.'/auth/cas/auth.php');

/**
 * CAS authentication plugin overrides standard CAS
 */
class auth_plugin_casent extends auth_plugin_cas {

    var $casclient;

    /**
     * Constructor.
     */
    function auth_plugin_casent() {
        $this->authtype = 'casent';
        $this->roleauth = 'auth_casent';
        $this->errorlogtag = '[AUTH CASENT] ';
        $this->init_plugin($this->authtype);
        $this->casinitialized = false;
    }

    /**
     * Pre process handle_logout_request
     */
    function loginpage_hook() {
        global $frm;
        global $CFG;
        global $SESSION, $OUTPUT, $PAGE, $DB;

        $this->check_remote_logout();

        $site = get_site();
        $CASform = get_string('CASform', 'auth_cas');
        $username = optional_param('username', '', PARAM_RAW);
        $courseid = optional_param('courseid', 0, PARAM_INT);

        if (!empty($username)) {
            if (isset($SESSION->wantsurl) && (strstr($SESSION->wantsurl, 'ticket') ||
                                              strstr($SESSION->wantsurl, 'NOCAS'))) {
                unset($SESSION->wantsurl);
            }
            return;
        }

        // Return if CAS enabled and settings not specified yet
        if (empty($this->config->hostname)) {
            return;
        }

        $authCAS = optional_param('authCAS', '', PARAM_RAW);
        if ($authCAS == 'NOCAS') {
            // If no cas required, let the hand to other authentication plugins.
            return;
        }

        // If the multi-authentication setting is used, check for the param before connecting to CAS.
        if ($this->config->multiauth) {

            // Show authentication form for multi-authentication.
            // Test pgtIou parameter for proxy mode (https connection in background from CAS server to the php server).
            if ($authCAS != 'CAS' && !isset($_GET['pgtIou'])) {
                $PAGE->set_url('/login/index.php');
                $PAGE->navbar->add($CASform);
                $PAGE->set_title("$site->fullname: $CASform");
                $PAGE->set_heading($site->fullname);
                echo $OUTPUT->header();
                include($CFG->dirroot.'/auth/casent/cas_form.html');
                echo $OUTPUT->footer();
                exit();
            }
        }

        // Connection to CAS server.
        if (!$this->connectCAS()) {
            if ($authCAS == 'CAS') {
                // Redirect and signal no connection if CAS was explicitely required.
                $redirect = new moodle_url('/auth/casent/deadend.php?code=1');
                redirect($redirect);
            }
        }

        if (phpCAS::checkAuthentication()) {
            $frm = new stdClass();
            $frm->username = phpCAS::getUser();
            $frm->password = 'passwdCas';

            if (!$user = $DB->get_record('user', array('username' => $frm->username))) {
                redirect(new moodle_url('/auth/casent/deadend.php', array('username' => $frm->username, 'code' => 2)));
            }

            complete_user_login($user);

            if (!empty($SESSION->wantsurl)) {
                redirect($SESSION->wantsurl);
            }

            // Redirect to a course if multi-auth is activated, authCAS is set to CAS and the courseid is specified.
            if ($this->config->multiauth && !empty($courseid)) {
                redirect(new moodle_url('/course/view.php', array('id' => $courseid, 'authCAS' => 'CAS')));
            } else {
                redirect(new moodle_url('/', array('id' => SITEID, 'authCAS' => 'CAS')));
            }

            return;
        }

        if (isset($_GET['loginguest']) && ($_GET['loginguest'] == true)) {
            $frm = new stdClass();
            $frm->username = 'guest';
            $frm->password = 'guest';
            return;
        }

        // Force CAS authentication (if needed).
        if (!phpCAS::isAuthenticated()) {
            phpCAS::setLang($this->config->language);
            phpCAS::forceAuthentication();
        }
    }

    /**
     * Authenticates user against CAS and stores session match record
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        global $DB;

        if (!$this->connectCAS()) {
            $redirect = new moodle_url('/auth/casent/deadend.php?code=1');
            redirect($redirect);
        }

        $result = phpCAS::isAuthenticated();
        if (!$result) {
            $redirect = new moodle_url('/login/index.php');
            redirect($redirect);
        }
        $casusername = trim(core_text::strtolower(phpCAS::getUser()));

        if ($casusername == $username) {
            if ($user = $DB->get_record('user', array('username' => $casusername, 'auth' => 'casent'))) {
                $mapping = new StdClass();
                $mapping->casid = @$_SESSION['phpCAS']['ticket'];
                if (!empty($mapping->casid)) {
                    $mapping->userid = $user->id;
                    if (!$exists = $DB->get_record('auth_casent', array('casid' => $_SESSION['phpCAS']['ticket'], 'userid' => $user->id))) {
                        $DB->insert_record('auth_casent', $mapping);
                    }
                /*
                } else {
                    // Cas ticket is missing. We may not have been authetified nowhere.
                    return false;
                */
                }
            }
            return true;
        }

        redirect($redirect);
    }

    /**
    * Hook for logout page, prepare redirection
    */
    function logoutpage_hook() {
        global $USER, $redirect, $CFG;

        // Only do this if the user is actually logged in via CAS
        if ($USER->auth == $this->authtype) {
            // Check if there is an alternative logout return url defined
            if (isset($this->config->logout_return_url) && !empty($this->config->logout_return_url)) {
                $returnurl = $this->config->logout_return_url;
                $returnurl = str_replace('%WWWROOT%', $CFG->wwwroot, $returnurl);
                // Set redirect to alternative return url.
                $redirect = $returnurl;
            } else {
                $redirect = new moodle_url('/auth/casent/deadend.php');
            }
        }
    }

    /**
     * Logout from the CAS
     *
     */
    function prelogout_hook() {
        global $CFG, $USER, $DB;

        // Usually do nothing special, specially DO NOT CAS::Logout, but just let local session
        // this mainly promotes configuring Cas logout option to "no".
        // die.

        if (!empty($this->config->logoutcas) && $USER->auth == $this->authtype) {
            $backurl = !empty($this->config->logout_return_url) ? str_replace('%WWWROOT%', $CFG->wwwroot, $this->config->logout_return_url) : $CFG->wwwroot;
            $this->connectCAS();
            // Note: Hack to stable versions to trigger the event before it redirect to CAS logout.
            $sid = session_id();
            $event = \core\event\user_loggedout::create(
                array(
                    'userid' => $USER->id,
                    'objectid' => $USER->id,
                    'other' => array('sessionid' => $sid),
                )
            );
            if ($session = $DB->get_record('sessions', array('sid' => $sid))) {
                $event->add_record_snapshot('sessions', $session);
            }
            \core\session\manager::terminate_current();
            $event->trigger();

            phpCAS::logoutWithRedirectService($backurl);
        }
    }

    /**
     * In cas ENT, CAS must be enabled to change session ID to handle remote logout requests
     * PHP session id will be renamed using CAS ticket session ID so matching CAS session identity
     * with local session.
     */
    function initCAS() {
        global $CAS_INITIALIZED;

        if (empty($this->config->casversion)) return;

        if (!$CAS_INITIALIZED) {
            // Make sure phpCAS doesn't try to start a new PHP session when connecting to the CAS server.
            $CAS_INITIALIZED = true;
            if ($this->config->proxycas) {
                $this->casclient = phpCAS::proxy($this->config->casversion, $this->config->hostname, (int) $this->config->port, $this->config->baseuri, false);
            } else {
                $this->casclient = phpCAS::client($this->config->casversion, $this->config->hostname, (int) $this->config->port, $this->config->baseuri, false);
            }
        }

        return true;
    }

    /**
     * Connect to the CAS (clientcas connection or proxycas connection)
     *
     */
    function connectCAS() {
        global $CFG;

        if (!$this->initCAS()) {
            return;
        }

        $hostname = $this->config->hostname;
        if (!preg_match('/^http/', $hostname)) {
            $hostname = 'http://'.$hostname;
        }

        // If Moodle is configured to use a proxy, phpCAS needs some curl options set.
        if (!empty($CFG->proxyhost) && !is_proxybypass($hostname)) {
            phpCAS::setExtraCurlOption(CURLOPT_PROXY, $CFG->proxyhost);
            if (!empty($CFG->proxyport)) {
                phpCAS::setExtraCurlOption(CURLOPT_PROXYPORT, $CFG->proxyport);
            }
            if (!empty($CFG->proxytype)) {
                // Only set CURLOPT_PROXYTYPE if it's something other than the curl-default http
                if ($CFG->proxytype == 'SOCKS5') {
                    phpCAS::setExtraCurlOption(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                }
            }
            if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
                phpCAS::setExtraCurlOption(CURLOPT_PROXYUSERPWD, $CFG->proxyuser.':'.$CFG->proxypassword);
                if (defined('CURLOPT_PROXYAUTH')) {
                    // any proxy authentication if PHP 5.1
                    phpCAS::setExtraCurlOption(CURLOPT_PROXYAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
                }
            }
        }

        if (!empty($this->config->certificate_check) && !empty($this->config->certificate_path)){
            phpCAS::setCasServerCACert($this->config->certificate_path);
        } else {
            // Don't try to validate the server SSL credentials
            phpCAS::setNoCasServerValidation();
        }

        return true;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        global $CFG, $OUTPUT;

        if (!function_exists('ldap_connect')) { // Is php-ldap really there?
            echo $OUTPUT->notification(get_string('auth_ldap_noextension', 'auth_ldap'));

            // Don't return here, like we do in auth/ldap. We cas use CAS without LDAP.
            // So just warn the user (done above) and define the LDAP constants we use
            // in config.html, to silence the warnings.
            if (!defined('LDAP_DEREF_NEVER')) {
                define ('LDAP_DEREF_NEVER', 0);
            }
            if (!defined('LDAP_DEREF_ALWAYS')) {
                define ('LDAP_DEREF_ALWAYS', 3);
            }
        }

        include($CFG->dirroot.'/auth/casent/config.html');
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {

        // CAS settings
        if (!isset($config->hostname)) {
            $config->hostname = '';
        }
        if (!isset($config->port)) {
            $config->port = '';
        }
        if (!isset($config->casversion)) {
            $config->casversion = '';
        }
        if (!isset($config->baseuri)) {
            $config->baseuri = '';
        }
        if (!isset($config->language)) {
            $config->language = '';
        }
        if (!isset($config->proxycas)) {
            $config->proxycas = '';
        }
        if (!isset($config->logoutcas)) {
            $config->logoutcas = '';
        }
        if (!isset($config->checklogoutclient)) {
            $config->checklogoutclient = false;
        }
        if (!isset($config->remotelogoutallowedclients)) {
            $config->remotelogoutallowedclients = '';
        }
        if (!isset($config->multiauth)) {
            $config->multiauth = '';
        }
        if (!isset($config->certificate_check)) {
            $config->certificate_check = '';
        }
        if (!isset($config->certificate_path)) {
            $config->certificate_path = '';
        }
        if (!isset($config->logout_return_url)) {
            $config->logout_return_url = '';
        }

        // LDAP settings
        if (!isset($config->host_url)) {
            $config->host_url = '';
        }
        if (!isset($config->start_tls)) {
             $config->start_tls = false;
        }
        if (empty($config->ldapencoding)) {
            $config->ldapencoding = 'utf-8';
        }
        if (!isset($config->pagesize)) {
            $config->pagesize = LDAP_DEFAULT_PAGESIZE;
        }
        if (!isset($config->contexts)) {
            $config->contexts = '';
        }
        if (!isset($config->user_type)) {
            $config->user_type = 'default';
        }
        if (!isset($config->user_attribute)) {
            $config->user_attribute = '';
        }
        if (!isset($config->search_sub)) {
            $config->search_sub = '';
        }
        if (!isset($config->opt_deref)) {
            $config->opt_deref = LDAP_DEREF_NEVER;
        }
        if (!isset($config->bind_dn)) {
            $config->bind_dn = '';
        }
        if (!isset($config->bind_pw)) {
            $config->bind_pw = '';
        }
        if (!isset($config->ldap_version)) {
            $config->ldap_version = '3';
        }
        if (!isset($config->objectclass)) {
            $config->objectclass = '';
        }
        if (!isset($config->memberattribute)) {
            $config->memberattribute = '';
        }

        if (!isset($config->memberattribute_isdn)) {
            $config->memberattribute_isdn = '';
        }
        if (!isset($config->attrcreators)) {
            $config->attrcreators = '';
        }
        if (!isset($config->groupecreators)) {
            $config->groupecreators = '';
        }
        if (!isset($config->removeuser)) {
            $config->removeuser = AUTH_REMOVEUSER_KEEP;
        }

        // save CAS settings
        set_config('hostname', trim($config->hostname), $this->pluginconfig);
        set_config('port', trim($config->port), $this->pluginconfig);
        set_config('casversion', $config->casversion, $this->pluginconfig);
        set_config('baseuri', trim($config->baseuri), $this->pluginconfig);
        set_config('language', $config->language, $this->pluginconfig);
        set_config('proxycas', $config->proxycas, $this->pluginconfig);
        set_config('logoutcas', $config->logoutcas, $this->pluginconfig);
        set_config('checklogoutclient', $config->checklogoutclient, $this->pluginconfig);
        set_config('remotelogoutallowedclients', $config->remotelogoutallowedclients, $this->pluginconfig);
        set_config('multiauth', $config->multiauth, $this->pluginconfig);
        set_config('certificate_check', $config->certificate_check, $this->pluginconfig);
        set_config('certificate_path', $config->certificate_path, $this->pluginconfig);
        set_config('logout_return_url', $config->logout_return_url, $this->pluginconfig);

        // save LDAP settings
        set_config('host_url', trim($config->host_url), $this->pluginconfig);
        set_config('start_tls', $config->start_tls, $this->pluginconfig);
        set_config('ldapencoding', trim($config->ldapencoding), $this->pluginconfig);
        set_config('pagesize', (int)trim($config->pagesize), $this->pluginconfig);
        set_config('contexts', trim($config->contexts), $this->pluginconfig);
        set_config('user_type', core_text::strtolower(trim($config->user_type)), $this->pluginconfig);
        set_config('user_attribute', core_text::strtolower(trim($config->user_attribute)), $this->pluginconfig);
        set_config('search_sub', $config->search_sub, $this->pluginconfig);
        set_config('opt_deref', $config->opt_deref, $this->pluginconfig);
        set_config('bind_dn', trim($config->bind_dn), $this->pluginconfig);
        set_config('bind_pw', $config->bind_pw, $this->pluginconfig);
        set_config('ldap_version', $config->ldap_version, $this->pluginconfig);
        set_config('objectclass', trim($config->objectclass), $this->pluginconfig);
        set_config('memberattribute', core_text::strtolower(trim($config->memberattribute)), $this->pluginconfig);
        set_config('memberattribute_isdn', $config->memberattribute_isdn, $this->pluginconfig);
        set_config('attrcreators', trim($config->attrcreators), $this->pluginconfig);
        set_config('groupecreators', trim($config->groupecreators), $this->pluginconfig);
        set_config('removeuser', $config->removeuser, $this->pluginconfig);

        return true;
    }

    /**
     * performs check of incoming special CAS logout calls
     * @see auth/cas/auth.php§loginpage_hook().
     */
    function check_remote_logout() {

        if (!$this->initCas()) return;

        // hooks a local static function to handle logoutRequests.
        phpCAS::setSingleSignoutCallback('auth_plugin_casent::handle_single_logout', array());

        $allowed_clients = explode(',', @$this->config->remotelogoutallowedclients);
        PhpCAS::handleLogoutRequests(@$this->config->checklogoutclient, $allowed_clients);
    }

    /**
     * Possibly useless call, standard CAS implementation should do enough logout processing.
     */
    static function handle_single_logout($ticket2logout) {
        global $DB;

        phpCAS::trace('Checking session '.$ticket2logout);
        if ($mapping = $DB->get_record('auth_casent', array('casid' => $ticket2logout))) {
            \core\session\manager::kill_user_sessions($mapping->userid);
            $DB->delete_records('auth_casent', array('casid' => $ticket2logout));
        }

        // Do whatever needed Moodle side.
    }
}
