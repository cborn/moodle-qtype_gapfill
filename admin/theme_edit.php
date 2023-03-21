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
 *
 * Edit themes form
 *
 * This does the same as the standard xml import but easier
 * @package    qtype_gapfill
 * @copyright  2023 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

$page   = optional_param('page', 1, PARAM_INT);
$newrecord = optional_param('newrecord', '', PARAM_TEXT);
$save = optional_param('save', '', PARAM_TEXT);

$PAGE->set_context(context_system::instance());
$baseurl = new moodle_url('/question/type/gapfill/admin/theme_edit.php', ['page' => $page]);
admin_externalpage_setup('qtype_gapfill_theme_edit');

/**
 *  Edit gapfill question type skins
 *
 * @copyright Marcus Green 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Form for editing gapfill quesiton type skins
 */
class gapfill_theme_edit_form extends moodleform {

    protected function definition() {
        global $PAGE;
        $record = $this->_customdata['record'];

        $mform = $this->_form;
        $PAGE->requires->css('/question/type/gapfill/amd/src/codemirror/lib/codemirror.css');
        $PAGE->requires->css('/question/type/gapfill/amd/src/codemirror/addon/hint/show-hint.css');
        $PAGE->requires->js_call_amd('qtype_gapfill/theme_edit', 'init');


        $mform->addElement('text', 'id');
        $mform->setType('id', PARAM_INT);
        //$mform->setDefault('id', $record->id);

        $mform->addElement('text', 'name', 'Name');
        $mform->setType('name', PARAM_TEXT);
        //$mform->setDefault('name', $record->name);

        $mform->addElement('textarea', 'themecode', get_string('themes', 'qtype_gapfill'), ['rows' => 30, 'cols' => 80]);

        // $mform->setDefault('themecode', $record->themecode);
        $mform->setType('themecode', PARAM_RAW);

        $navbuttons = [];
        $navbuttons[] = $mform->createElement('submit', 'save', 'Save   ');
        $navbuttons[] = $mform->createElement('submit', 'cancel', 'Cancel');
        $navbuttons[] = $mform->createElement('submit', 'newrecord', 'New Record');
        $navbuttons[] = $mform->createElement('submit', 'delete', 'Delete Record');
        $mform->addGroup($navbuttons);
    }
    public function set_data($skin) {
        $this->_form->getElement('id')->setValue($skin->id);
        $this->_form->getElement('name')->setValue($skin->name ?? "");
        $this->_form->getElement('themecode')->setValue($skin->themecode ?? "");

    }

}

$recordcount = $DB->count_records('question_gapfill_theme');
if ($recordcount == 0) {
    $params = (object) [
        'name' => '',
        'themecode' => ''
    ];
    $DB->insert_record('question_gapfill_theme', $params );
    $recordcount = 1;
}
if ($page >= $recordcount) {
    $page = 0;
    $start = 0;
}


$recordset = $DB->get_recordset('question_gapfill_theme');
$count = 0;
foreach ($recordset as $key => $value) {
    if ($count == $page) {
        $record = $value;
        break;
    }
    $count++;
}

$mform = new gapfill_theme_edit_form($baseurl, ['record' => $record]);
$mform->set_data($record);
if ($data = $mform->get_data()) {
    if (isset($data->save)) {
            $params = [
                'id' => $record->id,
                'name' => $data->name,
                'themecode' => $data->themecode
            ];
            $DB->update_record('question_gapfill_theme', $params);
            $data->id = $record->id;
    }
    if (isset($data->newrecord)) {
        unset($record->id);
        $DB->insert_record('question_gapfill_theme', $record);
    }

}

echo $OUTPUT->header();
echo $OUTPUT->paging_bar($recordcount, $page, 1, $baseurl);
$mform->display();
echo $OUTPUT->footer();
