<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/gradelib.php';
require_once __DIR__ . '/../../grade/querylib.php';
require_once __DIR__ . '/../../mod/quiz/lib.php';
require_once(dirname(__FILE__) . '/locallib.php');

global $DB, $USER, $OUTPUT, $CFG, $PAGE;

use mamiline\course;

require_login();

$context = context::instance_by_id(1);
$PAGE->set_context($context);

echo html_writer::start_tag('html', array('lang' => 'ja'));
echo html_writer::start_tag('head');
echo html_writer::empty_tag('meta', array('charset' => 'UTF-8'));
echo html_writer::empty_tag('meta', array('http-equiv' => 'content-language', 'content' => 'ja'));
echo html_writer::empty_tag('meta', array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::tag('title', get_string('pluginname', 'block_mamiline'), array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::script(null, '/blocks/mamiline/js/jquery.min.js');
echo html_writer::script(null, '/blocks/mamiline/js/ccchart.js');
echo html_writer::script(null, '/blocks/mamiline/js/messi.min.js');
echo html_writer::empty_tag('link', array('href' => '/blocks/mamiline/css/bootstrap.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => '/blocks/mamiline/css/bootstrap-theme.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => '/blocks/mamiline/css/messi.min.css', 'rel' => 'stylesheet'));
echo html_writer::end_tag('head');

echo html_writer::start_tag('body');

echo html_writer::start_tag('div', array('class' => 'container-fluid'));

echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('pluginname', 'block_mamiline'), array('href' => new moodle_url('/blocks/mamiline/index.php'), 'class' => 'navbar-brand'));
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('class' => 'collapse navbar-collapse', 'id' => 'bs-example-navbar-collapse-1'));
echo html_writer::start_tag('ul', array('class' => 'nav navbar-nav navbar-right'));
echo html_writer::tag('li', html_writer::link(new moodle_url('/blocks/mamiline/index.php'), get_string('top', 'block_mamiline')));
echo html_writer::tag('li', html_writer::link(new moodle_url('/blocks/mamiline/index.php'), get_string('close', 'block_mamiline')));
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::end_tag('nav');

//SideBar
echo html_writer::start_tag('div', array('id' => 'sidebar', 'class' => 'col-md-2 sidebar-offcanvas', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('id' => 'userinfo', 'class' => 'well', 'align' => 'center'));
echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size'=>140)), array('id' => 'userinfo', 'class' => '', 'align' => 'center'));
echo fullname($USER);
echo html_writer::end_tag('div');
//SideBar-Menu
echo html_writer::start_tag('div', array('class' => 'list-group'));
echo html_writer::link('index.php', get_string('top', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('view/student/timeline.php', get_string('timeline', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('view/student/quiz.php', get_string('quiz', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('view/student/assign.php', get_string('assign', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('view/student/forum.php', get_string('forum', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', array('class' => 'row'));

//enrol course list
echo html_writer::start_tag('div', array('class' => 'col-md-9 well'));
echo html_writer::tag('h3', get_string('course_yourcourses','block_mamiline'));
echo html_writer::start_tag('table', array('class' => 'table table-striped'));
echo html_writer::start_tag('tr');
echo html_writer::start_tag('thread');
echo html_writer::tag('th', get_string('course_fullname','block_mamiline'));
echo html_writer::tag('th', get_string('course_startdate','block_mamiline'));
echo html_writer::end_tag('thread');
echo html_writer::end_tag('tr');
$courses = enrol_get_all_users_courses($USER->id);
foreach($courses as $course){
    if($course->id != SITEID){
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $course->fullname), array('class' => 'subscribelink'));
        echo html_writer::tag('td', userdate($course->startdate));

        echo html_writer::end_tag('tr');
    }
}
echo html_writer::end_tag('table');
echo html_writer::end_tag('div');

//Login Graph
//echo html_writer::start_tag('div', array('class' => 'col-md-9 well'));
//echo html_writer::tag('h3', get_string('login_graph','block_mamiline'));

echo html_writer::end_tag('div');

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
