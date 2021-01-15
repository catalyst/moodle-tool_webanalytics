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
 * Web analytics abstract tool class.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics\tool;

use tool_webanalytics\record_interface;

defined('MOODLE_INTERNAL') || die();

/**
 * Web analytics abstract tool class.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class tool_base implements tool_interface
{
    /**
     * @var \tool_webanalytics\record
     */
    protected $record;

    /**
     * Constructor.
     *
     * @param \tool_webanalytics\record_interface $record
     */
    public function __construct(record_interface $record)
    {
        $this->record = $record;
    }

    /**
     * Check if we should track.
     *
     * @return bool
     */
    public function should_track()
    {
        // check if user is currently inside a category which has to be tracked
        $is_in_category = false;
        $check_categories = $this->record->get_property('categories');
        if (!empty($check_categories)) {
            $is_in_category = $this->is_in_category(explode(",", $check_categories));
        }

        // check if user is a student and currently on a course site?
        $is_student = false;
        $check_track_only_students = $this->record->get_property('track_only_students');
        if ($check_track_only_students) {
            $is_student = $this->is_student();
        }

        if ($check_categories && !$check_track_only_students) {
            return $is_in_category && $this->is_site_admin();

        } else if (!$check_categories && $check_track_only_students) {
            return $is_student && $this->is_site_admin();

        } else if ($check_categories && $check_track_only_students) {
            return $is_in_category && $is_in_category && $this->is_site_admin();

        } else {
            return $this->is_site_admin();
        }
    }

    /**
     * Add settings elements to Web Analytics Tool form.
     *
     * @param \MoodleQuickForm $mform Web Analytics Tool form.
     *
     * @return void
     */
    public function form_definition_after_data(\MoodleQuickForm &$mform)
    {

    }

    /**
     * A helper to build location config string.
     *
     * @return string
     */
    protected final function build_location()
    {
        return "additionalhtml" . $this->record->get_property('location');
    }

    /**
     * Insert tracking code.
     *
     * @return void
     */
    public final function insert_tracking()
    {
        global $CFG;

        if ($this->should_track()) {
            $location = $this->build_location();
            $this->remove_existing_tracking_code();
            $CFG->$location .= $this->get_start() . $this->get_tracking_code() . $this->get_end();
        }
    }

    /**
     * check if siteadmins should be tracked
     *
     * @return boolean
     */
    private function is_site_admin()
    {
        if (!is_siteadmin()) {
            return true;
        }


        return $this->record->get_property('trackadmin') == 1;
    }


    /**
     * check if user is currently inside one of the selected categories
     *
     * @param $categories
     * @return boolean
     */
    private function is_in_category($categories)
    {
        global $COURSE, $DB;

        $category = $DB->get_record('course_categories', array('id' => $COURSE->category));

        if (!is_object($category)) {
            return;
        }

        $categories_path = array_values(array_filter(explode("/", $category->path)));
        foreach ($categories as $c) {
            if (in_array($c, $categories_path)) {
                return true;
            }
        }
        return;
    }

    /**
     * checks if user is inside a course and is a student
     *
     * @return  boolean
     */
    private function is_student()
    {
        global $PAGE, $USER;

        // is user inside a course page right now?
        if (!in_array($PAGE->pagelayout, ['course', 'incourse'])) {
            return;
        }

        // is user a student inside course
        $context = $PAGE->context;
        $roles = array_values(get_user_roles($context, $USER->id));
        return in_array("student", array_column($roles, 'shortname'));
    }

    /**
     * Remove existing tracking code to avoid duplicates.
     */
    protected
    function remove_existing_tracking_code()
    {
        global $CFG;

        $location = $this->build_location();

        $re = '/' . $this->get_start() . '[\s\S]*' . $this->get_end() . '/m';
        $replaced = preg_replace($re, '', $CFG->$location);

        if ($CFG->$location != $replaced) {
            set_config($location, $replaced);
        }
    }

    /**
     * Get a string snippet to be able to find where the code starts on the page.
     *
     * @return string
     */
    protected
    function get_start()
    {
        return '<!-- WEB ANALYTICS ' . $this->record->get_property('id') . ' START -->';
    }

    /**
     * Get a string snippet to be able to find where the code ends on the page.
     *
     * @return string
     */
    protected
    function get_end()
    {
        return '<!-- WEB ANALYTICS ' . $this->record->get_property('id') . ' END -->';
    }

    /**
     * Encode a substring if required.
     *
     * @param string $input The string that might be encoded.
     * @param boolean $encode Whether to encode the URL.
     * @return string
     */
    protected
    function might_encode($input, $encode)
    {
        if (!$encode) {
            return str_replace("'", "\'", $input);
        }

        return urlencode($input);
    }

    /**
     * Helper function to get the Tracking URL for the request.
     *
     * @param bool|int $urlencode Whether to encode URLs.
     * @param bool|int $leadingslash Whether to add a leading slash to the URL.
     * @return string A URL to use for tracking.
     */
    public
    function trackurl($urlencode = false, $leadingslash = false)
    {
        global $DB, $PAGE;

        $pageinfo = get_context_info_array($PAGE->context->id);
        $trackurl = "";

        if ($leadingslash) {
            $trackurl .= "/";
        }

        // Adds course category name.
        if (isset($pageinfo[1]->category)) {
            if ($category = $DB->get_record('course_categories', ['id' => $pageinfo[1]->category])
            ) {
                $cats = explode("/", $category->path);
                foreach (array_filter($cats) as $cat) {
                    if ($categorydepth = $DB->get_record("course_categories", ["id" => $cat])) {
                        $trackurl .= self::might_encode($categorydepth->name, $urlencode) . '/';
                    }
                }
            }
        }

        // Adds course full name.
        if (isset($pageinfo[1]->fullname)) {
            if (isset($pageinfo[2]->name)) {
                $trackurl .= self::might_encode($pageinfo[1]->fullname, $urlencode) . '/';
            } else {
                $trackurl .= self::might_encode($pageinfo[1]->fullname, $urlencode);
                $trackurl .= '/';
                if ($PAGE->user_is_editing()) {
                    $trackurl .= get_string('edit', 'tool_webanalytics');
                } else {
                    $trackurl .= get_string('view', 'tool_webanalytics');
                }
            }
        }

        // Adds activity name.
        if (isset($pageinfo[2]->name)) {
            $trackurl .= self::might_encode($pageinfo[2]->modname, $urlencode);
            $trackurl .= '/';
            $trackurl .= self::might_encode($pageinfo[2]->name, $urlencode);
        }

        return $trackurl;
    }

}
