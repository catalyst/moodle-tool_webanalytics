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
 * Tests for injector class.
 *
 * @package   tool_webanalytics
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

use tool_webanalytics\injector;
use tool_webanalytics\records_manager;
use tool_webanalytics\record;

/**
 * Tests for injector class.
 *
 * @copyright  2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_webanalytics_injector_test extends advanced_testcase {

    /**
     * Test render empty tracking code without any config.
     */
    public function test_render_empty_tracking_code() {
        $this->resetAfterTest();
        $this->assertEmpty(injector::render_tracking_code());
    }

    /**
     * Test render tracking code.
     */
    public function test_render_tracking_code() {
        $this->resetAfterTest();

        $manager = new records_manager();
        $record1 = new record((object) [
            'name' => 'Test Google Analytics',
            'enabled' => true,
            'type' => 'guniversal',
            'trackadmin' => 1,
            'settings' => ['siteid' => 'Test GA'],
        ]);
        $manager->save($record1);

        $record2 = new record((object) [
            'name' => 'Test Google Tag Manager',
            'enabled' => true,
            'type' => 'gtagmanager',
            'trackadmin' => 1,
            'settings' => ['siteid' => 'Test GTM'],
        ]);
        $manager->save($record2);

        $actual = injector::render_tracking_code();

        $this->assertTrue(strpos($actual, 'GoogleAnalyticsObject') !== false);
        $this->assertTrue(strpos($actual, 'Test GA') !== false);
        $this->assertTrue(strpos($actual, 'googletagmanager') !== false);
        $this->assertTrue(strpos($actual, 'Test GTM') !== false);
    }

    /**
     * Test render tracking code.
     */
    public function test_render_tracking_code_without_disabled_records() {
        $this->resetAfterTest();

        $manager = new records_manager();
        $disabled = new record((object) [
            'name' => 'Test Google Analytics',
            'enabled' => false,
            'type' => 'guniversal',
            'trackadmin' => 1,
            'settings' => ['siteid' => 'Test GA'],
        ]);
        $manager->save($disabled);
        $enabled = new record((object) [
            'name' => 'Test Google Tag Manager',
            'enabled' => true,
            'type' => 'gtagmanager',
            'trackadmin' => 1,
            'settings' => ['siteid' => 'Test GTM'],
        ]);
        $manager->save($enabled);

        $actual = injector::render_tracking_code();

        $this->assertFalse(strpos($actual, 'GoogleAnalyticsObject') !== false);
        $this->assertFalse(strpos($actual, 'Test GA') !== false);
        $this->assertTrue(strpos($actual, 'googletagmanager') !== false);
        $this->assertTrue(strpos($actual, 'Test GTM') !== false);
    }

}
