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


class tool_webanalytics_record_test extends advanced_testcase {
    /**
     * Test data
     *
     * @var object
     */
    protected $data;

    /**
     * Test record.
     * @var \tool_webanalytics\record
     */
    protected $record;

    /**
     * Initial set up.
     */
    public function setUp() {
        $this->resetAfterTest();
        $this->data = new stdClass();
        $this->record = new record($this->data);
    }

    /**
     * Teardown unit tests.
     */
    public function tearDown() {
        $this->data = null;
        $this->record = null;
        parent::tearDown();
    }

    /**
     * Test implements required interface.
     */
    public function test_implements_required_interface() {
        $this->assertInstanceOf('tool_webanalytics\record_interface', $this->record );
    }

    /**
     * Data provider for test_has_correct_default_values.
     *
     * @return array
     */
    public function default_values_data_provider() {
        return [
            ['id', null],
            ['enabled', 0],
            ['name', ''],
            ['location', 'head'],
            ['type', null],
            ['trackadmin', 0],
            ['cleanurl', 0],
            ['settings', []],
        ];
    }

    /**
     * Test correct default data.
     *
     * @dataProvider default_values_data_provider
     * @param $name
     * @param $value
     */
    public function test_has_correct_default_values($name, $value) {
        $this->assertEquals($value, $this->record->get_property($name));
    }

    /**
     * Test can return  values.
     */
    public function test_can_get_values() {
        $this->data->id = 2;
        $this->data->enabled = 1;
        $this->data->name = "Test name";
        $this->data->location = 'Test loc';
        $this->data->type = 'Test type';
        $this->data->trackadmin = 1;
        $this->data->cleanurl = 1;
        $this->data->settings = [
            'test' => 'test',
            'record' => 'record',
        ];

        $this->record = new record($this->data);

        foreach ($this->data as $name => $value) {
            $this->assertEquals($value, $this->record->get_property($name));
        }
    }

    /**
     * Test throw coding exception if request invalid property.
     *
     * @expectedException coding_exception
     * @expectedExceptionMessage Requested invalid property
     */
    public function test_throw_exception_on_incorrect_property() {
        $this->record->get_property('test random property');
    }

    /**
     * Test we can check status.
     */
    public function test_is_enabled() {
        $this->assertFalse($this->record->is_enabled());

        $this->data->enabled = 'not empty';
        $this->record = new record($this->data);
        $this->assertTrue($this->record->is_enabled());

        $this->data->enabled = 1;
        $this->record = new record($this->data);
        $this->assertTrue($this->record->is_enabled());

        $this->data->enabled = [1];
        $this->record = new record($this->data);
        $this->assertTrue($this->record->is_enabled());
    }

    /**
     * Test we can export empty record as required.
     */
    public function test_export_empty_record() {
        $expected = new stdClass();
        $expected->id = null;
        $expected->enabled = 0;
        $expected->name = '';
        $expected->location = 'head';
        $expected->type = '';
        $expected->trackadmin = 0;
        $expected->cleanurl = 0;
        $expected->settings = [];

        $this->assertEquals($expected, $this->record->export());
    }

    /**
     * Test we can export not empty record as required.
     */
    public function test_export_not_empty_record() {
        $expected = new stdClass();
        $expected->id = 1;
        $expected->enabled = 1;
        $expected->name = 'Test';
        $expected->location = 'footer';
        $expected->type = 'Type';
        $expected->trackadmin = 1;
        $expected->cleanurl = 1;
        $expected->settings = [1, 2, 3, 4];

        $this->record = new record($expected);

        $this->assertEquals($expected, $this->record->export());
    }

    /**
     * Data provider for not array settings.
     *
     * @return array
     */
    public function not_array_settings_data_provider() {
        return [
            ['string'],
            [new stdClass()],
            [1],
            ['1'],
            [true],
            [null],
        ];
    }

    /**
     * Test that return empty array of invalid settings set.
     *
     * @dataProvider not_array_settings_data_provider
     */
    public function test_return_empty_settings_if_they_are_not_array($settings) {
        $this->data->settings = $settings;
        $this->record = new record($this->data);

        $this->assertEquals([], $this->record->get_property('settings'));
    }

    /**
     * Test that return empty array of invalid settings set.
     *
     * @dataProvider not_array_settings_data_provider
     */
    public function test_export_empty_settings_if_they_are_not_array($settings) {
        $this->data->settings = $settings;

        $expected = new stdClass();
        $expected->id = null;
        $expected->enabled = 0;
        $expected->name = '';
        $expected->location = 'head';
        $expected->type = '';
        $expected->trackadmin = 0;
        $expected->cleanurl = 0;
        $expected->settings = [];

        $this->record = new record($this->data);
        $this->assertEquals($expected, $this->record->export());
    }

}
