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
$string['piwikjsurl'] = 'Alternative piwik.js URL';
$string['piwikjsurl_help'] = 'Enter alternative piwik.js URL (optional) without http(s), piwik.js or a trailing slash, used when you host your own version of piwik.js';
$string['siteid'] = 'Matomo Site ID';
$string['siteid_help'] = 'Enter your Matomo Site ID';
$string['imagetrack'] = 'Image Tracking';
$string['imagetrack_help'] = 'Enable Image Tracking for Moodle for browsers with JavaScript disabled';
$string['error:siteid'] = 'You must provide Site ID';
$string['error:siteurl'] = 'You must provide site URL';
$string['error:siteurlinvalid'] = 'You must provide valid site URL';
$string['error:siteurlhttps'] = 'Please provide URL without http(s)';
$string['error:siteurltrailingslash'] = 'Please provide URL without a trailing slash';
$string['error:noapicreds'] = 'No site URL or API token configured!';
$string['error:autoupdatevalidation'] = 'Auto updating can only be enabled if you have an existing Matomo instance. Make sure the site id already exists in Matomo and is linked to the current wwwroot.';
$string['privacy:metadata:watool_matomo'] = 'In order to track user activity, user data needs to be sent with that service.';
$string['privacy:metadata:watool_matomo:userid'] = 'The userid is sent from Moodle to personalise user activity.';
$string['userid'] = 'Track User ID';
$string['userid_help'] = 'If enabled userId parameter will be sent for tracking';
$string['usefield'] = 'User ID field';
$string['usefield_help'] = 'Select a user field to be used as User ID when sending for tracking';
$string['apiheader'] = 'API Instance Settings:';
$string['apiurl'] = 'API url';
$string['apiurl_help'] = 'Base URL of the Matomo instance';
$string['apitoken'] = 'API token';
$string['apitoken_help'] = 'Authentication "token_auth" of a user who can access the instance over the API. If configured, this allows automatic site registration with the matomo instance set in "Analytics URL" above.';
$string['apitoken_desc'] = 'Authentication "token_auth" of a user who can access the instance over the API.';
$string['autoupdate'] = 'Automatic URL Updating';
$string['autoupdate_help'] = 'Enables automatic registration of new URLs with Matomo. To enable this setting you must either use auto provisioning or have a matching siteurl and an existing siteid on Matomo.';
$string['autoupdateurls'] = 'Automatic tracking URLs';
$string['autoupdateurls_help'] = 'A list of URLs that Matomo is tracking. This will only show URLs if automatic URL updating is enabled. This is taken from an API call and is updated irregularly.';
$string['autoprovision_heading'] = 'Auto provision settings:';
$string['autoprovision_name'] = 'auto-provisioned';
$string['autoprovision_failed_name'] = 'auto-provisioned:FAILED';
$string['autoupdate_disable'] = 'Matomo auto updating has been disabled.';
$string['defaultautoupdate'] = 'Enable automatic URL updating';
$string['defaultautoupdate_help'] = 'Sets the default value of the setting "Automatic URL Updating" for auto provisioned sites.';
$string['matomostricttracking'] = 'Strict Matomo URL tracking';
$string['matomostricttracking_help'] = 'Sets the default value of the Matomo setting "Only track visits and actions when the action URL starts with one of the linked URLs" when creating new auto provisioned instances.';
