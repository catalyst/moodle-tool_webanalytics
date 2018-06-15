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
 * Web analytics tool for google tag manager.
 *
 * @package   watool_gtagmanager
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace watool_gtagmanager\tool;

use tool_webanalytics\tool\tool_base;

defined('MOODLE_INTERNAL') || die();

class tool extends tool_base {

    /**
     * @inheritdoc
     */
    public function get_tracking_code() {
        global $OUTPUT;

        $settings = $this->record->get_property('settings');

        $template = new \stdClass();
        $template->analyticsid = $settings['siteid'];

        $this->add_no_script_code();

        return $OUTPUT->render_from_template('watool_gtagmanager/tracking_code', $template);
    }

    /**
     * Additionally, paste no script code immediately after the opening <body> tag as suggested in docs.
     */
    protected function add_no_script_code() {
        global $CFG;

        if ($this->should_track()) {
            // Remove existing code to avoid duplicates.
            $re = '/' .$this->get_start() . '[\s\S]*' . $this->get_end() . '/m';
            $replaced = preg_replace($re, '', $CFG->additionalhtmltopofbody);

            if ($CFG->additionalhtmltopofbody != $replaced) {
                set_config('additionalhtmltopofbody', $replaced);
            }

            $CFG->additionalhtmltopofbody .= $this->get_start() . $this->get_noscript_code()  . $this->get_end();
        }
    }

    /**
     * Build noscript code.
     *
     * @return string
     */
    protected function get_noscript_code() {
        global $OUTPUT;

        $settings = $this->record->get_property('settings');

        $template = new \stdClass();
        $template->analyticsid = $settings['siteid'];

        return $OUTPUT->render_from_template('watool_gtagmanager/noscript_code', $template);
    }

    /**
     * @inheritdoc
     */
    public function form_definition_after_data(\MoodleQuickForm &$mform) {
        // We don't use this setting as well. Safe to remove and have default value in DB.
        $mform->removeElement('cleanurl');

        // Documentation suggests to have the code as high in the <head> of the page as possible.
        // Let's allow a head location only.
        $mform->removeElement('location');

        $choices = array(
            'head' => get_string('head', 'tool_webanalytics'),
        );

        $element = $mform->createElement('select', 'location', get_string('location', 'tool_webanalytics'), $choices);
        $mform->insertElementBefore($element, 'trackadmin');
        $mform->addHelpButton('location', 'location', 'tool_webanalytics');
        $mform->setType('location', PARAM_TEXT);
    }

    /**
     * @inheritdoc
     */
    public function form_add_settings_elements(\MoodleQuickForm &$mform) {
        $mform->addElement('text', 'siteid', get_string('siteid', 'watool_gtagmanager'));
        $mform->addHelpButton('siteid', 'siteid', 'watool_gtagmanager');
        $mform->setType('siteid', PARAM_TEXT);
        $mform->addRule('siteid', get_string('required'), 'required', null, 'client');
    }

    /**
     * @inheritdoc
     */
    public function form_validate(&$data, &$files, &$errors) {
        if (empty($data['siteid'])) {
            $errors['siteid'] = get_string('error:siteid', 'watool_gtagmanager');
        }
    }

    /**
     * @inheritdoc
     */
    public function form_build_settings(\stdClass $data) {
        $settings = [];
        $settings['siteid']  = isset($data->siteid) ? $data->siteid : '';

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function form_set_data(\stdClass &$data) {
        $data->siteid = isset($data->settings['siteid']) ? $data->settings['siteid'] : '';
    }
}
