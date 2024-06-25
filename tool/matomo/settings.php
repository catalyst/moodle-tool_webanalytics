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
 * Settings
 *
 * @package   watool_matomo
 * @author    Simon Adams (simon.adams@catalyst-eu.net)
 * @copyright 2023 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings->add(new admin_setting_heading(
        'watool_matomo_autoprovision',
        get_string('autoprovision_heading', 'watool_matomo'),
        ''
    ));
    $settings->add(new admin_setting_configtext(
        'watool_matomo/siteurl',
        get_string('apiurl', 'watool_matomo'),
        get_string('apiurl_help', 'watool_matomo'),
        '',
        PARAM_URL
    ));
    $settings->add(new admin_setting_configpasswordunmask(
        'watool_matomo/apitoken',
        get_string('apitoken', 'watool_matomo'),
        get_string('apitoken_desc', 'watool_matomo'),
        '',
    ));
    $settings->add(new admin_setting_configcheckbox(
        'watool_matomo/defaultautoupdate',
        get_string('defaultautoupdate', 'watool_matomo'),
        get_string('defaultautoupdate_help', 'watool_matomo'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'watool_matomo/matomostricttracking',
        get_string('matomostricttracking', 'watool_matomo'),
        get_string('matomostricttracking_help', 'watool_matomo'),
        0
    ));
}
