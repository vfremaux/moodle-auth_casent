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
 * CAS authentication plugin upgrade code
 *
 * @package    auth_cas
 * @copyright  2015 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_casent_upgrade($oldversion) {
    global $DB;
    
    // Moodle v2.5.0 release upgrade line
    // Put any upgrade step following this

    // MDL-39323 New setting in 2.5, make sure it's defined.

    // Moodle v2.6.0 release upgrade line.
    // Put any upgrade step following this.

    $result = true;
    $dbman = $DB->get_manager();

    if ($oldversion < 2015012900) {

        // Changing precision of field vdbpass on table block_vmoodle to (32).
        $table = new xmldb_table('auth_casent');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('casid', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'id');
        $table->add_field('sesskey', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'casid');

        // Adding keys to table referentiel_task.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding index to table referentiel_notification_queue.
        $table->add_index('casmapping', XMLDB_INDEX_UNIQUE, array('casid', 'userid'));

        // Launch create table for referentiel_task.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table, true, true);
        }

        // Casent savepoint reached.
        upgrade_plugin_savepoint(true, 2015012900, 'auth', 'casent');
    }

    if ($oldversion < 2015020600) {

        $table = new xmldb_table('auth_casent');
        $field = new xmldb_field('sesskey');
    /// Launch add field referentiel_referentiel
        if ($dbman->field_exists($table, $field)) {
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'casid');
            $dbman->rename_field($table, $field, 'userid', false);
        }

        // Casent savepoint reached.
        upgrade_plugin_savepoint(true, 2015020600, 'auth', 'casent');

    }

    return $result;

}
