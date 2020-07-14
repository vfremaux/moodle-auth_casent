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
 * Strings for component 'auth_cas', language 'en'.
 *
 * @package   auth_casent
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['privacy:metadata'] = 'This plugin has no direct user data hold by itself.';

$string['accesNOCAS'] = 'Local users only';
$string['auth_cas_checklogoutclient'] = 'Check remote client for logout requests';
$string['auth_cas_checklogoutclientdesc'] = 'If enabled, incoming IPs are checked for accepting remote Logout Requests. You need fill the next field.';
$string['auth_cas_nologoutatallforcasusers'] = 'No logout at all for CAS users';
$string['auth_cas_nologoutatallforcasusersdesc'] = 'If enabled, there is no possibility to logout for CAS users, Manual users still will see the logout link. CAS users should kill session by login out from the master portal session.';
$string['auth_cas_remotelogoutallowedclients'] = 'Remote logout allowed clients';
$string['auth_cas_remotelogoutallowedclientsdesc'] = 'Give a comma separated list of allowed IP from which LogoutRequests will be accepted';
$string['auth_casentdescription'] = 'This plugin is a special CAS auth override for integrating in massive scholar ENT.';
$string['capture_cas_settings'] = 'Capture all cas settings and users and activate';
$string['locallogout'] = 'Local logout';
$string['locallogoutmessage'] = 'You have disconnected local session. to use the LMS again, use your portal link. You can close this window now.';
$string['locallogoutmessage2'] = 'Note that your master portal session is still alive. For more security, you should close entirely your browser when closing your working session.';
$string['locallogouttitle'] = 'Local logout';
$string['nocasconnect'] = 'No CAS connection available';
$string['nocasconnectmailbody_tpl'] = 'CAS could not be reached at %%CASURL%% or does not answer at %%SITE%%';
$string['nocasconnectmailtitle_tpl'] = '[%%SITE%%] CAS connexion failure';
$string['nocasconnectmessage'] = 'The authentification server is down or not reachable at the moment.';
$string['pluginname'] = 'CAS server (SSO) For ENT';
$string['quick_config'] = 'Quick config';
// $string['remotelogoutmessage'] = 'You have disconnected your master session. to use the LMS again, use your portal link. You can close this window now.';
$string['remotelogoutmessage'] = 'Welcome to Moodle.<br/<br/>to use this service please connect on the central ATRIUM postal. Use <a href="/auth/casent/redirecttocas.php">this link</a>';
$string['unknownincomingmailbody_tpl'] = '%%USERNAME%% was authentified by CAS but is not kown as user on %%SITE%%';
$string['unknownincomingmailtitle_tpl'] = '[%%SITE%%] CAS Unknown incomming user';
$string['unknownincomingmessage'] = 'You seem being successfully authentified to the CAS server we are using, but we do not know you locally. this may be due to a failure of our user account synchronisation, or your account may not yet be propagated to this site. Administrators have been informed of your attempt.';
$string['unknownincominguser'] = 'Authentified user is not known locally.';
