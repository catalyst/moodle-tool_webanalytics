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

use tool_webanalytics\tool\tool_base;

defined('MOODLE_INTERNAL') || die();

class tool extends tool_base {

    /**
     * @inheritdoc
     */
    public function get_tracking_code() {
        global $OUTPUT, $USER, $PAGE;

        $settings = $this->record->get_property('settings');

        $template = new \stdClass();
        $template->analyticsid = $settings['siteid'];

        if (!empty($this->record->get_property('cleanurl'))) {
            $template->addition = "{'hitType' : 'pageview',
                'page' : '" . $this->trackurl(true, true) . "',
                'title' : '" . addslashes(format_string($PAGE->heading)) . "'
                }";
        } else {
            $template->addition = "'pageview'";
        }

        if (!empty($settings['userid']) && !empty($USER->id)) {
            $template->userid = $USER->id;
        }

        return $OUTPUT->render_from_template('watool_guniversal/tracking_code', $template);
    }

    /**
     * @inheritdoc
     */
    public function form_add_settings_elements(\MoodleQuickForm &$mform) {
        $mform->addElement('text', 'siteid', get_string('siteid', 'watool_guniversal'));
        $mform->addHelpButton('siteid', 'siteid', 'watool_guniversal');
        $mform->setType('siteid', PARAM_TEXT);
        $mform->addRule('siteid', get_string('required'), 'required', null, 'client');

        $mform->addElement('checkbox', 'userid', get_string('userid', 'watool_guniversal'));
        $mform->addHelpButton('userid', 'userid', 'watool_guniversal');
    }

    /**
     * @inheritdoc
     */
    public function form_validate(&$data, &$files, &$errors) {
        if (empty($data['siteid'])) {
            $errors['siteid'] = get_string('error:siteid', 'watool_guniversal');
        }
    }

    /**
     * @inheritdoc
     */
    public function form_build_settings(\stdClass $data) {
        $settings = [];
        $settings['siteid']  = isset($data->siteid) ? $data->siteid : '';
        $settings['userid'] = isset($data->userid) ? $data->userid : 0;

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function form_set_data(\stdClass &$data) {
        $data->siteid = isset($data->settings['siteid']) ? $data->settings['siteid'] : '';
        $data->userid = isset($data->settings['userid']) ? $data->settings['userid'] : 0;
    }
}
