<?php

require_once __DIR__ . '/../../../../../config.php';
require_once(dirname(__FILE__) . '/../../../locallib.php');
require_once(dirname(__FILE__) . '/../../../classes/common.php');
require_once(dirname(__FILE__) . '/../../../classes/quiz.php');
require_once(dirname(__FILE__) . '/../../../classes/grade.php');
require_once __DIR__ . '/../../../../../lib/gradelib.php';
require_once __DIR__ . '/../../../../../mod/quiz/lib.php';
require_once __DIR__ . '/../../../../../mod/quiz/locallib.php';

use mamiline\common;
use mamiline\quiz;
use mamiline\grade;

require_login();

$basedir = $CFG->wwwroot . '/blocks/mamiline';
$courseid = required_param('courseid', PARAM_INT);

/* @var $DB moodle_database */
/* @var $USER object */
/* @var $OUTPUT core_renderer */
/* @var $CFG object */
/* @var $PAGE object */
global $DB, $USER, $OUTPUT, $CFG, $PAGE;

$context = context_course::instance($courseid);
$PAGE->set_context($context);

echo html_writer::start_tag('html', array('lang' => 'ja'));
echo html_writer::start_tag('head');
echo html_writer::empty_tag('meta', array('charset' => 'UTF-8'));
echo html_writer::empty_tag('meta', array('http-equiv' => 'content-language', 'content' => 'ja'));
echo html_writer::empty_tag('meta', array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::tag('title', get_string('pluginname', 'block_mamiline'), array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/bootstrap.min.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/sb-admin.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/timeline.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/profile.css'), 'rel' => 'stylesheet'));

echo html_writer::start_tag('body');

echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('pluginname', 'block_mamiline') . '/' . get_string('quiz', 'block_mamiline'), array('href' => new moodle_url('/blocks/mamiline/index.php'), 'class' => 'navbar-brand'));
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('class' => 'collapse navbar-collapse', 'id' => 'bs-example-navbar-collapse-1'));
echo html_writer::start_tag('ul', array('class' => 'nav navbar-nav navbar-right'));
echo html_writer::tag('li', html_writer::link(new moodle_url('/blocks/mamiline/index.php'), get_string('top', 'block_mamiline')));
echo html_writer::tag('li', html_writer::link(new moodle_url('/blocks/mamiline/index.php'), get_string('close', 'block_mamiline')));
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::end_tag('nav');

//左側メニューここから
echo html_writer::start_tag('nav', array('id' => 'sidebar', 'class' => 'navbar-default navbar-static-side'));
echo html_writer::start_tag('section', array('class' => 'row'));
echo html_writer::start_tag('article', array('class' => 'col-sm-12 col-md-12 col-lg-12'));
echo html_writer::start_tag('div', array('class' => 'profile'));
echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size' => 140, 'class' => 'img-circle')), array('id' => 'userinfo'));
echo html_writer::tag('p', fullname($USER));
if (has_capability('block/mamiline:viewteacher', $context)) { //ロール(学生/教員)を表示
    echo html_writer::tag('p', get_string('roleasteacher', 'block_mamiline'));
} else {
    echo html_writer::tag('p', get_string('roleasstudent', 'block_mamiline'));
}
echo html_writer::end_tag('div');
echo html_writer::start_tag('ul', array('class' => 'list-group', 'id' => 'side-menu'));
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/index.php', get_string('top', 'block_mamiline')),
    array('class' => 'list-group-item')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/timeline/', get_string('timeline', 'block_mamiline')),
    array('class' => 'list-group-item')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/quiz/', get_string('quiz', 'block_mamiline')),
    array('class' => 'list-group-item active')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/assign', get_string('assign', 'block_mamiline')),
    array('class' => 'list-group-item')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/forum/', get_string('forum', 'block_mamiline')),
    array('class' => 'list-group-item')
);
echo html_writer::end_tag('ul');
echo html_writer::end_tag('article');
echo html_writer::end_tag('section');
echo html_writer::end_tag('nav');
//左側メニューここまで

echo html_writer::start_tag('div', array('id' => 'page-wrapper'));
echo html_writer::start_tag('div', array('class' => 'row'));

$finished = 0;
$overdue = 0;
$inprogress = 0;
$abandoned = 0;

