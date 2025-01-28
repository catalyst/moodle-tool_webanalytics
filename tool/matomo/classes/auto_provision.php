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
 * Class that handles Matomo auto provisioning.
 *
 * @package   watool_matomo
 * @author    Benjamin Walker (benjaminwalker@catalyst-au.net)
 * @copyright 2023 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace watool_matomo;

use stdClass;
use Throwable;
use tool_webanalytics\record;
use tool_webanalytics\records_manager;
use watool_matomo\tool\tool;

/**
 * Matomo auto provisioning.
 */
class auto_provision {
    /**
     * Client for API calls
     *
     * @var \watool_matomo\client
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param \watool_matomo\client|null $client
     */
    public function __construct(client $client = null) {
        $this->client = $client ?? new client();
    }

    /**
     * Attempts auto provision based on config 'siteurl' and 'apitoken'.
     * This can either create a new Matomo instance, or update an existing one.
     *
     * @return void
     */
    public function attempt(): void {
        $action = self::get_action();
        switch ($action->type) {
            case 'create':
                $this->create();
                break;
            case 'update':
                $this->update($action->record);
                break;
        }
    }

    /**
     * Determines if a record needs to be created or updated.
     *
     * @return stdClass action containing the 'type' of update and 'record' if needed.
     */
    public static function get_action(): stdClass {
        $rm = new records_manager();
        $records = $rm->get_all();

        $action = new stdClass;
        $action->type = 'create';
        foreach ($records as $record) {
            if ($record->get_property('type') !== tool::TYPE) {
                continue;
            }

            // Does an enabled auto provisioned site need updating?
            if (self::ready_for_update($record)) {
                $action->type = 'update';
                $action->record = $record;
                return $action;
            }

            // Don't allow creation if there is an existing Matomo instance.
            $action->type = '';
        }
        return $action;
    }

    /**
     * Sets up new auto provisioned analytics tracking. This will attempt to link to an existing
     * Matomo instance, or create a new instance if one for the current url doesn't exist.
     *
     * @return void
     */
    private function create(): void {
        global $CFG;

        $data = new stdClass();
        $data->type = tool::TYPE;
        $data->name = get_string('autoprovision_name', 'watool_matomo');
        $data->enabled = true;

        try {
            // Check for existing setups on Matomo.
            $siteid = $this->client->get_siteid_from_url($CFG->wwwroot);
            if (!empty($siteid)) {
                $autoupdateurls = $this->client->get_urls_from_siteid($siteid);
            } else {
                // Create on Matomo.
                $siteid = $this->client->add_site();
                $autoupdateurls = [$CFG->wwwroot];
            }
        } catch (Throwable $e) {
            $siteid = 0;
        }
        if ($siteid === 0) {
            $data->name = get_string('autoprovision_failed_name', 'watool_matomo');
            $data->enabled = false;
            $autoupdateurls = [];
        }

        $config = get_config('watool_matomo');
        $autoupdate = $config->defaultautoupdate;
        $settings = [
            'siteid' => $siteid,
            'autoupdate' => $autoupdate,
            'autoupdateurls' => $autoupdate ? $autoupdateurls : [],
            'siteurl' => $this->get_formatted_siteurl(),
        ];
        $settings = array_merge(tool::SETTINGS_DEFAULTS, $settings);
        $data->settings = $settings;

        $rm = new records_manager();
        $record = new record($data);
        $rm->save($record);
    }

    /**
     * Attempts to update an existing instance with new urls.
     * This is basically a two-way sync which updates Moodle's list of stored urls and
     * adds the current site url to the Matomo instance.
     *
     * @param \tool_webanalytics\record $record
     * @return void
     */
    private function update(record $record): void {
        global $CFG;

        // Make sure the important instance settings haven't been manually changed.
        if (!$this->validate_record($record)) {
            $this->disable_updating($record);
            return;
        }

        // Update the saved urls to match actual urls, and if changed check if update is still needed.
        if ($this->update_saved_urls($record) && !$this->ready_for_update($record)) {
            return;
        }

        $data = $record->export();
        $settings = $data->settings;
        $autoupdateurls = array_unique(array_merge($settings['autoupdateurls'], [$CFG->wwwroot]));

        try {
            $updated = $this->client->update_site($settings['siteid'], '', $autoupdateurls);
        } catch (Throwable $e) {
            $updated = false;
        }
        if (!$updated) {
            $this->disable_updating($record);
            return;
        }

        $settings['autoupdateurls'] = $autoupdateurls;
        $settings['siteurl'] = $this->get_formatted_siteurl();
        $data->settings = $settings;

        $rm = new records_manager();
        $record = new record($data);
        $rm->save($record);
    }

