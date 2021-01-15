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
 * Record class.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_webanalytics;

use coding_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class analytics described single analytics built from DB record.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class record implements record_interface {

    /**
     * Analytics ID.
     *
     * @var int|null
     */
    protected $id = null;

    /**
     * Shows if an analytics is enabled.
     *
     * @var int
     */
    protected $enabled = 0;

    /**
     * Analytics name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Analytics location on the page.
     *
     * @var string
     */
    protected $location = 'head';

    /**
     * Type of an analytics.
     *
     * @var string
     */
    protected $type = null;

    /**
     * Should track admins?
     *
     * @var string
     */
    protected $trackadmin = 0;

    /**
     * only track students
     *
     * @var string
     */
    protected $track_only_students = 0;

    /**
     * only track these categories
     *
     * @var string
     */
    protected $categories = array();

    /**
     * Should use a clean URL?
     *
     * @var string
     */
    protected $cleanurl = 0;

    /**
     * Custom settings.
     *
     * @var array
     */
    protected $settings = array();

    /**
     * Constructor.
     *
     * @param stdClass $data Data to build an analytics from.
     *
     * @throws coding_exception
     */
    public function __construct(stdClass $data) {
        foreach ($data as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Check if an analytics is enabled.
     *
     * @return bool
     */
    public function is_enabled() {
        return !empty($this->get_property('enabled'));
    }

    /**
     * Return property value.
     *
     * @param string $name Property name.
     *
     * @return mixed Property value.
     *
     * @throws \coding_exception If invalid property requested.
     */
    public function get_property($name) {
        if (!property_exists($this, $name)) {
            throw new coding_exception('Requested invalid property.', $name);
        }

        if ($name == 'settings') {
            return $this->get_settings();
        }

        return $this->$name;
    }


    /**
     * Export record for inserting/updating it in DB.
     *
     * @return \stdClass
     */
    public function export() {
        $dbrecord = new stdClass();

        $dbrecord->id = $this->get_property('id');
        $dbrecord->name = $this->get_property('name');
        $dbrecord->enabled = $this->get_property('enabled');
        $dbrecord->location = $this->get_property('location');
        $dbrecord->type = $this->get_property('type');
        $dbrecord->trackadmin = $this->get_property('trackadmin');
        $dbrecord->track_only_students = $this->get_property('track_only_students');
        $dbrecord->categories = $this->get_property('categories');
        $dbrecord->cleanurl = $this->get_property('cleanurl');
        $dbrecord->settings = $this->get_settings();

        return $dbrecord;
    }

    /**
     * Return dimensions.
     *
     * @return array Settings array.
     */
    protected function get_settings() {
        if (!is_array($this->settings)) {
            $this->settings = array();
        }

        return $this->settings;
    }
}
