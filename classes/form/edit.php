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
use tool_webanalytics\plugin_manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Edit form.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit extends moodleform {
    /**
     * Web analytics type.
     * @var string
     */
    protected $type;

    /**
     * Web analytics record.
     * @var \tool_webanalytics\record_interface
     */
    protected $record;

    /**
     * Web analytics tool of provided type.
     * @var \tool_webanalytics\tool\tool_interface
     */
    protected $tool;

    /**
     * Return form object.
     *
     * @return \MoodleQuickForm
     */
    public function get_form() {
        return $this->_form;
    }

    /**
     * Form definition.
     *
     * @see moodleform::definition()
     */
    public function definition() {
        $plugins = plugin_manager::instance()->get_enabled_plugins();

        $mform = $this->_form;
        $this->record = $this->_customdata['record'];
        $this->type = $this->record->get_property('type');
        $this->tool = $plugins[$this->type]->get_tool_instance($this->record);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);

        $choices = [];
        foreach ($plugins as $plugin) {
            $choices[$plugin->name] = $plugin->displayname;
        }

        $mform->addElement('select', 'type', get_string('type', 'tool_webanalytics'), $choices);
        $mform->setType('type', PARAM_TEXT);

        $mform->addElement('checkbox', 'enabled', get_string('enabled', 'tool_webanalytics'));
        $mform->addHelpButton('enabled', 'enabled', 'tool_webanalytics');
        $mform->setDefault('enabled', 1);

        $mform->addElement('text', 'name', get_string('name', 'tool_webanalytics'));
        $mform->addHelpButton('name', 'name', 'tool_webanalytics');
        $mform->setType('name', PARAM_TEXT);
        $this->_form->addRule('name', get_string('required'), 'required', null, 'client');

        $choices = [
            'head' => get_string('head', 'tool_webanalytics'),
            'topofbody' => get_string('topofbody', 'tool_webanalytics'),
            'footer' => get_string('footer', 'tool_webanalytics'),
        ];

        $mform->addElement('checkbox', 'trackadmin', get_string('trackadmin', 'tool_webanalytics'));
        $mform->addHelpButton('trackadmin', 'trackadmin', 'tool_webanalytics');

        $mform->addElement('checkbox', 'cleanurl', get_string('cleanurl', 'tool_webanalytics'));
        $mform->addHelpButton('cleanurl', 'cleanurl', 'tool_webanalytics');

        $this->tool->form_add_settings_elements($mform);

        $this->add_action_buttons();
    }

    /**
     * Server side validation.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $this->tool->form_validate($data, $files, $errors);

        return $errors;
    }

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * note: $slashed param removed
     *
     * @return object submitted data; NULL if not valid or not submitted or cancelled
     */
    public function get_data() {
        $data = parent::get_data();

        if (!empty($data)) {
            $data->settings = $this->tool->form_build_settings($data);
        }

        return $data;
    }

    /**
     * Load in existing data as form defaults. Usually new entry defaults are stored directly in
     * form definition (new entry form); this function is used to load in data where values
     * already exist and data is being edited (edit entry form).
     *
     * note: $slashed param removed
     *
     * @param \stdClass|array $defaultvalues object or array of default values
     */
    public function set_data($defaultvalues) {
        $this->tool->form_set_data($defaultvalues);
        parent::set_data($defaultvalues);
    }

    /**
     * Setup the form depending on current values.
     */
    public function definition_after_data() {
        parent::definition_after_data();
        $this->_form->freeze(['type']);
        $this->tool->form_definition_after_data($this->_form);
    }

}
