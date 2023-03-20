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
 * Gapfill question type inport skins
 *
 * @package    qtype_gapfill
 * @copyright  2023 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Perform the post-install procedures.
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_gapfill_install() {
    global $DB, $CFG;
    $filenames = glob($CFG->dirroot.'/question/type/gapfill/skins/*');
    foreach ($filenames as $filename) {
        $xml = simplexml_load_file($filename);
        foreach ($xml as $element) {
            $name = (string) $element->name;
            $style = (string) $element->style;
            $style = '<style>'.$style.'</style>';
            $data = (object) ['name' => (string) $name, 'themecode' => $style ];
            $DB->insert_record('question_gapfill_theme', $data);
        }
    }
}

