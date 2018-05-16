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

namespace watool_guniversal\tool;

use tool_webanalytics\record_interface;
use tool_webanalytics\tool\tool_interface;

defined('MOODLE_INTERNAL') || die();

class tool implements tool_interface {
    /**
     * @var \tool_webanalytics\record
     */
    protected $record;

    /**
     * @inheritdoc
     */
    public function __construct(record_interface $record) {
        $this->record = $record;
    }

    /**
     * @inheritdoc
     */
    public function insert_tracking() {
        global $CFG, $OUTPUT;

        $settings = $this->record->get_property('settings');

        $template = new \stdClass();
        $template->analyticsid = $settings['siteid'];
        $template->addition = "'pageview'";

        if ($this->should_track()) {
            $location = "additionalhtml" . $this->record->get_property('location');
            $CFG->$location .= $OUTPUT->render_from_template('watool_guniversal/tracking_code', $template);
        }
    }

    public function should_track() {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function form_add_settings_elements(\MoodleQuickForm &$mform) {
        $mform->addElement('text', 'siteid', get_string('siteid', 'local_analytics'));
        $mform->addHelpButton('siteid', 'siteid', 'local_analytics');
        $mform->setType('siteid', PARAM_TEXT);
        $mform->addRule('siteid', get_string('required'), 'required', null, 'client');
    }

    public function form_definition_after_data(\MoodleQuickForm &$mform) {

    }

    /**
     * @inheritdoc
     */
    public function form_validate_(&$data, &$files, &$errors) {
        if (empty($data['siteid'])) {
            $errors['siteid'] = 'Site ID should be set';
        }
    }

    /**
     * @inheritdoc
     */
    public function form_build_settings(\stdClass $data) {
        $settings = [];

        if (isset($data->siteid)) {
            $settings['siteid'] = $data->siteid;
        }

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function form_set_data(\stdClass &$data) {
        $data->siteid = $data->settings['siteid'];
    }
}