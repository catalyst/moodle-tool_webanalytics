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
 * @package   watool_matomo
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace watool_matomo\tool;

use tool_webanalytics\tool\tool_base;

defined('MOODLE_INTERNAL') || die();

class tool extends tool_base {
    /**
     * @inheritdoc
     */
    public function get_tracking_code() {
        global $OUTPUT, $USER;

        $settings = $this->record->get_property('settings');

        $template = new \stdClass();
        $template->siteid = $settings['siteid'];
        $template->siteurl = $settings['siteurl'];
        $template->imagetrack = $settings['imagetrack'];
        $template->userid = $USER->id;
        $template->doctitle = "";

        if (!empty($this->record->get_property('cleanurl'))) {
            $template->doctitle = "_paq.push(['setDocumentTitle', '" . $this->trackurl() . "']);\n";
        }

        return $OUTPUT->render_from_template('watool_matomo/tracking_code', $template);
    }

    /**
     * @inheritdoc
     */
    public function form_add_settings_elements(\MoodleQuickForm &$mform) {
        $mform->addElement('text', 'siteurl', get_string('siteurl', 'watool_matomo'));
        $mform->addHelpButton('siteurl', 'siteurl', 'watool_matomo');
        $mform->setType('siteurl', PARAM_TEXT);
        $mform->addRule('siteurl', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'siteid', get_string('siteid', 'watool_matomo'));
        $mform->addHelpButton('siteid', 'siteid', 'watool_matomo');
        $mform->setType('siteid', PARAM_TEXT);
        $mform->addRule('siteid', get_string('required'), 'required', null, 'client');

        $mform->addElement('checkbox', 'imagetrack', get_string('imagetrack', 'watool_matomo'));
        $mform->addHelpButton('imagetrack', 'imagetrack', 'watool_matomo');
    }

    /**
     * @inheritdoc
     */
    public function form_validate(&$data, &$files, &$errors) {
        if (empty($data['siteid'])) {
            $errors['siteid'] = get_string('error:siteid', 'watool_matomo');
        }

        if (!isset($data['siteurl']) || empty($data['siteurl'])) {
            $errors['siteurl'] = get_string('error:siteurl', 'watool_matomo');
        } else {
            if (empty(clean_param($data['siteurl'], PARAM_URL))) {
                $errors['siteurl'] = get_string('error:siteurlinvalid', 'watool_matomo');
            }

            if (preg_match("/^(http|https):\/\//", $data['siteurl'])) {
                $errors['siteurl'] = get_string('error:siteurlhttps', 'watool_matomo');
            }

            if (substr(trim($data['siteurl']), -1) == '/') {
                $errors['siteurl'] = get_string('error:siteurltrailingslash', 'watool_matomo');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function form_build_settings(\stdClass $data) {
        $settings = [];
        $settings['siteid']  = isset($data->siteid) ? $data->siteid : '';
        $settings['siteurl'] = isset($data->siteurl) ? $data->siteurl : '';
        $settings['imagetrack'] = isset($data->imagetrack) ? $data->imagetrack : 0;

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function form_set_data(\stdClass &$data) {
        $data->siteid = isset($data->settings['siteid']) ? $data->settings['siteid'] : '';
        $data->siteurl = isset($data->settings['siteurl']) ? $data->settings['siteurl'] : '';
        $data->imagetrack = isset($data->settings['imagetrack']) ? $data->settings['imagetrack'] : 0;
    }
}
