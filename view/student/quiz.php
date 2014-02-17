<?php

require_once __DIR__ . '/../../../../config.php';
require_once(dirname(__FILE__) . '/../../locallib.php');
require_once __DIR__ . '/../../../../lib/gradelib.php';
require_once __DIR__ . '/../../../../grade/querylib.php';
require_once __DIR__ . '/../../../../mod/quiz/lib.php';
require_once __DIR__ . '/../../../../mod/quiz/locallib.php';
require_once __DIR__ . '/../../../../mod/quiz/report/reportlib.php';
require_once(dirname(__FILE__) . '/../../classes/quiz.php');
require_once(dirname(__FILE__) . '/../../classes/grade.php');

global $CFG, $DB, $USER, $PAGE;
$basedir = $CFG->wwwroot . '/blocks/mamiline';

/* @var $DB moodle_database */
/* @var $CFG object */
/* @var $USER object */
/* @var $OUTPUT core_renderer */

use mamiline\quiz;
use mamiline\grade;

require_login();

$quizid = optional_param('quizid',0 ,PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);

echo html_writer::start_tag('html', array('lang' => 'ja'));
echo html_writer::start_tag('head');
echo html_writer::empty_tag('meta', array('charset' => 'UTF-8'));
echo html_writer::empty_tag('meta', array('http-equiv' => 'content-language', 'content' => 'ja'));
echo html_writer::empty_tag('meta', array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::tag('title', get_string('pluginname', 'block_mamiline'), array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::script(null, $basedir . '/js/jquery.min.js');
echo html_writer::script(null, $basedir . '/js/ccchart.js');
echo html_writer::script(null, $basedir . '/js/messi.min.js');
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/bootstrap.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/bootstrap-theme.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/messi.min.css', 'rel' => 'stylesheet'));
echo html_writer::end_tag('head');

echo html_writer::start_tag('body');

//NavBar
echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('quiz_attmpt', 'block_mamiline'), array('href' => new moodle_url($basedir . '/index.php'), 'class' => 'navbar-brand'));
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('class' => 'collapse navbar-collapse', 'id' => 'bs-example-navbar-collapse-1'));
echo html_writer::start_tag('ul', array('class' => 'nav navbar-nav navbar-right'));
echo html_writer::tag('li', html_writer::link(new moodle_url($basedir . '/index.php'), get_string('top', 'block_mamiline')));
echo html_writer::tag('li', html_writer::link(new moodle_url($basedir . '/index.php'), get_string('close', 'block_mamiline')));
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::end_tag('nav');

