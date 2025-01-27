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

class watool_matomo_autoprovision_test extends advanced_testcase {

    /**
     * @return void
     */
    private function set_ap_config() {
        set_config('siteurl', 'https://example.com', 'watool_matomo');
        set_config('apitoken', '1234567ABCDEFG', 'watool_matomo');
    }

    /**
     * @return client stub
     */
    private function get_client_stub($siteidfromurl = 1, $updatesite = 1, $addsite = 1): client {
        $clientstub = $this->createStub('\watool_matomo\client');
        $clientstub->method('get_siteid_from_url')->willReturn($siteidfromurl);
        $clientstub->method('update_site')->willReturn($updatesite);
        $clientstub->method('add_site')->willReturn($addsite);

        return $clientstub;
    }

    /**
     * @return void
     */
    public function test_register_site(): void {
        global $CFG;

        // Setup the stub return ids.
        $clientstub = $this->get_client_stub(0, 1, 2);
        $record = new record(new stdClass());
        $tool = new \watool_matomo\tool\tool($record);

        // First time registering so expect id 2.
        $id = $tool->register_site($clientstub);
        $this->assertEquals(2, $id);

        $data = new stdClass();
        $data->name = uniqid();
        $data->type = 'matomo';
        $settings['siteid'] = 2;
        $settings['wwwroot'] = $CFG->wwwroot;
        $data->settings = $settings;
        $record = new record($data);

        $clientstub = $this->get_client_stub(2, 1, 3);
        $tool = new \watool_matomo\tool\tool($record);

        // Site already registered with same url so siteid stays the same and nothing is updated.
        $id = $tool->register_site($clientstub);
        $this->assertEquals(2, $id);

        $settings['wwwroot'] = 'https://example.com';
        $record->set_property('settings', $settings);
        $tool = new \watool_matomo\tool\tool($record);

        // Site already registered but DNS has changed since last update.
        $id = $tool->register_site($clientstub);
        $this->assertEquals(1, $id);
    }

    /**
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
     * @return void
     */
    public function test_can_auto_provision(): void {
        global $CFG;

        $this->resetAfterTest(true);

        // With no auto-prov API config.
        $this->assertFalse(\watool_matomo\tool\tool::can_auto_provision());

        $this->set_ap_config();
        // With auto-prov API config.
        $this->assertTrue(\watool_matomo\tool\tool::can_auto_provision());

        $data = new stdClass();
        $data->name = 'auto-provisioned:' . uniqid();
        $data->type = 'matomo';
        $settings['siteid'] = 1;
        $settings['wwwroot'] = $CFG->wwwroot;
        $settings['siteurl'] = "https://example.com";
        $settings['apitoken'] = "1234567ABCDEFG";
        $data->settings = $settings;
        $record = new record($data);

        $rm = new records_manager();
        $id = $rm->save($record);

        // With an existing record that has the same DNS as the site currently.
        $this->assertFalse(\watool_matomo\tool\tool::can_auto_provision());

        $record = $rm->get($id);
        $settings = $record->get_property('settings');
        $settings['wwwroot'] = "https://newexample.com";
        $record->set_property('settings', $settings);
        $rm->save($record);

        // The site DNS has been changed.
        $this->assertTrue(\watool_matomo\tool\tool::can_auto_provision());

        $record->set_property('name', 'auto-provisioned:FAILED');
        $rm->save($record);

        // An auto provision attempt failed so don't keep trying.
        $this->assertFalse(\watool_matomo\tool\tool::can_auto_provision());
    }

    /**
     * @return void
     */
    public function test_auto_provision(): void {
        global $CFG;

        $this->resetAfterTest(true);
        $this->set_ap_config();

        // Setup the stub return ids.
        $clientstub = $this->get_client_stub();

        \watool_matomo\tool\tool::auto_provision($clientstub);
        $rm = new records_manager();
        $allrecords = $rm->get_all();
        $this->assertCount(1, $allrecords);

        $record = reset($allrecords);
        $settings = $record->get_property('settings');
        $settings['wwwroot'] = "https://newexample.com";
        $record->set_property('settings', $settings);
        $id = $record->get_property('id');
        $rm->save($record);

        \watool_matomo\tool\tool::auto_provision($clientstub);

        $rm = new records_manager();
        $allrecords = $rm->get_all();
        $this->assertCount(1, $allrecords);
        $record = reset($allrecords);
        $settings = $record->get_property('settings');
        // Check the existing record had its DNS flag updated.
        $this->assertEquals($CFG->wwwroot, $settings['wwwroot']);
        $this->assertEquals($id, $record->get_property('id'));

        $rm->delete($record->get_property('id'));
        $rm = new records_manager();
        $allrecords = $rm->get_all();
        $this->assertCount(0, $allrecords);

        $clientstub->method('add_site')->willThrowException(new Exception());

        \watool_matomo\tool\tool::auto_provision($clientstub);

        $rm = new records_manager();
        $allrecords = $rm->get_all();
        $this->assertCount(1, $allrecords);
        $record = reset($allrecords);
        // Make sure failed provisions get marked accordingly.
        $this->assertEquals('auto-provisioned:FAILED', $record->get_property('name'));
    }
}
