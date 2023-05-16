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
 * Sub plugin class.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics\plugininfo;

use core\plugininfo\base;
use stdClass;
use tool_webanalytics\record_interface;
use tool_webanalytics\tool\tool_interface;

defined('MOODLE_INTERNAL') || die();

/**
 * Sub plugin class.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class watool extends base {

    /**
     * Return a configured web analytics tool instance for this plugin.
     *
     * @param \tool_webanalytics\record_interface $record
     *
     * @return \tool_webanalytics\tool\tool_interface
     */
    public function get_tool_instance(record_interface $record): tool_interface {
        $class = '\\watool_' . $this->name . '\\tool\\tool';

        return new  $class($record);
    }

    /**
     * Gt the classname of the tool to aid in calling methods dynamically.
     *
     * @return string
     */
    public function get_tool_classname() {
        return '\\watool_' . $this->name . '\\tool\\tool';
    }

    /**
     * Load tool specific settings.
     *
     * @param \part_of_admin_tree $adminroot
     * @param $parentnodename
     * @param $hassiteconfig
     * @return void
     */
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE; // In case settings.php wants to refer to them.
        $ADMIN = $adminroot; // May be used in settings.php.
        $plugininfo = $this; // Also can be used inside settings.php.

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig || !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();
        $settings = new \admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
        include($this->full_path('settings.php')); // This may also set $settings to null.

        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    /**
     * Get the tool specific section name for our settings page.
     *
     * @return string
     */
    public function get_settings_section_name() {
        return 'watool_' . $this->name;
    }

    /**
     * @param stdClass $config
     * @return mixed|void
     */
    public function get_client(stdClass $config) {
        $class = "\watool_{$this->name}\client";
        if (class_exists($class)) {
            return new $class($config);
        }
    }
}
