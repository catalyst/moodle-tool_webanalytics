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
 * Edit Web Analytics tools.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_webanalytics\form\edit;
use tool_webanalytics\record;
use tool_webanalytics\records_manager;
use \tool_webanalytics\plugin_manager;

require_once(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('tool_webanalytics_manage');

$type = required_param('type', PARAM_ALPHAEXT);
$id = optional_param('id', 0, PARAM_ALPHANUM);

$manageurl = new moodle_url('/admin/tool/webanalytics/manage.php');

if (!plugin_manager::instance()->is_plugin_enabled($type)) {
    throw new moodle_exception('not_enabled', 'tool_webanalytics', $manageurl);
}

$action = 'create';
$record = new stdClass();
$record->type = $type;
$tool = null;
$dimensions = null;
$manager = new records_manager();

if ($id) {
    $record = $manager->get($id);
    if (empty($record)) {
        throw new moodle_exception('not_found', 'tool_webanalytics', $manageurl);
    }

    $action = 'edit';
    $settings = $record->get_property('settings');
    $record = $record->export();
}

$mform = new edit(null, ['record' => new record($record)]);
$mform->set_data($record);

if ($mform->is_cancelled()) {
    redirect($manageurl);
} else if ($data = $mform->get_data()) {
    $record = new record($data);
    $manager->save($record);
    redirect($manageurl);
}

$PAGE->navbar->add(get_string($action . '_breadcrumb', 'tool_webanalytics'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($action . '_heading', 'tool_webanalytics'));
$mform->display();
echo $OUTPUT->footer();
