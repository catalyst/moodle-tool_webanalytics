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
 * Tests for Matomo auto provisioning.
 *
 * @package    watool_matomo
 * @copyright  2023 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author     Simon Adams simon.adams@catalyst-eu.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_webanalytics\records_manager;
use tool_webanalytics\record;
use watool_matomo\client;

/**
 * Tests for Matomo auto provisioning.
 *
 * @copyright  2023 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class watool_matomo_autoprovision_test extends advanced_testcase {

    /**
     * Sets the required config.
     *
     * @return void
     */
    private function set_ap_config() {
        set_config('siteurl', 'https://example.com', 'watool_matomo');
        set_config('apitoken', '1234567ABCDEFG', 'watool_matomo');
    }

    /**
     *
     * Returns a client with stub methods.
     *
     * @param integer $siteidfromurl
     * @param bool $updatesite
     * @param integer $addsite
     * @return client
     */
    private function get_client_stub($siteidfromurl = 1, $updatesite = true, $addsite = 1): client {
        $clientstub = $this->createStub('\watool_matomo\client');
        $clientstub->method('get_siteid_from_url')->willReturn($siteidfromurl);
        $clientstub->method('update_site')->willReturn($updatesite);
        $clientstub->method('add_site')->willReturn($addsite);
        $clientstub->method('get_urls_from_siteid')->willReturn([]);

        return $clientstub;
    }

    /**
     * Tests support for auto provision.
     *
     * @return void
     */
    public function test_supports_auto_provision(): void {
        $this->resetAfterTest(true);

        // No auto-prov API config set.
        $this->assertFalse(\watool_matomo\tool\tool::supports_auto_provision());

        // With auto-prov API config set.
        $this->set_ap_config();
        $this->assertTrue(\watool_matomo\tool\tool::supports_auto_provision());
    }

    /**
     * Tests scenarios where auto provision is required.
     *
     * @return void
     */
    public function test_can_auto_provision(): void {
        global $CFG;

        $this->resetAfterTest(true);
        $rm = new records_manager();

        // With no auto-prov API config.
        $this->assertFalse(\watool_matomo\tool\tool::can_auto_provision());

        // With auto-prov API config.
        $this->set_ap_config();
        $this->assertTrue(\watool_matomo\tool\tool::can_auto_provision());

        // With an existing manual record.
        $data = new stdClass();
        $data->name = 'Manual Instance';
        $data->type = 'matomo';
        $data->enabled = true;
        $settings['siteid'] = 1;
        $settings['siteurl'] = "https://example.com";
        $data->settings = $settings;
        $record = new record($data);
        $id = $rm->save($record);

        $this->assertFalse(\watool_matomo\tool\tool::can_auto_provision());
        $rm->delete($id);

        // With an existing record that has the same DNS as the site currently.
        $data = new stdClass();
        $data->name = get_string('autoprovision_name', 'watool_matomo') . uniqid();
        $data->type = 'matomo';
        $data->enabled = true;
        $settings['autoupdate'] = true;
        $settings['siteid'] = 1;
        $settings['autoupdateurls'] = [$CFG->wwwroot];
        $settings['siteurl'] = "https://example.com";
        $data->settings = $settings;
        $record = new record($data);
        $id = $rm->save($record);

        $this->assertFalse(\watool_matomo\tool\tool::can_auto_provision());

        // The site DNS has been changed.
        $record = $rm->get($id);
        $settings = $record->get_property('settings');
        $settings['autoupdateurls'] = ['https://newexample.com'];
        $record->set_property('settings', $settings);
        $rm->save($record);

        $this->assertTrue(\watool_matomo\tool\tool::can_auto_provision());

        // An auto provision attempt failed so don't keep trying.
        $record->set_property('name', get_string('autoprovision_failed_name', 'watool_matomo'));
        $record->set_property('enabled', false);
        $rm->save($record);

        $this->assertFalse(\watool_matomo\tool\tool::can_auto_provision());
    }

    /**
     * Tests auto provision and updating.
     *
     * @return void
     */
    public function test_auto_provision(): void {
        global $CFG;

        $this->resetAfterTest(true);
        $this->set_ap_config();

        // Setup the stub return ids.
        $clientstub = $this->get_client_stub();

        $ap = new \watool_matomo\auto_provision($clientstub);
        $ap->attempt();

        $rm = new records_manager();
        $allrecords = $rm->get_all();
        $this->assertCount(1, $allrecords);

        // Check that auto provision can update the urls.
        $record = reset($allrecords);
        $settings = $record->get_property('settings');
        $settings['autoupdateurls'] = ["https://newexample.com"];
        $record->set_property('settings', $settings);
        $id = $record->get_property('id');
        $rm->save($record);

        $ap->attempt();

        $rm = new records_manager();
        $allrecords = $rm->get_all();
        $this->assertCount(1, $allrecords);
        $record = reset($allrecords);
        $settings = $record->get_property('settings');
        $this->assertContains($CFG->wwwroot, $settings['autoupdateurls']);
        $this->assertEquals($id, $record->get_property('id'));

        $rm->delete($record->get_property('id'));
        $rm = new records_manager();
        $allrecords = $rm->get_all();
        $this->assertCount(0, $allrecords);

        // Check that failed auto provisions are renamed correctly.
        $clientstub2 = $this->createStub('\watool_matomo\client');
        $clientstub2->method('get_siteid_from_url')->willReturn(0);
        $clientstub2->method('add_site')->willThrowException(new Exception());

        $ap2 = new \watool_matomo\auto_provision($clientstub2);
        $ap2->attempt();

        $rm = new records_manager();
        $allrecords = $rm->get_all();
        $this->assertCount(1, $allrecords);
        $record = reset($allrecords);
        $this->assertEquals(get_string('autoprovision_failed_name', 'watool_matomo'), $record->get_property('name'));
    }
}
