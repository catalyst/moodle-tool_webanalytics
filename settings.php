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
 * Settings
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if (is_siteadmin()) {
    $category = new admin_category(
            'tool_webanalytics',
            new lang_string('pluginname', 'tool_webanalytics'),
            false
    );
    $ADMIN->add('tools', $category);

    $externalpage = new admin_externalpage(
            'tool_webanalytics_manage',
            get_string('pluginname', 'tool_webanalytics'),
            new moodle_url('/admin/tool/webanalytics/manage.php')
    );
    $ADMIN->add('tool_webanalytics', $externalpage);

    foreach (core_plugin_manager::instance()->get_plugins_of_type('watool') as $plugin) {
        /** @var \tool_webanalytics\plugin_manager $plugin */
        $plugin->load_settings($ADMIN, 'tool_webanalytics', $hassiteconfig);
    }

}