//SideBar
echo html_writer::start_tag('div', array('id' => 'sidebar', 'class' => 'col-md-2 sidebar-offcanvas', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('id' => 'userinfo', 'class' => 'well', 'align' => 'center'));
echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size'=>140)), array('id' => 'userinfo', 'class' => '', 'align' => 'center'));
echo fullname($USER);
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('class' => 'list-group'));
echo html_writer::link('../../index.php', get_string('top', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('timeline.php', get_string('timeline', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('quiz.php', get_string('quiz', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('assign.php', get_string('assign', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('forum.php', get_string('forum', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

$finished   = 0;
$overdue    = 0;
$inprogress = 0;
$abandoned  = 0;

if($quizid == 0){
    $sql = "SELECT qa.id, q.id as qid, q.name, q.course, qa.timestart, qa.timefinish, qa.state, q.grade, q.course, q.sumgrades as q_sumgrades, qa.sumgrades as qa_sumgrades
            FROM {quiz_attempts} as qa
            JOIN {user} as u ON u.id = qa.userid
            JOIN {quiz} as q ON qa.quiz = q.id
            WHERE qa.userid = $USER->id && qa.preview = 0
            GROUP BY q.id
            ORDER BY qa.timefinish DESC";
    $quiz_attempts = $DB->get_records_sql($sql);

    echo html_writer::start_tag('div', array('class' => 'container'));
    echo html_writer::start_tag('div', array('class' => 'col-md-10 well'));

    echo html_writer::tag('h3', get_string('quiz_attmpt', 'block_mamiline'));

    echo html_writer::start_tag('table', array('class' => 'table table-striped table-hover'));
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('quiz_coursename','block_mamiline'));
    echo html_writer::tag('th', get_string('quiz_name','block_mamiline'));
    echo html_writer::tag('th', get_string('quiz_timestart','block_mamiline'));
    echo html_writer::tag('th', get_string('quiz_timefinish','block_mamiline'));
    echo html_writer::tag('th', get_string('quiz_state','block_mamiline'));
    echo html_writer::tag('th', get_string('quiz_score_max','block_mamiline'));
    echo html_writer::tag('th', get_string('quiz_show_diff','block_mamiline'));
    echo html_writer::end_tag('thread');
    echo html_writer::end_tag('tr');

    foreach($quiz_attempts as $quiz){
        $cm = get_coursemodule_from_instance('quiz', $quiz->qid);
        $grades = grade_get_grades($quiz->course, 'mod', 'quiz', $quiz->qid, $USER->id);

        foreach($grades as $grade){
            foreach($grade as $g){
                foreach($g->grades as $gd){
                }
            }
        }
        if($quiz->timefinish == 0){
            $timefinish = '-';
        }else{
            $timefinish = userdate($quiz->timefinish);
        }

        $course = \mamiline\get_course($quiz->course);
        switch($quiz->state){
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
        echo html_writer::tag('td', html_writer::link(new moodle_url($CFG->wwwroot . '/blocks/mamiline/view/student/quiz.php',
                    array('quizid' => $quiz->qid)),
                get_string('quiz_show_diff', 'block_mamiline'), array('class' => 'btn btn-success')
            )
        );
        echo html_writer::end_tag('tr');
    }

    $sum = $finished + $inprogress + $overdue + $abandoned;
    echo html_writer::end_tag('table');
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('div', array('class' => 'well col-md-9'));

    echo html_writer::tag('h3', get_string('graph','block_mamiline'));

    echo html_writer::empty_tag('canvas', array('id' => 'quiz_status'));
    echo html_writer::end_tag('div');
    $jscode =  '
          var chartdata53 = {
            "config": {
                "title": "'. get_string('quiz_graph_status',   'block_mamiline') .'",
                "subTitle": "",
                "type": "pie",
                "useVal": "yes",
                "pieDataIndex": 2,
                "colNameFont": "100 18px",
                "pieRingWidth": 80,
                "pieHoleRadius": 40,
                "bg": "#fff",
                "xColor": "rgba(150,150,150,0.6)",
                "colorSet":
                    ["rgba(0,150,250,0.5)","rgba(200,0,250,0.4)","rgba(250,250,0,0.3)"],
                    "textColor": "#444",
                },
            "data": [
                ["小テスト数", ' . $sum . '],
                ["' . get_string('quiz_state_finished',   'block_mamiline') . '", '.$finished.'  ],
                ["' . get_string('quiz_state_abandoned',  'block_mamiline') . '", '.$abandoned.' ],
                ["' . get_string('quiz_state_inprogress', 'block_mamiline') . '", '.$inprogress.'],
                ["' . get_string('quiz_state_overdue',    'block_mamiline') . '", '.$overdue.'   ]
            ]
        };
        ccchart.init("quiz_status", chartdata53);
';
    echo html_writer::script($jscode);
    echo html_writer::end_tag('div');
}else{
    $quiz = quiz::quiz($quizid);

    $cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course);
    $context = context_module::instance($cm->id);
    $PAGE->set_context($context);

    $grades = grade::usergrade($quiz, $USER->id);

    $feedback = quiz_feedback_for_grade($grades->rawgrade, $quiz, $context);

    echo html_writer::start_tag('div', array('class' => 'row'));
    echo html_writer::start_tag('div', array('class' => 'col-md-3 well'));

    echo html_writer::tag('h3', get_string('quiz_info','block_mamiline'));
    echo html_writer::tag('div', get_string('quiz_score_max','block_mamiline') . ' : ' . html_writer::tag('h1', round($grades->rawgrade,1)));
    echo html_writer::start_tag('div', array('class' => 'progress'));
    echo html_writer::start_tag('div', array('class' => 'progress-bar progress-bar-success',
                                             'role' => 'progressbar',
                                             'aria-valuenow' => '40',
                                             'aria-valuemin' => '0',
                                             'aria-valuemax' => '100',
                                             'style' => 'width:' . $grades->rawgrade . '%'));
    echo html_writer::tag('span', '40% Complete (success)', array('class' => 'sr-only'));
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
    $average = quiz::average($quiz);
    echo html_writer::tag('div', get_string('quiz_average','block_mamiline') . ' : ');
    echo html_writer::tag('div', $average);
    echo html_writer::tag('div', get_string('quiz_feedback','block_mamiline') . ' : ');
    echo html_writer::tag('div', s($feedback));
    echo html_writer::tag('div', html_writer::empty_tag('img', array('src' => $basedir . '/images/verygood.gif','align' => 'center' ,'class' => 'img-rounded', 'width' => '140', 'height' => '140')));
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('class' => 'col-md-5 col-md-offset-1 well'));
    echo html_writer::tag('h3', get_string('quiz_graph_diff','block_mamiline'));
    echo html_writer::empty_tag('canvas', array('id' => 'quiz_diff'));
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
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thread');

    $quiz_attempts = quiz_get_user_attempts($quizid, $USER->id, 'all', true);
    foreach($quiz_attempts as $quiz_attempt){
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
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('table');
    echo html_writer::end_tag('div');

    //受験状況グラフ生成
    $jscode =  '
            var chartdata53 = {
                "config": {
                    "title": "'. $quiz->name .'",
                    "subTitle": "'. get_string('forum_graph_numofpost_subject', 'block_mamiline') .'",
                    "type": "line",
                    "bg": "#fff",
                    "xColor": "rgba(150,150,150,0.6)",
                    "colorSet":
                        ["rgba(0,150,250,0.5)","rgba(200,0,250,0.4)","rgba(250,250,0,0.3)"],
                    "textColor": "#000000",
                    "useMarker": "css-ring",
                    "useVal": "yes",
                    "lineWidth": 10,
                    "borderWidth": 2,
                    "markerWidth": 10,
                    "minX": 1,
                    "minY": 0
                },
                "data": [
                    ["受験回",';


    $i = 1;
    foreach($quiz_attempts as $quiz_attempt){
        if($i != 1)
            $jscode .= ',';
        echo $i;
        $i++;
    }
    echo "],['". get_string('quiz_grade', 'block_mamiline') ."',";
    $i = 1;
    foreach($quiz_attempts as $quiz_attempt){
        if($i != 1)
            echo ',';
        echo round($quiz_attempt->sumgrades, 1);
        $i++;
    }
    $jscode .= ']]};';
    $jscode .= 'ccchart.init("quiz_diff", chartdata53)';

    echo html_writer::script($jscode);
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

}

echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
