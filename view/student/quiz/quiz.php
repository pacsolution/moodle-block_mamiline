<?php

require_once __DIR__ . '/../../../../../config.php';
require_once(dirname(__FILE__) . '/../../../locallib.php');
require_once(dirname(__FILE__) . '/../../../classes/quiz.php');
require_once(dirname(__FILE__) . '/../../../classes/grade.php');
require_once __DIR__ . '/../../../../../lib/gradelib.php';
require_once __DIR__ . '/../../../../../mod/quiz/lib.php';
require_once __DIR__ . '/../../../../../mod/quiz/locallib.php';

require_login();

use mamiline\quiz;
use mamiline\grade;

$basedir = $CFG->wwwroot . '/blocks/mamiline';
$quizid = optional_param('quizid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

/* @var $DB moodle_database */
/* @var $USER object */
/* @var $OUTPUT core_renderer */
/* @var $CFG object */
/* @var $PAGE object */
global $DB, $USER, $OUTPUT, $CFG, $PAGE;

$context = context::instance_by_id(1);
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
    html_writer::link('/blocks/mamiline/view/student/timeline.php', get_string('timeline', 'block_mamiline')),
    array('class' => 'list-group-item')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/quiz.php', get_string('quiz', 'block_mamiline')),
    array('class' => 'list-group-item active')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/assign.php', get_string('assign', 'block_mamiline')),
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

$quiz = quiz::quiz($quizid);
$grade = mamiline\quiz::grades($quiz, $USER->id);
$grades = grade::usergrade($quiz, $USER->id);

$cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course);
$context = context_module::instance($cm->id);
$PAGE->set_context($context);

$feedback = quiz_feedback_for_grade($grades->rawgrade, $quiz, $context);

echo html_writer::start_tag('div', array('class' => 'row'));
echo html_writer::start_tag('div', array('class' => 'col-md-3'));

echo html_writer::tag('h3', get_string('quiz_info','block_mamiline') . '(' . $quiz->name . ')');
echo html_writer::tag('div', get_string('quiz_score_max','block_mamiline') . ' : ' . html_writer::tag('h1', $grade->items[0]->grades[3]->str_long_grade));
echo html_writer::start_tag('div', array('class' => 'progress'));
echo html_writer::start_tag('div', array('class' => 'progress-bar progress-bar-success',
    'role' => 'progressbar',
    'aria-valuenow' => '40',
    'aria-valuemin' => '0',
    'aria-valuemax' => '100',
    'style' => 'width:' . ((int)$grade->items[0]->grades[3]->grade)/((int)$grade->items[0]->grademax) * 100 . '%'));
echo html_writer::tag('span', '40% Complete (success)', array('class' => 'sr-only'));
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
$average = round(quiz::average($quiz),1);
echo html_writer::tag('div', get_string('quiz_average','block_mamiline') . ' : ' . html_writer::tag('h1', round($average, 1)));
echo html_writer::tag('div', get_string('quiz_feedback','block_mamiline') . ' : '  . html_writer::tag('div', $feedback));
//    echo html_writer::tag('div', html_writer::empty_tag('img', array('src' => $basedir . '/images/verygood.gif','align' => 'center' ,'class' => 'img-rounded', 'width' => '140', 'height' => '140')));
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', array('class' => 'col-md-5 col-md-offset-1'));
echo html_writer::tag('h3', get_string('quiz_graph_diff','block_mamiline'));
echo html_writer::tag('p', get_string('quiz_grade_graph_desc', 'block_mamiline'));
echo html_writer::start_div('', array('id' => 'line-quiz'));
echo html_writer::end_div();
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', array('class' => 'col-md-10'));
echo html_writer::tag('h3', get_string('quiz_logs','block_mamiline'));
echo html_writer::start_tag('table', array('class' => 'table table-striped'));
echo html_writer::start_tag('tr');
echo html_writer::start_tag('thread');
echo html_writer::tag('th', '#');
echo html_writer::tag('th', get_string('quiz_timestart',  'block_mamiline'));
echo html_writer::tag('th', get_string('quiz_timefinish', 'block_mamiline'));
echo html_writer::tag('th', get_string('quiz_state', 'block_mamiline'));
echo html_writer::tag('th', get_string('quiz_grade', 'block_mamiline'));
if(isset($file))
{
    echo html_writer::tag('th', '-');
}
echo html_writer::end_tag('tr');
echo html_writer::end_tag('thread');

