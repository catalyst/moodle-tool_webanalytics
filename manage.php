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
 * Manage Web Analytics tools.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_webanalytics\records_manager;
use tool_webanalytics\table\tools_table;
use tool_webanalytics\plugin_manager;

require_once(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('tool_webanalytics_manage');

$createurl = new moodle_url('/admin/tool/webanalytics/edit.php');
$manageurl = new moodle_url('/admin/tool/webanalytics/manage.php');

$manager = new records_manager();

$PAGE->set_url($manageurl);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage_heading', 'tool_webanalytics'));

$plugins = plugin_manager::instance()->get_enabled_plugins();

$options = [];

foreach ($plugins as $plugin) {
    $options[$plugin->name] = $plugin->displayname;
}

$newtool = new single_select($createurl, 'type', $options);
$newtool->method = 'post';
$newtool->set_label(get_string('add_tool', 'tool_webanalytics'));

echo $OUTPUT->render($newtool);

$records = $manager->get_all();
$table = new tools_table();
$table->display($records);

echo $OUTPUT->footer();
