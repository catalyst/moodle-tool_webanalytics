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

use stdClass;
use tool_webanalytics\client_base;
use tool_webanalytics\record;
use tool_webanalytics\record_interface;

defined('MOODLE_INTERNAL') || die();

/**
 * Web analytics abstract tool class.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class tool_base implements tool_interface {
    /**
     * @var \tool_webanalytics\record
     */
    protected $record;

    /**
     * Constructor.
     *
     * @param \tool_webanalytics\record_interface $record
     */
    public function __construct(record_interface $record) {
        $this->record = $record;
    }

    /**
     * Check if we should track.
     *
     * @return bool
     */
    public function should_track(): bool {
        if (!is_siteadmin()) {
            return true;
        }

        return $this->record->get_property('trackadmin') == 1;
    }

    /**
     * Add settings elements to Web Analytics Tool form.
     *
     * @param \MoodleQuickForm $mform Web Analytics Tool form.
     *
     * @return void
     */
    public function form_definition_after_data(\MoodleQuickForm &$mform) {

    }

    /**
     * Encode a substring if required.
     *
     * @param string  $input  The string that might be encoded.
     * @param boolean $encode Whether to encode the URL.
     * @return string
     */
    protected function might_encode($input, $encode): string {
        if (!$encode) {
            return str_replace("'", "\'", $input);
        }

        return urlencode($input);
    }

    /**
     * Helper function to get the Tracking URL for the request.
     *
     * @param bool|int $urlencode    Whether to encode URLs.
     * @param bool|int $leadingslash Whether to add a leading slash to the URL.
     * @return string A URL to use for tracking.
     */
    public function trackurl($urlencode = false, $leadingslash = false): string {
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
                        $trackurl .= self::might_encode($categorydepth->name, $urlencode).'/';
                    }
                }
            }
        }

        // Adds course full name.
        if (isset($pageinfo[1]->fullname)) {
            if (isset($pageinfo[2]->name)) {
                $trackurl .= self::might_encode($pageinfo[1]->fullname, $urlencode).'/';
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

    /**
     * Called from the config instance form submission.
     *
     * @param $client
     * @return int $siteid returned from the API.
     */
    public function register_site($client): int {
        return 0;
    }

    /**
     * Does the tool support auto provision over an API?
     *
     * @return bool
     */
    public static function supports_auto_provision(): bool {
        return false;
    }

    /**
     * Is the tool ready to attempt an auto provision?
     *
     * @return bool
     */
    public static function can_auto_provision(): bool {
        return false;
    }

    /**
     * Auto provision the site with the API.
     *
     * @param $client
     * @return void
     */
    public static function auto_provision($client): void {}
}
