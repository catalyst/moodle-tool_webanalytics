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
 * Injector class.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace tool_webanalytics;

defined('MOODLE_INTERNAL') || die;

class injector {
    /**
     * @var bool
     */
    private static $injected = false;

    /**
     * Inject Web analytics tracking code for all tools.
     */
    public static function inject() {
        if (self::$injected) {
            return;
        }

        self::$injected = true;

        $manager = new records_manager();
        $records = $manager->get_all();
        $plugins = plugin_manager::instance()->get_enabled_plugins();

        if (!empty($records) && !empty($plugins)) {
            foreach ($records as $record) {
                if ($record->is_enabled()) {
                    $type = $record->get_property('type');
                    $tool = $plugins[$type]->get_tool_instance($record);
                    $tool->insert_tracking();
                }
            }
        }
    }

    /**
     * Reset injected state.
     */
    public static function reset() {
        self::$injected = false;
    }
}
