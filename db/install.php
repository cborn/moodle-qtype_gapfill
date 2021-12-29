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
 * Gapfill question type  capability definition
 *
 * @package    qtype_gapfill
 * @copyright  2021 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// defined('MOODLE_INTERNAL') || die();

/**
 * Perform the post-install procedures.
 */
require_once('../../../../config.php');

xmldb_qtype_gapfill_install();

function xmldb_qtype_gapfill_install() {
    global $DB;
    $filenames = glob(__DIR__.'/themes/*');
    foreach ($filenames as $filename) {
        $xml = simplexml_load_file($filename);
        $style = (string) $xml->style;
        $style = '<style>'.$style.'</style>';
        $data = (object) ['name' => (string) $xml->name, 'themecode' => $style ];
        $DB->insert_record('question_gapfill_theme', $data);
    }
}

