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
 * Webanalytics class to wrap HTTP requests.
 *
 * @package    watool_matomo
 * @copyright  2023 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author     Simon Adams simon.adams@catalyst-eu.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics;

use curl;
use stdClass;

/**
 * Simple wrapper Http client.
 * Each plugin that needs it should implement their own class in the plugin namespace /watool_{PLUGINNAME}/client.
 * Each plugin must also ensure that the relevant client config is stored via set_config under component watool_{PLUGINNAME}.
 */
class client_base extends curl {
    /**
     * @param stdClass $config global settings to allow the API to function
     * @param array $settings optional settings for the curl client to use.
     */
    public function __construct(stdClass $config, array $settings = []) {
        $this->config = $config;
        $this->settings = $settings;
        parent::__construct($settings);
    }
}
