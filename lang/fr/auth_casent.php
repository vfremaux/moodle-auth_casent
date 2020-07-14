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
 * Strings for component 'auth_cas', language 'fr'.
 *
 * @package   auth_casent
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['privacy:metadata'] = 'Ce plugin ne détient pas directement de données utilisateur';

$string['accesNOCAS'] = 'Accès pour les utilisateurs locaux';
$string['auth_cas_checklogoutclient'] = 'Vérifier le client distant pour les demandes de déconnexion';
$string['auth_cas_checklogoutclientdesc'] = 'Si activé, les IPs de provenance des demandes de déconnexion distantes sont vérifiées. Veuillez remplir la liste d\'IPs autorisées ci-dessous.';
$string['auth_cas_nologoutatallforcasusers'] = 'Pas de déconnexion pour les utilisateurs CAS';
$string['auth_cas_nologoutatallforcasusersdesc'] = 'Si activé, les utilisateurs CAS ne peuvent se déconnecter directement de Moodle. La session se termine par la déconnexion du portail de l\'ENT. Ceci suppose une mise en oeuvre complète des Déconnexions Distantes CAS. Les utilisateurs manuels peuvent toujours se déconnecter.';
$string['auth_cas_remotelogoutallowedclients'] = 'Clients distants autorisés pour les déconnexions';
$string['auth_cas_remotelogoutallowedclientsdesc'] = 'Donner une liste séparée par des virgules des adresses IP dont les demandes de déconnexion distantes seront acceptées.';
$string['auth_casentdescription'] = 'Ce plugin est une variante du plugin d\'authentification CAS standard pour les spécificités des intégrations dans les ENT.';
$string['capture_cas_settings'] = 'Capturer tous les utilisateurs CAS, réglages et activer le CAS ENT.';
$string['locallogout'] = 'Déconnexion locale';
$string['locallogoutmessage'] = 'Vous avez refermé votre session locale sur la plate-forme pédagogique. Pour rentrer à nouveau sur Moodle, fermez cette fenêtre et utilisez à nouveau le lien dans votre portail Atrium.';
$string['locallogoutmessage2'] = 'Notez que votre session principale sur Atrium est toujours active. Pour plus de sécurité, nous vous conseillons de refermer complètement votre navigateur à la fin de votre session de travail sur Atrium.';
$string['locallogouttitle'] = 'Déconnexion locale';
$string['nocasconnect'] = 'Pas de connexion CAS disponible';
$string['nocasconnectmailsubject_tpl'] = 'Erreur connexion serveur CAS %%CASURL%% à partir de %%SITE%%';
$string['nocasconnectmailtitle_tpl'] = '[%%SITE%%] Erreur connexion CAS';
$string['nocasconnectmessage'] = 'Le serveur central d\'authentification n\'est pas disponible ou joignable. Nous ne pouvons vous connecter à Moodle. Cette situation a été signalée aux administrateur. Merci de bien vouloir patienter jusqu\'au rétablissement du service.';
$string['pluginname'] = 'Server CAS (SSO) Pour les ENTs';
$string['quick_config'] = 'Configuration rapide';
$string['remotelogoutmessage'] = 'Bienvenue sur Moodle.<br/><br/> Pour vous connecter à ce service, veuillez vous authentifier sur le portail ATRIUM en suivant ce lien : <a href="/auth/casent/redirecttocas.php">Connexion ATRIUM</a>';
$string['unknownincomingmailbody_tpl'] = 'Un utilisateur %%USERNAME%% authentifié du CAS est inconu sur %%SITE%%';
$string['unknownincomingmailtitle_tpl'] = '[%%SITE%%] CAS Utilisateur entrant inconnu';
$string['unknownincomingmessage'] = '<p>Bonjour.</p> <p>vous semblez avoir été correctement authentifié par le serveur auquel nous
sommes rattaché, mais votre compte ne semble pas encore exister sur ce site.</p>
<p>Cela peut être du au fait que votre compte est nouveau et ne nous a pas été encore transmis, ou que ce site a un problème dans son mécanisme
d\'alimentation des comptes.</p>
<p>Les administrateurs ont été prévenus de la situation. si vous n\'avez pas de nouvelles dans les 48 heures qui suivent cet incident, merci 
de remonter un signalement de défaut dans le gichet de support Atrium.</p>';
$string['unknownincominguser'] = 'Utilisateur authentifié inconnu localement';
// $string['remotelogoutmessage'] = 'Vous avez refermé votre session principale sur le portail. Pour rentrer à nouveau sur Moodle, fermez cette fenêtre et ouvrez une nouvelle session sur votre portail Atrium.';
// $string['remotelogoutmessage'] = 'Vous utilisez un service d\'accès réservé aux utilisateurs locaux de ce site. Si vous n\'êtes pas l\'administrateur local de cette plate-forme, cliquez sur <a href="/auth/casent/redirect.php">ce lien</a> pour vous connecter à l\'ENT.';
