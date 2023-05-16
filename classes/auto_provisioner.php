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
 * Auto-provisioning service.
 *
 * @package   tool_webanalytics
 * @author    Simon Adams (simon.adams@catalyst-eu.net)
 * @copyright 2023 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics;

use tool_webanalytics\plugininfo\watool;

class auto_provisioner {

    /**
     * Get all plugin types that support provisioning and are ready to provision. Then attempt an auto-provision.
     *
     * @return void
     */
    public static function auto_provision(): void {
        $autoprovisionable = plugin_manager::instance()->get_auto_provision_type_plugins();

        /** @var watool $tool */
        foreach ($autoprovisionable as $tool) {
            $class = $tool->get_tool_classname();
            if ($class::can_auto_provision()) {
                $class::auto_provision($tool->get_client(get_config("watool_{$tool->name}")));
            }
        }
    }
}
