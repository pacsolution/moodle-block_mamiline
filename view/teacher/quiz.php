<?php

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../lib/gradelib.php';
require_once __DIR__ . '/../../../../grade/querylib.php';
require_once __DIR__ . '/../../../../mod/quiz/lib.php';
require_once __DIR__ . '/../../../../mod/quiz/locallib.php';
require_once(dirname(__FILE__) . '/../../classes/quiz.php');
require_once(dirname(__FILE__) . '/../../locallib.php');

use visualization\quiz;

global $DB, $USER, $PAGE, $OUTPUT;

$context = context_system::instance();
$PAGE->set_context($context);

require_login();
//教員からのアクセスのみ許可
require_capability('block/visualization:addinstance', $context);

$page     = required_param('page', PARAM_TEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$quizid   = optional_param('quizid', 0, PARAM_INT);

echo html_writer::start_tag('html', array('lang' => 'ja'));
echo html_writer::start_tag('head');
echo html_writer::empty_tag('meta', array('charset' => 'UTF-8'));
echo html_writer::empty_tag('meta', array('http-equiv' => 'content-language', 'content' => 'ja'));
echo html_writer::empty_tag('meta', array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::tag('title', get_string('pluginname', 'block_visualization'), array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::script(null, '/blocks/visualization/js/jquery.min.js');
echo html_writer::script(null, '/blocks/visualization/js/ccchart.js');
echo html_writer::script(null, '/blocks/visualization/js/messi.min.js');
echo html_writer::empty_tag('link', array('href' => '/blocks/visualization/css/bootstrap.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => '/blocks/visualization/css/bootstrap-theme.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => '/blocks/visualization/css/messi.min.css', 'rel' => 'stylesheet'));
echo html_writer::end_tag('head');

echo html_writer::start_tag('body');

echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('pluginname', 'block_visualization'), array('href' => new moodle_url('/blocks/visualization/index.php'), 'class' => 'navbar-brand'));
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('class' => 'collapse navbar-collapse', 'id' => 'bs-example-navbar-collapse-1'));
echo html_writer::start_tag('ul', array('class' => 'nav navbar-nav navbar-right'));
echo html_writer::tag('li', html_writer::link(new moodle_url('/blocks/visualization/index.php'), get_string('top', 'block_visualization')));
echo html_writer::tag('li', html_writer::link(new moodle_url('/blocks/visualization/index.php'), get_string('close', 'block_visualization')));
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::end_tag('nav');

echo html_writer::start_tag('div', array('class' => 'container'));
echo html_writer::start_tag('div', array('class' => 'row'));
echo html_writer::start_tag('div', array('class' => 'col-md-12 well'));

switch ($page) {
    //コース一覧
    case 'course_list' :
        $courses = $DB->get_records('course', null, 'shortname', 'id, shortname, fullname, startdate, summary');

        echo html_writer::start_tag('ol', array('class' => 'breadcrumb'));
        echo html_writer::tag('li', html_writer::link(new moodle_url('/blocks/visualization/view_quiz.php', array('page' => 'course_list')), get_string('top', 'block_visualization')));
        echo html_writer::end_tag('ol');

        echo html_writer::start_tag('table', array('class' => 'table table-striped'));
        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('thread');

        echo html_writer::tag('th', get_string('course_fullname', 'block_visualization'));
        echo html_writer::tag('th', get_string('course_startdate', 'block_visualization'));
        echo html_writer::tag('th', get_string('course_summary', 'block_visualization'));
        echo html_writer::end_tag('thread');
        echo html_writer::end_tag('tr');

        foreach ($courses as $course) {
            if ($course->id != SITEID) {
                echo html_writer::start_tag('tr');
                $url = new moodle_url('/blocks/visualization/view_quiz.php', array('courseid' => $course->id, 'page' => 'quiz_list'));
                echo html_writer::tag('td', html_writer::link($url, $course->fullname), array('class' => 'subscribelink'));
                echo html_writer::tag('td', userdate($course->startdate));
                echo html_writer::tag('td', $course->summary);
                echo html_writer::end_tag('tr');
            }
        }
        echo html_writer::end_tag('table');
        break;

    //クイズ一覧
    case 'quiz_list' :
        $quizes = $DB->get_records('quiz', array('course' => $courseid));
        $course = $DB->get_record('course', array('id' => $courseid), 'id, shortname, fullname, startdate, summary');

        $top_url = new moodle_url('/blocks/visualization/view/teacher/view_quiz.php', array('page' => 'course_list'));
        $quizlist_url = new moodle_url('/blocks/visualization/view/teacher/view_quiz.php', array('page' => 'quiz_list'));

        echo html_writer::start_tag('ol', array('class' => 'breadcrumb'));
        echo html_writer::tag('li', html_writer::link($top_url, get_string('top', 'block_visualization')));
        echo html_writer::tag('li', $course->fullname, array('class' => 'active'));
        echo html_writer::end_tag('ol');

        echo html_writer::tag('h4', get_string('quiz_getall', 'block_visualization'));

        echo html_writer::start_tag('div', array('class' => 'well'));
        echo html_writer::link(new moodle_url('/blocks/visualization/view/teacher/view_quiz.php', array('page' => 'result_by_course', 'courseid' => $course->id)), get_string('quiz_result_by_course', 'block_visualization'), array('class' => 'btn btn-success'));
        echo html_writer::end_tag('div');

        echo html_writer::tag('h4', get_string('quiz_list', 'block_visualization'));

        echo html_writer::start_tag('table', array('class' => 'table table-striped'));
        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('thread');

        echo html_writer::tag('th', get_string('course_fullname', 'block_visualization'));
        echo html_writer::tag('th', get_string('course_startdate', 'block_visualization'));

        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thread');

        foreach ($quizes as $quiz) {
            echo html_writer::start_tag('tr');
            $url = new moodle_url('/blocks/visualization/view/teacher/view_quiz.php', array('page' => 'result_by_quiz', 'courseid' => $course->id, 'quizid' => $quiz->id));
            echo html_writer::tag('td', html_writer::link($url, $quiz->name), array('class' => 'subscribelink'));
            echo html_writer::tag('td', userdate($quiz->timemodified));
            echo html_writer::end_tag('tr');
        }
        echo html_writer::end_tag('table');
        break;

    //小テスト結果表示
    case 'result_by_quiz' :
        $quiz = $DB->get_record('quiz', array('id' => $quizid));
        $course = $DB->get_record('course', array('id' => $courseid), 'id, shortname, fullname');

        $top_url = new moodle_url('/blocks/visualization/view/teacher/view_quiz.php', array('page' => 'course_list'));
        $quizlist_url = new moodle_url('/blocks/visualization/view/teacher/view_quiz.php', array('page' => 'quiz_list', 'courseid' => $courseid));

        echo html_writer::start_tag('ol', array('class' => 'breadcrumb'));
        echo html_writer::tag('li', html_writer::link($top_url, get_string('top', 'block_visualization')));
        echo html_writer::tag('li', html_writer::link($quizlist_url, $course->fullname), array('class' => 'active'));
        echo html_writer::tag('li', $quiz->name, array('class' => 'active'));
        echo html_writer::end_tag('ol');

        echo html_writer::tag('h3', $quiz->name);

        echo html_writer::start_tag('table', array('class' => 'table table-striped'));
        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('thread');
        echo html_writer::tag('th', get_string('student', 'block_visualization'));
        echo html_writer::tag('th', get_string('quiz_timestart', 'block_visualization'));
        echo html_writer::tag('th', get_string('quiz_timefinish', 'block_visualization'));
        echo html_writer::tag('th', get_string('quiz_timeattempt', 'block_visualization'));
        echo html_writer::tag('th', get_string('quiz_state', 'block_visualization'));
        echo html_writer::tag('th', get_string('quiz_grade', 'block_visualization'));
        echo html_writer::end_tag('thread');
        echo html_writer::end_tag('tr');

        $grades = quiz_get_user_grades($quiz, 0);
        foreach ($grades as $grade) {
            $user = $DB->get_record('user', array('id' => $grade->userid));
            $attempts = quiz_get_user_attempts($quizid, $user->id);
            $bestgrade = quiz_get_best_grade($quiz, $user->id);

            foreach ($attempts as $attempt) {
                echo html_writer::start_tag('tr');
                echo html_writer::tag('td', $OUTPUT->user_picture($user, array('size' => 50)) . fullname($user));
                echo html_writer::tag('td', userdate($attempt->timestart), array('class' => 'subscribelink'));
                echo html_writer::tag('td', userdate($attempt->timefinish), array('class' => 'subscribelink'));
                echo html_writer::tag('td', $attempt->timefinish - $attempt->timestart, array('class' => 'subscribelink'));
                echo html_writer::tag('td', get_string('quiz_state_' . $attempt->state, 'block_visualization'), array('class' => 'subscribelink'));
                echo html_writer::tag('td', ($attempt->sumgrades / $quiz->sumgrades * 100), array('class' => 'subscribelink'));
                echo html_writer::end_tag('tr');
            }
        }
        echo html_writer::end_tag('table');
        break;

    //小テスト結果表示
    case 'result_by_course' :
        $course = $DB->get_record('course', array('id' => $courseid), 'id, shortname, fullname');
        $quizes = $DB->get_records('quiz', array('course' => $courseid));

        $top_url = new moodle_url('/blocks/visualization/view/teacher/view_quiz.php', array('page' => 'course_list'));
        $quizlist_url = new moodle_url('/blocks/visualization/view/teacher/view_quiz.php', array('page' => 'quiz_list', 'courseid' => $courseid));

        echo html_writer::start_tag('ol', array('class' => 'breadcrumb'));
        echo html_writer::tag('li', html_writer::link($top_url, get_string('top', 'block_visualization')));
        echo html_writer::tag('li', html_writer::link($quizlist_url, $course->fullname), array('class' => 'active'));
        echo html_writer::end_tag('ol');

        foreach ($quizes as $quiz) {
            echo html_writer::tag('h3', $quiz->name);
            echo html_writer::start_tag('table', array('class' => 'table table-striped'));
            echo html_writer::start_tag('tr');
            echo html_writer::start_tag('thread');
            echo html_writer::tag('th', get_string('student', 'block_visualization'));
            echo html_writer::tag('th', get_string('quiz_timestart', 'block_visualization'));
            echo html_writer::tag('th', get_string('quiz_timefinish', 'block_visualization'));
            echo html_writer::tag('th', get_string('quiz_timeattempt', 'block_visualization'));
            echo html_writer::tag('th', get_string('quiz_state', 'block_visualization'));
            echo html_writer::tag('th', get_string('quiz_grade', 'block_visualization'));
            echo html_writer::end_tag('thread');
            echo html_writer::end_tag('tr');

            $grades = quiz_get_user_grades($quiz, 0);
            foreach ($grades as $grade) {
                $user = $DB->get_record('user', array('id' => $grade->userid));
                $attempts = quiz_get_user_attempts($quiz->id, $user->id);
                $bestgrade = quiz_get_best_grade($quiz, $user->id);

                foreach ($attempts as $attempt) {
                    echo html_writer::start_tag('tr');
                    echo html_writer::tag('td', $OUTPUT->user_picture($user, array('size' => 50)) . fullname($user));
                    echo html_writer::tag('td', userdate($attempt->timestart), array('class' => 'subscribelink'));
                    echo html_writer::tag('td', userdate($attempt->timefinish), array('class' => 'subscribelink'));
                    echo html_writer::tag('td', $attempt->timefinish - $attempt->timestart, array('class' => 'subscribelink'));
                    echo html_writer::tag('td', get_string('quiz_state_' . $attempt->state, 'block_visualization'), array('class' => 'subscribelink'));
                    echo html_writer::tag('td', ($attempt->sumgrades / $quiz->sumgrades * 100), array('class' => 'subscribelink'));
                    echo html_writer::end_tag('tr');
                }
            }
            echo html_writer::end_tag('table');
        }

        break;

    //学生ごとの小テスト表示
    case 'all_summary' :
        $courseid = required_param('courseid', PARAM_INT);
        $quizes = quiz::quizes($courseid);

        echo html_writer::start_tag('table', array('class' => 'table table-striped'));
        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('thread');
        echo html_writer::tag('th', get_string('quiz_name', 'block_visualization'));
        echo html_writer::tag('th', get_string('quiz_attempts_num', 'block_visualization'));
        echo html_writer::tag('th', get_string('quiz_attempts_num_students', 'block_visualization'));
        echo html_writer::tag('th', get_string('quiz_total_grade', 'block_visualization'));

        echo html_writer::end_tag('thread');
        echo html_writer::end_tag('tr');

        foreach ($quizes as $quiz) {
            $count_attempts = quiz::count_attemts($quiz->id);
            $attempts = quiz::attempts($quiz->id);
            $grades = quiz_get_user_grades($quiz);
            $count_students = count($grades);
            $total_grade = quiz::grades($quiz);

            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', $quiz->name);
            echo html_writer::tag('td', $count_students);
            echo html_writer::tag('td', $count_attempts);
            echo html_writer::tag('td', $total_grade);
            echo html_writer::end_tag('tr');
        }
        echo html_writer::end_tag('table');

        break;

    //TODO
    default :
        break;
}

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');