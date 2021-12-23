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
 * Upgrade script.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion The old version of the plugin
 * @return bool
 */
function xmldb_tool_webanalytics_upgrade($oldversion): bool {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020063001) {
        // Apply new matomo settings for existing matomo tools.

        $records = $DB->get_records('tool_webanalytics', ['type' => 'matomo']);
        foreach ($records as $record) {
            $settings = unserialize($record->settings);
            $settings['userid'] = 1;
            $settings['usefield'] = 'id';
            $record->settings = serialize($settings);
            $DB->update_record('tool_webanalytics', $record);
        }

        upgrade_plugin_savepoint(true, 2020063001, 'tool', 'webanalytics');
    }

    if ($oldversion < 2021052800) {
        // Drop location as we render everything in the head.
        $table = new xmldb_table('tool_webanalytics');
        $field = new xmldb_field('location');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021052800, 'tool', 'webanalytics');
    }

    if ($oldversion < 2021053100) {
        // Switching to a new CFG manager.
        $config = [];

        $records = $DB->get_records('tool_webanalytics');
        foreach ($records as $record) {
            $record->settings = unserialize($record->settings);
            $config[$record->id] = $record;
        }

        set_config('tool_anaylytics_records', serialize($config));

        // Clean up table.
        $table = new xmldb_table('tool_webanalytics');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2021053100, 'tool', 'webanalytics');
    }

    if ($oldversion < 2021122300) {
        set_config('tool_webanalytics_records', $CFG->tool_anaylytics_records);
        unset_config('tool_anaylytics_records');

        upgrade_plugin_savepoint(true, 2021122300, 'tool', 'webanalytics');
    }

    return true;
}