echo html_writer::start_tag('div', array('class' => 'col-md-9'));
echo html_writer::tag('h3', get_string('quiz_attempt', 'block_mamiline'));
echo html_writer::start_tag('table', array('class' => 'table table-striped table-hover'));
echo html_writer::start_tag('tr');
echo html_writer::tag('th', get_string('quiz_coursename', 'block_mamiline'));
echo html_writer::tag('th', get_string('quiz_name', 'block_mamiline'));
echo html_writer::tag('th', get_string('quiz_timestart', 'block_mamiline'));
echo html_writer::tag('th', get_string('quiz_timefinish', 'block_mamiline'));
echo html_writer::tag('th', get_string('quiz_state', 'block_mamiline'));
echo html_writer::tag('th', get_string('quiz_score_max', 'block_mamiline'));
echo html_writer::tag('th', get_string('quiz_show_diff', 'block_mamiline'));
echo html_writer::end_tag('thread');
echo html_writer::end_tag('tr');

$quiz_attempts = quiz::finished_attenpts($USER->id, $courseid);
$unfinished = quiz::unfinish($USER->id, $courseid);

foreach ($quiz_attempts as $quiz) {
    $cm = get_coursemodule_from_instance('quiz', $quiz->qid);
    $grades = grade_get_grades($quiz->course, 'mod', 'quiz', $quiz->qid, $USER->id);

    foreach ($grades as $grade) {
        foreach ($grade as $g) {
            foreach ($g->grades as $gd) {
            }
        }
    }
    if ($quiz->timefinish == 0) {
        $timefinish = '-';
    } else {
        $timefinish = userdate($quiz->timefinish);
    }

    $course = common::course($quiz->course);
    switch ($quiz->state) {
        case 'finished' :
            $finished++;
            $str_state = get_string('quiz_state_finished', 'block_mamiline');
            break;
        case 'inprogress' :
            $inprogress++;
            $str_state = get_string('quiz_state_inprogress', 'block_mamiline');
            break;
        case 'overdue' :
            $overdue++;
            $str_state = get_string('quiz_state_overdue', 'block_mamiline');
            break;
        case 'abandoned' :
            $abandoned++;
            $str_state = get_string('quiz_state_abandoned', 'block_mamiline');
            break;
    }
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', html_writer::link(new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $course->id)), s($course->fullname)));
    echo html_writer::tag('td', html_writer::link(new moodle_url($CFG->wwwroot . '/mod/quiz/view.php', array('id' => $quiz->id)), s($quiz->name)));
    echo html_writer::tag('td', userdate($quiz->timestart));
    echo html_writer::tag('td', $timefinish);
    echo html_writer::tag('td', $str_state);
    echo html_writer::tag('td', round($gd->grade, 1) . "/" . round($g->grademax, 1));
    echo html_writer::tag('td', html_writer::link(new moodle_url($CFG->wwwroot . '/blocks/mamiline/view/student/quiz/quiz.php',
                array('quizid' => $quiz->qid)),
            get_string('quiz_show_diff', 'block_mamiline'), array('class' => 'btn btn-success')
        )
    );
    echo html_writer::end_tag('tr');
}

$data =  "{label : '" . get_string('finished', 'block_mamiline') . "', value : $finished},";
$data .= "{label : '" . get_string('inprogress', 'block_mamiline') . "', value : $inprogress},";
$data .= "{label : '" . get_string('overdue', 'block_mamiline') . "', value : $overdue},";
$data .= "{label : '" . get_string('abandoned', 'block_mamiline') . "', value : $abandoned},";
$data .= "{label : '" . get_string('unfinished', 'block_mamiline') . "', value : $unfinished}";

$sum = $finished + $inprogress + $overdue + $abandoned;
echo html_writer::end_tag('table');
echo html_writer::end_tag('div');

//グラフ
echo html_writer::start_tag('div', array('class' => 'col-md-4'));
echo html_writer::tag('h3', get_string('quiz_graph_status', 'block_mamiline'));
echo html_writer::start_div('', array('id' => 'donut-quizstatus'));
echo html_writer::end_div();
echo html_writer::end_tag('div');
$js = "
Morris.Donut({
  element: 'donut-quizstatus',
  data: [
    $data
  ]
});
";
echo html_writer::end_tag('div');

//Script
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/jquery.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/raphael-min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/morris-0.4.3.min.js'));
echo html_writer::script($js);

echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
