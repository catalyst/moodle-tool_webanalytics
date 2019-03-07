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
 * Version information.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface describes records manager behaviour.
 */
interface records_manager_interface {

    /**
     * Returns single web analytics record.
     *
     * @param int $id Analytics record ID.
     *
     * @return mixed
     */
    public function get($id);

    /**
     * Returns all existing web analytics records.
     *
     * @return array
     */
    public function get_all();

    /**
     * Returns all existing enabled web analytics records.
     *
     * @return array
     */
    public function get_enabled();

    /**
     * Saves web analytics data.
     *
     * @param \tool_webanalytics\record_interface $tool
     *
     * @return int ID of the analytics.
     */
    public function save(record_interface $tool);

    /**
     * Delete analytics.
     *
     * @param int $id Analytics record ID.
     *
     * @return void
     */
    public function delete($id);

    /**
     * Check if records manager is ready for retrieving records.
     *
     * @return bool
     */
    public function is_ready();
}
