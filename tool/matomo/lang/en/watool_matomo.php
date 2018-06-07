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
 * @package   watool_matomo
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'Matomo Analytics (formerly Piwik)';
$string['siteurl'] = 'Analytics URL';
$string['siteurl_help'] = 'Enter your Matomo URL without http(s) or a trailing slash';
$string['siteid'] = 'Matomo Site ID';
$string['siteid_help'] = 'Enter your Matomo Site ID';
$string['imagetrack'] = 'Image Tracking';
$string['imagetrack_help'] = 'Enable Image Tracking for Moodle for browsers with JavaScript disabled';
$string['error:siteid'] = 'You must provide Site ID';
$string['error:siteurl'] = 'You must provide site URL';
$string['error:siteurlinvalid'] = 'You must provide valid site URL';
$string['error:siteurlhttps'] = 'Please provide URL without http(s)';
$string['error:siteurltrailingslash'] = 'Please provide URL without a trailing slash';
$string['privacy:metadata:watool_matomo'] = 'In order to track user activity, user data needs to be sent with that service.';
$string['privacy:metadata:watool_matomo:userid'] = 'The userid is sent from Moodle to personalise user activity.';
