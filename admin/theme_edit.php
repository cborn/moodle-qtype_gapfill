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

        $mform = $this->_form;
        $PAGE->requires->css('/question/type/gapfill/amd/src/codemirror/lib/codemirror.css');
        $PAGE->requires->css('/question/type/gapfill/amd/src/codemirror/addon/hint/show-hint.css');
        $PAGE->requires->js_call_amd('qtype_gapfill/theme_edit', 'init');


        $mform->addElement('text', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', 'Name');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('textarea', 'themecode', get_string('themes', 'qtype_gapfill'), ['rows' => 30, 'cols' => 80]);

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

$emptyparams = (object) [
    'name' => '',
    'themecode' => ''
];
$recordcount = $DB->count_records('question_gapfill_theme');

if ($recordcount == 0 || $newrecord) {
    $id = $DB->insert_record('question_gapfill_theme', $emptyparams );
    $record = $DB->get_record('question_gapfill_theme', ['id' => $id]);
    $page = $DB->count_records('question_gapfill_theme');
    $page --;
}
$recordcount = $DB->count_records('question_gapfill_theme');

$recordset = $DB->get_recordset('question_gapfill_theme');
$count = 0;
foreach ($recordset as $key => $value) {
    if ($count == $page) {
        $record = $value;
        break;
    }
    $count++;
}
$baseurl = new moodle_url('/question/type/gapfill/admin/theme_edit.php', ['page' => $page]);

$record->page = $page;
$mform = new gapfill_theme_edit_form($baseurl, ['record' => $record]);
if ($data = $mform->get_data()) {
    if (isset($data->save)) {
            $params = [
                'id' => $data->id,
                'name' => $data->name,
                'themecode' => $data->themecode
            ];
            $DB->update_record('question_gapfill_theme', $params);
            $record = $DB->get_record('question_gapfill_theme', ['id' => $data->id]);

    }
}
$mform->set_data($record);

echo $OUTPUT->header();
if (!$newrecord) {
    echo $OUTPUT->paging_bar($recordcount, $page, 1, $baseurl);
}

$mform->display();
echo $OUTPUT->footer();
