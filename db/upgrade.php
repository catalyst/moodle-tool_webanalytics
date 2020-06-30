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

use \tool_webanalytics\records_manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion The old version of the plugin
 * @return bool
 */
function xmldb_tool_webanalytics_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2020063001) {
        // Apply new matomo settings for existing matomo tools.

        $records = $DB->get_records(records_manager::TABLE_NAME, ['type' => 'matomo']);
        foreach ($records as $record) {
            $settings = unserialize($record->settings);
            $settings['userid'] = 1;
            $settings['usefield'] = 'id';
            $record->settings = serialize($settings);
            $DB->update_record(records_manager::TABLE_NAME, $record);
        }

        upgrade_plugin_savepoint(true, 2020063001, 'tool', 'webanalytics');
    }

    return true;
}
