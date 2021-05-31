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
 * Record manager that uses global CFG for storing records.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics;

defined('MOODLE_INTERNAL') || die();


/**
 * Record manager that uses global CFG for storing records.
 *
 * @copyright  2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class records_manager implements records_manager_interface {
    /**
     * An analytics table name table name.
     */
    const CONFIG_NAME = 'tool_anaylytics_records';

    /**
     * Row data.
     *
     * @var \stdClass[]
     */
    private $data;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->load_data();
    }

    /**
     * Load data from the config.
     */
    private function load_data() {
        global $CFG;

        if (!isset($CFG->{self::CONFIG_NAME})) {
            $CFG->{self::CONFIG_NAME} = serialize([]);
        }

        $this->data = unserialize($CFG->{self::CONFIG_NAME});
    }

    /**
     * Save data to the config.
     */
    private function save_data() {
        set_config(self::CONFIG_NAME, serialize($this->data));
    }

    /**
     * Returns single record.
     *
     * Note: we don't want to use cache here.
     *
     * @param string $id record ID.
     *
     * @return false|\tool_webanalytics\record
     */
    public function get(string $id) : ?record {
        $record = null;

        if (!empty($this->data[$id])) {
            $record = $this->data[$id];
            $record = new record($record);
        }

        return $record;
    }

    /**
     * Returns all existing analytics records.
     *
     * @return \tool_webanalytics\record_interface[]
     */
    public function get_all(): array {
        $records = [];

        foreach ($this->data as $record) {
            if (!empty($record)) {
                $records[$record->id] = new record($record);
            }
        }

        return $records;
    }

    /**
     * Returns all existing enabled analytics records.
     *
     * @return \tool_webanalytics\record[]
     */
    public function get_enabled(): array {
        $records = [];

        foreach ($this->get_all() as $record) {
            if ($record->is_enabled()) {
                $records[$record->get_property('id')] = $record;
            }
        }

        return $records;
    }

    /**
     * Saves analytics data.
     *
     * @param \tool_webanalytics\record_interface $record
     *
     * @return string Unique ID of the analytics.
     */
    public function save(record_interface $record) : string {
        $datarecord = $record->export();

        if (empty($datarecord->id)) {
            do {
                $datarecord->id = uniqid();
            } while (key_exists($datarecord->id, $this->data));
        }

        $this->data[$datarecord->id] = $datarecord;
        $this->save_data();

        return $datarecord->id;
    }


    /**
     * Delete analytics.
     *
     * @param int $id Analytics record ID.
     *
     * @return void
     */
    public function delete($id) {
        if (isset($this->data[$id])) {
            unset($this->data[$id]);
            $this->save_data();
        }
    }

    /**
     * Check if records manager is ready for retrieving records.
     *
     * @return bool
     */
    public function is_ready(): bool {
        global $CFG;

        return isset($CFG->tool_anaylytics_records);
    }

}