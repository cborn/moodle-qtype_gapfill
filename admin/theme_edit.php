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
 * Import sample Gapfill questions f.
 *
 * This does the same as the standard xml import but easier
 * @package    qtype_gapfill
 * @copyright  2015 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

//admin_externalpage_setup('qtype_gapfill_theme_edit');
$previous = optional_param('previous', '', PARAM_TEXT);
$next = optional_param('next', '', PARAM_TEXT);
$id = optional_param('id', '', PARAM_INT);
global $PAGE;
$PAGE->set_context(context_system::instance());
$url = new moodle_url('/course/report/completion/index.php', ['course' => 'popo']);
$PAGE->set_url($url);

admin_externalpage_setup('qtype_gapfill_theme_edit');


/**
 *  Edit gapfill question type themes
 *
 * @copyright Marcus Green 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Form for editing gapfill quesiton type themes
 */
class gapfill_theme_edit_form extends moodleform {
    /**
     *
     * @var number
     */
    public $questioncategory;
    /**
     *
     * @var number
     */
    public $course;
    /**
     * mini form for entering the import details
     */
    protected function definition() {
        global $PAGE, $DB;
        $id = $this->_customdata['id'];

        $record = $DB->get_record('question_gapfill_theme', ['id' => $id]);
        $maxid = $DB->get_record_sql("SELECT MAX(id) as id FROM {question_gapfill_theme}")->id;

        $mform = $this->_form;
        $PAGE->requires->css('/question/type/gapfill/amd/src/codemirror/lib/codemirror.css');
        $PAGE->requires->css('/question/type/gapfill/amd/src/codemirror/addon/hint/show-hint.css');
        $PAGE->requires->js_call_amd('qtype_gapfill/theme_edit', 'init');


        //$themes = get_config('qtype_gapfill', 'themes');

        $mform->addElement('text', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $record->id);

        $mform->addElement('text', 'name', 'Name');
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $record->name);

        $mform->addElement('textarea', 'themecode', get_string('themes', 'qtype_gapfill'), ['rows' => 30, 'cols' => 80]);

        $mform->setDefault('themecode', $record->themecode);
        $mform->setType('themecode', PARAM_RAW);
        $this->add_action_buttons(true, 'Save');

        $navbuttons = [];
        $navbuttons[] = $mform->createElement('submit', 'previous', 'Previous');
        $navbuttons[] = $mform->createElement('submit', 'next', 'Next');
        if ($record->id  ==  $maxid) {
            $navbuttons[] = $mform->createElement('submit', 'newrecord', 'New theme');
        }
        $mform->addGroup($navbuttons);
        $this->definition_after_data();
    }

}
if ($id == '') {
    $sql = 'select min(id) id from {question_gapfill_theme}';
    $record = $DB->get_field_sql($sql);
    if ($record) {
        $id = $record;
    } else {
        $id = $DB->insert_record('question_gapfill_theme', (object) ['name' => '', 'themecode' => '']);
    }
}

if ($next > "") {
        $sql = 'SELECT * FROM {question_gapfill_theme}
        WHERE id >:id
        ORDER BY id';
        $record = $DB->get_record_sql($sql, ['id' => $id], IGNORE_MULTIPLE);
        $id = $record->id;
}
if ($previous > "") {
    $sql = 'SELECT * FROM {question_gapfill_theme}
    WHERE id < :id
    ORDER BY id';
    if ($record = $DB->get_record_sql($sql, ['id' => $id], IGNORE_MULTIPLE)) {
        $id = $record->id;
    }
}


$url = new moodle_url('/question/type/gapfill/admin/theme_edit.php', ['id' => $id]);
$mform = new gapfill_theme_edit_form($url, ['id' => $id]);

$message = '';
global $DB;

if ($data = $mform->get_data()) {
//     if (isset($data->next)) {
//         $sql = 'SELECT * FROM {question_gapfill_theme}
//         WHERE id >:id
//         ORDER BY id';
//         $record = $DB->get_records_sql($sql, ['id' => $id], IGNORE_MULTIPLE);
//     }
//     if (isset($data->Previous)) {
//         $message = 'Previous';
//     }
    if (isset($data->submitbutton)) {
        if ($id) {
            $record = $DB->get_record('question_gapfill_theme', ['id' => $id]);
            $params = [
                'id'   => $id,
                'name' => $data->themename,
                'themecode' => $data->themecode
            ];
            $DB->update_record('question_gapfill_theme', $params);
        }
    }

}


if ($data = $mform->get_data()) {
    if (isset($data->next) || isset($data->previous)) {
        $url->param('id', $id);
        redirect($url);
    }
}

echo $OUTPUT->header();
$mform->display();

echo $OUTPUT->footer();
