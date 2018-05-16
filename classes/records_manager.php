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
 * Record manager.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics;

defined('MOODLE_INTERNAL') || die();


/**
 * Class manages records in DB.
 */
class records_manager implements records_manager_interface {
    /**
     * An analytics table name table name.
     */
    const TABLE_NAME = 'tool_webanalytics';

    /**
     * Global DB object.
     *
     * @var \moodle_database
     */
    protected $db;

    /**
     * A list of all records.
     *
     * @var bool|false|mixed
     */
    protected $allrecords = false;

    /**
     * A list of enabled records.
     *
     * @var bool|false|mixed
     */
    protected $enabledrecords = false;

    /**
     * Constructor.
     */
    public function __construct() {
        global $DB;

        $this->db = $DB;
    }

    /**
     * Returns single record.
     *
     * Note: we don't want to use cache here.
     *
     * @param int $id record ID.
     *
     * @return \tool_webanalytics\record
     */
    public function get($id) {
        $record = $this->db->get_record(self::TABLE_NAME, array('id' => $id));
        if (!empty($record)) {
            $record = new record($record);
        }

        return $record;
    }

    /**
     * Returns all existing analytics records.
     *
     * @return array
     */
    public function get_all() {
        if ($this->allrecords === false) {
            $this->allrecords = $this->get_multiple();
        }

        return $this->allrecords;
    }

    /**
     * Returns all existing enabled analytics records.
     *
     * @return array
     */
    public function get_enabled() {
        if ($this->enabledrecords === false) {
            $this->enabledrecords = $this->get_multiple(array('enabled' => 1));
        }

        return $this->enabledrecords;
    }

    /**
     * Saves analytics data.
     *
     * @param \tool_webanalytics\record_interface $record
     *
     * @return int ID of the analytics.
     */
    public function save(record_interface $record) {
        $dbrecord = $record->export();

        if (empty($dbrecord->id)) {
            $dbrecord->id = $this->db->insert_record(self::TABLE_NAME, $dbrecord, true);
        } else {
            unset($dbrecord->type);
            $this->db->update_record(self::TABLE_NAME, $dbrecord);
        }

        return $dbrecord->id;
    }


    /**
     * Delete analytics.
     *
     * @param int $id Analytics record ID.
     *
     * @return void
     */
    public function delete($id) {
        $this->db->delete_records(self::TABLE_NAME, array('id' => $id));
    }

    /**
     * Get multiple records of the analytics.
     *
     * @param array $params Parameters to use in get_records functions.
     *
     * @return array A list of analytics.
     */
    protected function get_multiple($params = array()) {
        $records = $this->db->get_records(self::TABLE_NAME, $params, 'id');

        if (!empty($records)) {
            foreach ($records as $record) {
                if (!empty($record) && !empty($record->settings)) {
                    $records[$record->id] = new record($record);
                }
            }
        }

        return $records;
    }

}