    /**
     * Determines if a record is ready to attempt an update.
     * This will be called often, so don't perform any heavy actions.
     *
     * @param \tool_webanalytics\record $record
     * @return bool true if a record is active and out of date, false otherwise.
     */
    private static function ready_for_update(record $record): bool {
        GLOBAL $CFG;

        // Check if auto updating is enabled for this record.
        $settings = $record->get_property('settings');
        if (!$record->is_enabled() || empty($settings['autoupdate'])) {
            return false;
        }

        // Check if the current url needs to be added to Matomo.
        return empty($settings['autoupdateurls']) || !in_array($CFG->wwwroot, $settings['autoupdateurls']);
    }

    /**
     * Validates the record to ensure the siteurl and siteid can be used for auto updating.
     *
     * @param \tool_webanalytics\record $record
     * @return bool true if validation was successful, false otherwise.
     */
    public function validate_record(record $record): bool {
        GLOBAL $CFG;
        // Check that the siteurl matches config value.
        if (!self::matching_siteurl($record)) {
            return false;
        }

        // Check that the siteid is valid. Site ids are locked when auto updating is enabled, so
        // this only needs to be checked once when auto updating is first enabled or re-enabled.
        // Having proof that auto updating was already enabled is enough to continue.
        $settings = $record->get_property('settings');
        if (!empty($settings['autoupdate'])) {
            return true;
        }

        // If auto updating wasn't already enabled we need extra extra validation to prevent new connections
        // to unverified site ids, stopping users from from targeting unrelated site ids on shared hosting.
        // Check that the current wwwroot is already linked to the provided Matomo instance.
        $siteid = $settings['siteid'];
        $linkedurls = $this->client->get_urls_from_siteid($siteid);
        return in_array($CFG->wwwroot, $linkedurls);
    }

    /**
     * Validates a records siteurl by confirming that it matches the config siteurl.
     *
     * @param \tool_webanalytics\record $record
     * @return bool
     */
    public static function matching_siteurl($record): bool {
        // Auto provisioning must be enabled for the siteurl to work.
        if (!tool::supports_auto_provision()) {
            return false;
        }
        $settings = $record->get_property('settings');
        return !empty($settings) && $settings['siteurl'] === self::get_formatted_siteurl();
    }

    /**
     * Disables auto updating and the related settings.
     *
     * @param \tool_webanalytics\record $record
     * @return void
     */
    private function disable_updating(record $record): void {
        $settings = $record->get_property('settings');
        $settings['autoupdate'] = false;
        $settings['autoupdateurls'] = [];
        $record->set_property('settings', $settings);

        $rm = new records_manager();
        $rm->save($record);
        debugging('tool_webanalytics: ' . get_string('autoupdate_disable', 'watool_matomo'));
    }

    /**
     * Updates the stored urls with values grabbed from the API.
     *
     * @param \tool_webanalytics\record $record
     * @return bool true if the saved urls were updated, false otherwise
     */
    private function update_saved_urls(record $record): bool {
        $settings = $record->get_property('settings');
        $siteid = $settings['siteid'];
        $savedurls = $settings['autoupdateurls'];
        $linkedurls = $this->client->get_urls_from_siteid($siteid);

        // Update urls if we have new urls or it hasn't been set up.
        if ($savedurls !== $linkedurls) {
            $settings['autoupdateurls'] = $linkedurls;
            $record->set_property('settings', $settings);
            $rm = new records_manager();
            $rm->save($record);
            return true;
        }
        return false;
    }

    /**
     * Gets the siteurl from config and apply the required formatting.
     *
     * @return string
     */
    private static function get_formatted_siteurl(): string {
        $config = get_config('watool_matomo');
        return rtrim(preg_replace("/^(http|https):\/\//", '', $config->siteurl), '/');
    }
}
