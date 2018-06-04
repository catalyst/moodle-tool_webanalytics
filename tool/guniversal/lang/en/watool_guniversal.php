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
 * Lang strings
 *
 * @package   watool_guniversal
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'Google Universal Analytics';
$string['siteid'] = 'Google Analytics ID';
$string['siteid_help'] = 'Enter your Google Analytics ID';
$string['userid'] = 'Track User ID';
$string['userid_help'] = 'If enabled userId parameter will be sent for tracking';
$string['error:siteid'] = 'You must provide Google Analytics ID';
$string['privacy:metadata:watool_guniversal'] = 'In order to track user activity, user data needs to be sent with that service.';
$string['privacy:metadata:watool_guniversal:userid'] = 'The userid is sent from Moodle to personalise user activity.';
