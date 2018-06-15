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
 * Web analytics tool interface.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics\tool;

use tool_webanalytics\record_interface;

defined('MOODLE_INTERNAL') || die();

interface tool_interface {
    /**
     * Constructor.
     *
     * @param \tool_webanalytics\record_interface $record
     */
    public function __construct(record_interface $record);

    /**
     * Check if we should track.
     *
     * @return bool
     */
    public function should_track();

    /**
     * Get tracking code to insert.
     *
     * @return string
     */
    public function get_tracking_code();

    /**
     * Insert tracking code.
     *
     * @return void
     */
    public function insert_tracking();

    /**
     * Add settings elements to Web Analytics Tool form.
     *
     * @param \MoodleQuickForm $mform Web Analytics Tool form.
     *
     * @return void
     */
    public function form_add_settings_elements(\MoodleQuickForm &$mform);

    /**
     * Modify Web Analytics Tool form after data has been set.
     *
     * @param \MoodleQuickForm $mform Web Analytics Tool form.
     *
     * @return void
     */
    public function form_definition_after_data(\MoodleQuickForm &$mform);

    /**
     * Validate submitted data to Web Analytics Tool form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param array $errors array of ("fieldname"=>error message)
     *
     * @return void
     */
    public function form_validate(&$data, &$files, &$errors);

    /**
     * Build settings array from submitted form data.
     *
     * @param \stdClass $data
     *
     * @return array
     */
    public function form_build_settings(\stdClass $data);

    /**
     * Set form data.
     *
     * @param $data
     *
     * @return void
     */
    public function form_set_data(\stdClass &$data);
}
