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
 * Edit form.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();


class edit extends moodleform {

    /**
     * Return form object.
     *
     * @return \MoodleQuickForm
     */
    public function get_form() {
        return $this->_form;
    }

    /**
     * @inheritdoc
     *
     * @see moodleform::definition()
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);


        $choices = [];
        $plugins = \core_plugin_manager::instance()->get_plugins_of_type('watool');
        foreach ($plugins as $plugin) {
            $choices[$plugin->name] = $plugin->displayname;
        }

        $mform->addElement('select', 'type', get_string('type', 'tool_webanalytics'), $choices);
        $mform->addHelpButton('type', 'type', 'tool_webanalytics');
        $mform->setType('type', PARAM_TEXT);

        $mform->addElement('text', 'name', get_string('name', 'tool_webanalytics'));
        $mform->addHelpButton('name', 'name', 'tool_webanalytics');
        $mform->setType('name', PARAM_TEXT);
        $this->_form->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('checkbox', 'enabled', get_string('enabled', 'tool_webanalytics'));
        $mform->addHelpButton('enabled', 'enabled', 'tool_webanalytics');
        $mform->setDefault('enabled', 1);

        $choices = array(
            'head' => get_string('head', 'tool_webanalytics'),
            'topofbody' => get_string('topofbody', 'tool_webanalytics'),
            'footer' => get_string('footer', 'tool_webanalytics'),
        );

        $mform->addElement('select', 'location', get_string('location', 'tool_webanalytics'), $choices);
        $mform->addHelpButton('location', 'location', 'tool_webanalytics');
        $mform->setType('location', PARAM_TEXT);


        $this->add_action_buttons();
    }

    /**
     * @inheritdoc
     *
     * @see moodleform::validation()
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // TODO: check if we have the same record.

        return $errors;
    }

    /**
     * @inheritdoc
     *
     * @see moodleform::get_data()
     */
    public function get_data() {
        $data = parent::get_data();

        return $data;
    }

    /**
     * @inheritdoc
     *
     * @see moodleform::definition_after_data()
     */
    public function definition_after_data() {
        parent::definition_after_data();

        $mform = $this->_form;

        $mform->freeze(array('type'));

    }

}