$quiz_attempts = quiz_get_user_attempts($quizid, $USER->id, 'all', true);
$finished = 0;
$overdue = 0;
$inprogress = 0;
$abandoned = 0;

foreach($quiz_attempts as $quiz_attempt){
    $file = \mamiline\quiz::get_uploaded_file($quiz_attempt->id);

    if($quiz_attempt->timefinish == 0){
        $timefinish = '-';
    }else{
        $timefinish = userdate($quiz_attempt->timefinish);
    }

    switch($quiz_attempt->state){
        case 'finished' :
            $finished++;
            $str_state = get_string('quiz_state_finished',   'block_mamiline');
            $image_badge  = html_writer::start_tag('div');
            $image_badge .= html_writer::empty_tag('img', array('src' => $basedir . '/images/verygood.gif', 'class' => 'img-rounded', 'width' => '100', 'height' => '100'));
            $image_badge .= html_writer::end_tag('div');
            break;
        case 'inprogress' :
            $inprogress++;
            $str_state = get_string('quiz_state_inprogress', 'block_mamiline');
            $image_badge  = html_writer::start_tag('div');
            $image_badge .= html_writer::empty_tag('img', array('src' => $basedir . '/images/notgood.gif', 'class' => 'img-rounded', 'width' => '100', 'height' => '100'));
            $image_badge .= html_writer::end_tag('div');
            break;
        case 'overdue' :
            $overdue++;
            $str_state = get_string('quiz_state_overdue',    'block_mamiline');
            $image_badge  = html_writer::start_tag('div');
            $image_badge .= html_writer::empty_tag('img', array('src' => $basedir . '/images/notgood.gif', 'class' => 'img-rounded', 'width' => '100', 'height' => '100'));
            $image_badge .= html_writer::end_tag('div');
            break;
        case 'abandoned' :
            $abandoned++;
            $str_state = get_string('quiz_state_abandoned',  'block_mamiline');
            $image_badge  = html_writer::start_tag('div');
            $image_badge .= html_writer::empty_tag('img', array('src' => $basedir . '/images/notgood.gif', 'class' => 'img-rounded', 'width' => '100', 'height' => '100'));
            $image_badge .= html_writer::end_tag('div');
            break;
    }
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', $quiz_attempt->id);
    echo html_writer::tag('td', userdate($quiz_attempt->timestart));
    echo html_writer::tag('td', $timefinish);
    echo html_writer::tag('td', $str_state . $image_badge);
    echo html_writer::tag('td', round($quiz_attempt->sumgrades, 1));

    if(isset($file))
    {
        $url = moodle_url::make_pluginfile_url($file->contextid, $file->component, $file->filearea, $file->itemid, $file->filepath, $file->filename);
        echo html_writer::tag('td', html_writer::tag('a', $url, array('class'=>'btn btn-success')));
    }

    echo html_writer::end_tag('tr');
}
echo html_writer::end_tag('table');
echo html_writer::end_tag('div');

//受験状況グラフ生成
$jscode =  '
        Morris.Line({
            element : "line-quiz",
            xkey : "y",
            ykeys : ["a"],
            goals: ['. $grade->items[0]->grademax .'],
            parseTime : false,
            labels : ["'. get_string('quiz_grade', 'block_mamiline') .'"],
            data : [';
$i = 1;
foreach($quiz_attempts as $quiz_attempt){
    if($quiz_attempt->sumgrades == null){
        $jscode .= "{y:" . $i . ", a:0},";
    }else{
        $jscode .= "{y:" . $i . ", a:" . $quiz_attempt->sumgrades . "},";
    }
    $i++;
}
$jscode = substr($jscode, 0, -1);
$jscode .= ']});';

echo html_writer::end_tag('div');

//Script
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/jquery.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/prefixfree.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/raphael-min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/morris-0.4.3.min.js'));

echo html_writer::script($jscode);

echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
