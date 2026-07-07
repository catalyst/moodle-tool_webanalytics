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
 * Security tests for the Matomo analytics tool.
 *
 * @package   watool_matomo
 * @author    Owen Herbert (owenherbert@catalyst-au.net)
 * @copyright 2024 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace watool_matomo;

use tool_webanalytics\record;
use watool_matomo\tool\tool;

/**
 * Security tests for the Matomo analytics tool.
 *
 * @copyright  2026 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \watool_matomo\tool\tool
 */
final class tool_test extends \advanced_testcase {
    /**
     * Build a tool instance with the given settings/record overrides.
     */
    private function make_tool(array $settings = [], array $extra = []): tool {
        $record = new record((object) array_merge([
            'name'       => 'Matomo',
            'enabled'    => true,
            'type'       => 'matomo',
            'trackadmin' => 1,
            'cleanurl'   => 0,
            'settings'   => array_merge([
                'siteid'     => 1,
                'siteurl'    => 'matomo.example.com',
                'piwikjsurl' => '',
                'imagetrack' => 0,
                'userid'     => 0,
                'usefield'   => 'id',
            ], $settings),
        ], $extra));
        return new tool($record);
    }

    /**
     * Invoke the protected might_encode() method via reflection.
     */
    private function call_might_encode(tool $tool, string $input, bool $encode): string {
        $ref = new \ReflectionMethod($tool, 'might_encode');
        $ref->setAccessible(true);
        return $ref->invoke($tool, $input, $encode);
    }

    /**
     * Test plain strings pass through unchanged.
     */
    public function test_might_encode_plain_string(): void {
        $tool = $this->make_tool();
        $this->assertSame('hello world', $this->call_might_encode($tool, 'hello world', false));
    }

    /**
     * Test single quotes are backslash-escaped.
     */
    public function test_might_encode_escapes_single_quote(): void {
        $tool = $this->make_tool();
        $this->assertSame("it\\'s", $this->call_might_encode($tool, "it's", false));
    }

    /**
     * Test backslashes are doubled so they cannot cancel a later escaped quote.
     */
    public function test_might_encode_escapes_backslash(): void {
        $tool = $this->make_tool();
        $this->assertSame('back\\\\slash', $this->call_might_encode($tool, 'back\\slash', false));
    }

    /**
     * Test the exact injection vector: backslash immediately before a quote.
     *
     * Input  \' must become \\\' (escaped-backslash + escaped-quote).
     * In a JS single-quoted string that renders as the literal value \' and
     * cannot terminate the string.
     */
    public function test_might_encode_backslash_quote_is_double_escaped(): void {
        $tool = $this->make_tool();
        $this->assertSame("\\\\\\'", $this->call_might_encode($tool, "\\'", false));
    }

    /**
     * Test URL-encoding is applied when the encode flag is set.
     */
    public function test_might_encode_url_encodes_when_requested(): void {
        $tool = $this->make_tool();
        $this->assertSame('hello+world', $this->call_might_encode($tool, 'hello world', true));
        $this->assertSame('it%27s', $this->call_might_encode($tool, "it's", true));
    }

    /**
     * Test that the Clean URL document title is wrapped in json_encode(), producing
     * a double-quoted JSON string literal rather than a single-quoted JS string.
     */
    public function test_get_tracking_code_cleanurl_doctitle_is_json_encoded(): void {
        global $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(['fullname' => "Sec\\'s Course"]);
        $PAGE->set_context(\context_course::instance($course->id));

        $tool   = $this->make_tool([], ['cleanurl' => 1]);
        $output = $tool->get_tracking_code();

        // Argument must be a double-quoted JSON string, not a single-quoted JS string.
        $this->assertStringContainsString("setDocumentTitle', \"", $output);
        $this->assertStringNotContainsString("setDocumentTitle', '", $output);
    }
}
