<?php

require_once __DIR__ . '/../../../../../config.php';
require_once __DIR__ . '/../../../../../lib/gradelib.php';
require_once __DIR__ . '/../../../../../grade/querylib.php';
require_once __DIR__ . '/../../../../../mod/forum/lib.php';
require_once(dirname(__FILE__) . '/../../../locallib.php');
require_once __DIR__ . '/../../../classes/common.php';
require_once __DIR__ . '/../../../classes/forum.php';
require_once __DIR__ . '/../../../classes/grade.php';

require_login();

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
echo html_writer::tag('title', get_string('pluginname', 'block_mamiline') . '/' . get_string('forum', 'block_mamiline'), array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/bootstrap.min.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/sb-admin.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/profile.css'), 'rel' => 'stylesheet'));

echo html_writer::start_tag('body');

echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('pluginname', 'block_mamiline') . '/' . get_string('forum', 'block_mamiline'), array('href' => new moodle_url('/blocks/mamiline/index.php'), 'class' => 'navbar-brand'));
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
    array('class' => 'list-group-item')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/assign.php', get_string('assign', 'block_mamiline')),
    array('class' => 'list-group-item')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/forum/', get_string('forum', 'block_mamiline')),
    array('class' => 'list-group-item active')
);
echo html_writer::end_tag('ul');
echo html_writer::end_tag('article');
echo html_writer::end_tag('section');
echo html_writer::end_tag('nav');
//左側メニューここまで

//フォーラムページここから開始
echo html_writer::start_tag('div', array('id' => 'page-wrapper'));
echo html_writer::start_tag('div', array('class' => 'row'));

//「投稿したフォーラム一覧」
echo html_writer::start_tag('div', array('class' => 'col-lg-12'));
echo html_writer::start_tag('div', array('class' => 'panel panel-default'));
echo html_writer::start_tag('div', array('class' => 'panel-heading'));
echo html_writer::tag('h3', get_string('forum_posted_course', 'block_mamiline'));
echo html_writer::end_tag('div');
echo html_writer::start_tag('table', array('class' => 'table table-striped'));
echo html_writer::start_tag('tr');
echo html_writer::start_tag('thread');
echo html_writer::tag('th', get_string('course_fullname', 'block_mamiline'));
echo html_writer::tag('th', get_string('course_grade', 'block_mamiline'));
echo html_writer::end_tag('thread');
echo html_writer::end_tag('tr');
$courses = forum_get_courses_user_posted_in($USER);
foreach ($courses as $course) {
    if ($course->id != SITEID) {
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', html_writer::link(new moodle_url('/blocks/mamiline/view/student/forum/list.php', array('courseid' => $course->id)), $course->fullname), array('class' => 'subscribelink'));
        echo html_writer::tag('td', html_writer::link(new moodle_url('/blocks/mamiline/view/student/forum/list.php', array('courseid' => $course->id)), get_string('forum_view_detail', 'block_mamiline'), array('class' => 'btn btn-success')));
        echo html_writer::end_tag('tr');
    }
}
echo html_writer::end_tag('table');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');


/*
echo html_writer::start_tag('div', array('class' => 'col-lg-5'));
echo html_writer::start_tag('div', array('class' => 'panel panel-default'));
echo html_writer::start_tag('div', array('class' => 'panel-heading'));
echo html_writer::tag('h4', get_string('forum_graph_numofpost_title', 'block_mamiline'));
echo html_writer::start_tag('div', array('id' => 'graph_forum'));
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', array('class' => 'col-lg-5'));
echo html_writer::start_tag('div', array('class' => 'panel panel-default'));
echo html_writer::start_tag('div', array('class' => 'panel-heading'));
echo html_writer::tag('h4', get_string('forum_grade_ranking', 'block_mamiline'));
//$ranking = mamiline\forum::grade_ranking($USER->id);
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

*/

//Script
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/jquery.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/raphael-min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/morris-0.4.3.min.js'));

echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
