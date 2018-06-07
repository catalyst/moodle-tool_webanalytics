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
 * Privacy provider.
 *
 * @package   watool_guniversal
 * @author    Dmitrii Metelkin (dmitriim@catalyst-au.net)
 * @copyright 2018 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace watool_guniversal\privacy;

defined('MOODLE_INTERNAL') || die;

use core_privacy\local\legacy_polyfill;

/**
 * Class provider
 * @package watool_guniversal\privacy
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    use legacy_polyfill;

    public static function _get_metadata($collection) {

        $collection->add_external_location_link('watool_guniversal', [
            'userid' => 'privacy:metadata:watool_guniversal:userid',
        ], 'privacy:metadata:watool_guniversal');

        return $collection;
    }

}
