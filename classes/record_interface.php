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
 * Interface to describe records.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface to describe records.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface record_interface {

    /**
     * Check if an record is enabled.
     *
     * @return bool
     */
    public function is_enabled(): bool;

    /**
     * Return property value.
     *
     * @param string $name Property name.
     * @return mixed Property value.
     */
    public function get_property($name);

    /**
     * Set the property value.
     *
     * @param string $name Property name.
     */
    public function set_property($name, $value);

    /**
     * Export the record.
     *
     * @return \stdClass
     */
    public function export(): \stdClass;

}
