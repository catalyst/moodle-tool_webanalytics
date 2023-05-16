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
 * Plugin manager class.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics;

use tool_webanalytics\plugininfo\watool;

defined('MOODLE_INTERNAL') || die;

/**
 * Plugin manager.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_manager {
    /**
     * Plugins type.
     */
    const PLUGIN_TYPE = 'watool';

    /**
     * Required class for plugins.
     */
    const TOOL_CLASS = 'tool\tool';

    /**
     * Required file for plugins.
     */
    const TOOL_FILE = 'classes/tool/tool.php';

    /**
     * Required interface to implement.
     */
    const TOOL_INTERFACE = 'tool_webanalytics\tool\tool_interface';

    /**
     * A singleton instance of this class.
     * @var \tool_webanalytics\plugin_manager
     */
    private static $instance;

    /**
     * A list of enabled plugin.
     * @var \tool_webanalytics\plugininfo\watool[]
     */
    private static $plugins;


    /**
     * Direct initiation not allowed, use the factory method {@see self::instance()}
     */
    protected function __construct() {
    }

    /**
     * Sorry, this is singleton
     */
    protected function __clone() {
    }

    /**
     * Factory method for this class .
     *
     * @return \tool_webanalytics\plugin_manager the singleton instance
     */
    public static function instance(): plugin_manager {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Check if a plugin with provided name is enabled.
     *
     * @param string $name A name of the plugin.
     *
     * @return bool
     */
    public function is_plugin_enabled($name): bool {
        $plugins = $this->get_enabled_plugins();

        return isset($plugins[$name]);
    }

    /**
     * Return a list of all enabled plugins.
     *
     * @return \tool_webanalytics\plugininfo\watool[]
     */
    public function get_enabled_plugins(): array {
        if (is_null(static::$plugins)) {
            static::$plugins = $this->build_plugins();
        }

        return static::$plugins;
    }

    /**
     * @param string $type
     * @return watool|null
     */
    public function get_enabled_plugin_by_type(string $type): ?watool {
        if (is_null(static::$plugins)) {
            static::$plugins = $this->build_plugins();
        }

        return static::$plugins[$type];
    }

    /**
     * Build a list of enabled plugins.
     *
     * @return \core\plugininfo\base[]
     */
    protected function build_plugins(): array {
        $plugins = [];

        $pluginswithclass = \core_component::get_plugin_list_with_class(self::PLUGIN_TYPE, self::TOOL_CLASS);
        $pluginswithfile = \core_component::get_plugin_list_with_file(self::PLUGIN_TYPE, self::TOOL_FILE);
        $pluginsinstalled = \core_plugin_manager::instance()->get_plugins_of_type(self::PLUGIN_TYPE);

        foreach ($pluginsinstalled as $name => $plugin) {
            $classname = self::PLUGIN_TYPE . '_' . $name;
            $interfaces = class_implements($pluginswithclass[$classname]);

            if (array_key_exists($name, $pluginswithfile) && array_key_exists(self::TOOL_INTERFACE, $interfaces)) {
                $plugins[$name] = $plugin;
            }
        }

        return $plugins;
    }

    /**
     * Get all sub-plugins that support auto provisioning.
     *
     * @return \core\plugininfo\base[]
     */
    public function get_auto_provision_type_plugins(): array {
        return array_filter($this->build_plugins(), static function($plugin) {
            $classes = \core_component::get_component_classes_in_namespace('watool_' . $plugin->name, 'tool');
            if (!$class = array_key_first($classes)) {
                return false;
            }
            $method = 'supports_auto_provision';
            if (!method_exists($class, $method)) {
                return false;
            }
            return $class::$method();
        }
        );
    }
}
