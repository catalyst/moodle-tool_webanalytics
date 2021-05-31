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
 * Tests for record class.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

use tool_webanalytics\record;
use tool_webanalytics\records_manager_cfg;

/**
 * Tests for record class.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class records_manager_cfg_test extends advanced_testcase {

    /**
     * A helper function for setting some test records to a config.
     */
    protected function set_test_records() {
        global $CFG;

        $CFG->tool_anaylytics_records = serialize([
            'test1' => (object) [
                'id' => 'test1',
                'name' => '',
                'enabled' => false,
                'type' => 'test 1',
                'trackadmin' => 0,
                'cleanurl' => 0,
                'settings' => [],
            ],
            'test2' => (object) [
                'id' => 'test2',
                'name' => '',
                'enabled' => true,
                'type' => 'test 2',
                'trackadmin' => 0,
                'cleanurl' => 0,
                'settings' => [],
            ],
            'test3' => (object) [
                'id' => 'test3',
                'name' => '',
                'enabled' => false,
                'type' => 'test 3',
                'trackadmin' => 0,
                'cleanurl' => 0,
                'settings' => [],
            ],
        ]);
    }

    /**
     * Test expected global config name.
     */
    public function test_config_name() {
        $this->assertSame('tool_anaylytics_records', records_manager_cfg::CONFIG_NAME);
    }

    /**
     * Test check that the manager is ready to serve records.
     */
    public function test_is_ready() {
        global $CFG;
        $this->resetAfterTest();
        $manager = new records_manager_cfg();

        $this->assertTrue($manager->is_ready());

        unset($CFG->tool_anaylytics_records);
        $this->assertFalse($manager->is_ready());
    }

    /**
     * Test can get all records.
     */
    public function test_get_all_records() {
        $this->resetAfterTest();

        $manager = new records_manager_cfg();
        $this->assertCount(0, $manager->get_all());

        $this->set_test_records();

        $manager = new records_manager_cfg();
        $this->assertCount(3, $manager->get_all());
    }

    /**
     * Test can get enabled records.
     */
    public function test_get_enabled() {
        $this->resetAfterTest();

        $manager = new records_manager_cfg();
        $this->assertCount(0, $manager->get_enabled());

        $this->set_test_records();
        $manager = new records_manager_cfg();
        $records = $manager->get_enabled();
        $this->assertCount(1, $records);
        $record = reset($records);
        $this->assertSame($record->get_property('id'), 'test2');
    }

    /**
     * Test we can retrieve specific record by id.
     */
    public function test_get() {
        $this->resetAfterTest();

        $manager = new records_manager_cfg();
        $this->assertNull($manager->get('test1'));
        $this->assertNull($manager->get('test2'));
        $this->assertNull($manager->get('test3'));

        $this->set_test_records();

        $manager = new records_manager_cfg();
        $this->assertSame($manager->get('test1')->get_property('id'), 'test1');
        $this->assertSame($manager->get('test2')->get_property('id'), 'test2');
        $this->assertSame($manager->get('test3')->get_property('id'), 'test3');
        $this->assertNull($manager->get('test4'));
    }

    /**
     * Test we can insert a new record.
     */
    public function test_insert_record() {
        global $CFG;

        $this->resetAfterTest();
        $manager = new records_manager_cfg();
        $this->assertCount(0, $manager->get_all());

        $record1 = new record((object)[
            'id' => 'test1',
            'enabled' => false,
            'type' => 'test 1',
        ]);

        $manager->save($record1);
        $this->assertCount(1, $manager->get_all());

        $record2 = new record((object)[
            'id' => 'test2',
            'enabled' => true,
            'type' => 'test 2',
            'settings' => [],
        ]);

        $manager->save($record2);
        $this->assertCount(2, $manager->get_all());

        $this->assertEquals([
            'test1' => (object) [
                'id' => 'test1',
                'name' => '',
                'enabled' => false,
                'type' => 'test 1',
                'trackadmin' => 0,
                'cleanurl' => 0,
                'settings' => [],

            ],
            'test2' => (object) [
                'id' => 'test2',
                'name' => '',
                'enabled' => true,
                'type' => 'test 2',
                'trackadmin' => 0,
                'cleanurl' => 0,
                'settings' => [],
            ]
        ], unserialize($CFG->tool_anaylytics_records));
    }

    /**
     * Test we can update record.
     */
    public function test_update_record() {
        global $CFG;

        $this->resetAfterTest();
        $this->set_test_records();

        $manager = new records_manager_cfg();
        $record = $manager->get('test2');
        $this->assertSame('', $record->get_property('name'));

        $newrecord = new record((object) [
            'id' => 'test2',
            'name' => 'Test 2',
            'enabled' => false,
            'type' => 'test 2 updated',
            'trackadmin' => 1,
            'cleanurl' => 1,
            'settings' => ['setting1' => 1],
        ]);
        $manager->save($newrecord);
        $this->assertSame('test2', $manager->get('test2')->get_property('id'));
        $this->assertSame('Test 2', $manager->get('test2')->get_property('name'));
        $this->assertSame(false, $manager->get('test2')->get_property('enabled'));
        $this->assertSame('test 2 updated', $manager->get('test2')->get_property('type'));
        $this->assertSame(1, $manager->get('test2')->get_property('trackadmin'));
        $this->assertSame(1, $manager->get('test2')->get_property('cleanurl'));
        $this->assertSame(['setting1' => 1], $manager->get('test2')->get_property('settings'));

        $this->assertEquals([
            'test1' => (object) [
                'id' => 'test1',
                'name' => '',
                'enabled' => false,
                'type' => 'test 1',
                'trackadmin' => 0,
                'cleanurl' => 0,
                'settings' => [],
            ],
            'test2' => (object) [
                'id' => 'test2',
                'name' => 'Test 2',
                'enabled' => false,
                'type' => 'test 2 updated',
                'trackadmin' => 1,
                'cleanurl' => 1,
                'settings' => ['setting1' => 1],
            ],
            'test3' => (object) [
                'id' => 'test3',
                'name' => '',
                'enabled' => false,
                'type' => 'test 3',
                'trackadmin' => 0,
                'cleanurl' => 0,
                'settings' => [],
            ]
        ], unserialize($CFG->tool_anaylytics_records));

    }

    /**
     * Test we can delete record.
     */
    public function test_delete_record() {
        global $CFG;

        $this->resetAfterTest();
        $this->set_test_records();

        $manager = new records_manager_cfg();
        $manager->delete('test1');

        $this->assertEquals([
            'test2' => (object) [
                'id' => 'test2',
                'name' => '',
                'enabled' => true,
                'type' => 'test 2',
                'trackadmin' => 0,
                'cleanurl' => 0,
                'settings' => [],
            ],
            'test3' => (object) [
                'id' => 'test3',
                'name' => '',
                'enabled' => false,
                'type' => 'test 3',
                'trackadmin' => 0,
                'cleanurl' => 0,
                'settings' => [],
            ]
        ], unserialize($CFG->tool_anaylytics_records));
    }

}
