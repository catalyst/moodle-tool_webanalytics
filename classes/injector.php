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

use tool_webanalytics\tool\tool_interface;

defined('MOODLE_INTERNAL') || die;

/**
 * Injector class.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class injector {

    /**
     * Inject Web analytics tracking code for all tools.
     *
     * @return string
     */
    public static function render_tracking_code(): string {
        $result = '';

        // Do not inject if being called in an ajax or cli script unless it's a unit test.
        if ((CLI_SCRIPT or AJAX_SCRIPT) && !PHPUNIT_TEST) {
            return $result;
        }

        if (!self::get_records_manager()->is_ready()) {
            return $result;
        }

        $records = self::get_records_manager()->get_enabled();
        $plugins = plugin_manager::instance()->get_enabled_plugins();

        if (!empty($records) && !empty($plugins)) {
            foreach ($records as $record) {
                $type = $record->get_property('type');
                if (key_exists($type, $plugins)) {
                    $tool = $plugins[$type]->get_tool_instance($record);
                    if ($tool->should_track()) {
                        $result .= $tool->get_tracking_code();
                    }
                }
            }
        }

        auto_provisioner::auto_provision();

        return $result;
    }

    /**
     * Get a records manager.
     *
     * @return \tool_webanalytics\records_manager_interface
     */
    public static function get_records_manager(): records_manager_interface {
        return new records_manager();
    }

}
