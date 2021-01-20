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
 * Webservice class
 *
 * @package   tool_webanalytics
 * @author    Andreas Stephan (andreas.stephan@rz.uni-regensburg.de)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

class tool_webanalytics_external extends external_api
{
    /**
     * description of request paramter
     *
     * @return external_function_parameters
     */
    public static function get_categories_parameters()
    {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'The search query', VALUE_REQUIRED)
        ]);
    }

    /**
     * fetches categories by partial name
     *
     * @param $query
     * @return false|string
     * @throws dml_exception
     */
    public static function get_categories($query)
    {
        global $DB;

        $like = $DB->sql_like('name', ':value', false);
        $params = ['value' => '%' . $query . '%'];
        $categories = $DB->get_records_select('course_categories', $like, $params, 'name', 'name, id', 0, 5);
        
        return json_encode($categories);
    }

    /**
     * description of response
     *
     * @return external_value
     */
    public static function get_categories_returns()
    {
        return new external_value(PARAM_RAW, 'The updated JSON output');
    }
}
