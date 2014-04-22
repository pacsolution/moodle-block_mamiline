<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/gradelib.php';
require_once __DIR__ . '/../../grade/querylib.php';
require_once __DIR__ . '/../../mod/quiz/lib.php';
require_once(dirname(__FILE__) . '/locallib.php');
require_once __DIR__ . '/classes/common.php';
require_once __DIR__ . '/classes/grade.php';
require_once __DIR__ . '/classes/quiz.php';
require_once __DIR__ . '/classes/role.php';

require_login();

/* @var $DB moodle_database */
/* @var $USER object */
/* @var $OUTPUT core_renderer */
/* @var $CFG object */
/* @var $PAGE object */
global $DB, $USER, $OUTPUT, $CFG, $PAGE;

$context = context::instance_by_id(1);
$courses = enrol_get_all_users_courses($USER->id);
$PAGE->set_context($context);

echo html_writer::start_tag('html', array('lang' => 'ja'));
echo html_writer::start_tag('head');
echo html_writer::empty_tag('meta', array('charset' => 'UTF-8'));
echo html_writer::empty_tag('meta', array('http-equiv' => 'content-language', 'content' => 'ja'));
echo html_writer::empty_tag('meta', array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::tag('title', get_string('pluginname', 'block_mamiline'), array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/bootstrap.min.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/sb-admin.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/profile.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/simplePagination.css'), 'rel' => 'stylesheet'));

echo html_writer::start_tag('body');

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

//左側メニューここから
echo html_writer::start_tag('nav', array('id' => 'sidebar', 'class' => 'navbar-default navbar-static-side'));
echo html_writer::start_tag('section', array('class' => 'row'));
echo html_writer::start_tag('article', array('class' => 'col-sm-12 col-md-12 col-lg-12'));
echo html_writer::start_tag('div', array('class' => 'profile'));
echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size'=>140, 'class' => 'img-circle')), array('id' => 'userinfo'));
echo html_writer::tag('p', fullname($USER));
if(\mamiline\role::is_moderator_courses($USER, $courses))
{
    echo html_writer::tag('p', get_string('roleasteacher', 'block_mamiline'));
}else{
    echo html_writer::tag('p', get_string('roleasstudent', 'block_mamiline'));
}
echo html_writer::end_tag('div');
echo html_writer::start_tag('ul', array('class' => 'list-group', 'id' => 'side-menu'));
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/index.php', get_string('top', 'block_mamiline')),
    array('class' => 'list-group-item active')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/timeline/', get_string('timeline', 'block_mamiline')),
    array('class' => 'list-group-item')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/quiz/', get_string('quiz', 'block_mamiline')),
    array('class' => 'list-group-item')
);
echo html_writer::tag('li',
    html_writer::link('/blocks/mamiline/view/student/assign/', get_string('assign', 'block_mamiline')),
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

//ダッシュボードページここから開始
echo html_writer::start_tag('div', array('id' => 'page-wrapper'));
echo html_writer::start_tag('div', array('class' => 'row'));
echo html_writer::tag('h3', get_string('dashboard', 'block_mamiline'));

//「所属しているコース」
echo html_writer::start_tag('div', array('class' => 'col-lg-5'));
echo html_writer::start_tag('div', array('class' => 'panel panel-default'));
echo html_writer::start_tag('div', array('class' => 'panel-heading'));
echo html_writer::tag('h4', get_string('course_yourcourses','block_mamiline'));
echo html_writer::end_tag('div');
echo html_writer::start_tag('table', array('class' => 'table table-hover'));
echo html_writer::start_tag('tr');
echo html_writer::start_tag('thread');
echo html_writer::tag('th', get_string('course_fullname','block_mamiline'));
echo html_writer::tag('th', get_string('course_startdate','block_mamiline'));
echo html_writer::tag('th', get_string('course_grade', 'block_mamiline'));
echo html_writer::end_tag('thread');
echo html_writer::end_tag('tr');
$i = 0;
foreach($courses as $course){
    if($i > 30){
        break;
    }
    if($course->id != SITEID){
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $course->fullname), array('class' => 'subscribelink'));
        echo html_writer::tag('td', userdate($course->startdate));
        echo html_writer::tag('td', \mamiline\grade::coursegrade($USER->id, $course->id)->str_long_grade);
        echo html_writer::end_tag('tr');
        $i++;
    }
}
echo html_writer::end_tag('table');
echo html_writer::end_tag('div');
echo html_writer::tag('a', get_string('view_all_courses', 'block_mamiline'),
    array('class' => 'btn btn-success', 'href' => new moodle_url('/blocks/mamiline/view/student/courses/')));
echo html_writer::end_tag('div');

//「ログイングラフ」
echo html_writer::start_div('col-lg-5');
echo html_writer::start_div('panel panel-default');
echo html_writer::start_div('panel-heading');
echo html_writer::tag('h4', get_string('login_graph','block_mamiline'));
echo html_writer::end_div();
echo html_writer::start_div('', array('id' => 'line-login'));
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();
$logins = \mamiline\common::logins($USER->id);
$date = array(
    date("m/d",strtotime("-6 day")),
    date("m/d",strtotime("-5 day")),
    date("m/d",strtotime("-4 day")),
    date("m/d",strtotime("-3 day")),
    date("m/d",strtotime("-2 day")),
    date("m/d",strtotime("-1 day")),
    date("m/d")
);
$js_graph_login = "
Morris.Line({
  element: 'line-login',
  data: [
    { date:'$date[0]', a:$logins[0]},
    { date:'$date[1]', a:$logins[1]},
    { date:'$date[2]', a:$logins[2]},
    { date:'$date[3]', a:$logins[3]},
    { date:'$date[4]', a:$logins[4]},
    { date:'$date[5]', a:$logins[5]},
    { date:'$date[6]', a:$logins[6]}
  ],
  xkey : 'date',
  ykeys : ['a'],
  smooth : false,
  onlyIntegers : true,
  labels: ['" . get_string('num_login', 'block_mamiline') . "'],
  parseTime: false
});
";

//「最近完了した小テスト」
echo html_writer::start_div('col-lg-5');
echo html_writer::start_div('panel panel-default');
echo html_writer::start_div('panel-heading');
echo html_writer::tag('h4', get_string('recent_quiz_finished','block_mamiline'));
echo html_writer::end_div();
$recents = mamiline\quiz::recent_finished($USER->id);
echo html_writer::start_tag('table', array('class' => 'table'));
echo html_writer::start_tag('tr');
echo html_writer::start_tag('thread');
echo html_writer::tag('th', get_string('quiz','block_mamiline'));
echo html_writer::tag('th', get_string('quiz_grade', 'block_mamiline'));
echo html_writer::tag('th', get_string('gradepass', 'block_mamiline'));
echo html_writer::tag('th', get_string('judge','block_mamiline'));
echo html_writer::end_tag('thread');
echo html_writer::end_tag('tr');
foreach($recents as $recent){
    $quiz = mamiline\quiz::quiz($recent->qid);
    $grade = mamiline\quiz::grades($quiz, $USER->id);
    if((int)$grade->items[0]->gradepass != 0 && ($grade->items[0]->gradepass <= $grade->items[0]->grade)){
        $judge = get_string('passed', 'block_mamiline');
        $icon = html_writer::tag('span', '', array('class' => 'glyphicon glyphicon-ok'));
    }elseif((int)$grade->items[0]->gradepass != 0){
        $judge = get_string('failed', 'block_mamiline');
        $icon = html_writer::tag('span', '', array('class' => 'glyphicon glyphicon-ng'));
    }else{
        $judge = get_string($recent->state, 'block_mamiline');
        $icon = html_writer::tag('span', '', array('class' => 'glyphicon glyphicon-ok'));
    }

    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', $grade->items[0]->name);
    echo html_writer::tag('td', $grade->items[0]->grades[3]->str_grade);
    if((int)$grade->items[0]->gradepass == 0){
        echo html_writer::tag('td', get_string('pass_unset', 'block_mamiline'));
    }else{
        echo html_writer::tag('td', $grade->items[0]->gradepass);
    }
    echo html_writer::tag('td', $judge . $icon);
    echo html_writer::end_tag('tr');
}
echo html_writer::end_tag('table');

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

echo html_writer::end_tag('div');

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

//Script
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/jquery.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/raphael-min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/morris-0.4.3.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/jquery.simplePagination.js'));
echo html_writer::script($js_graph_login);
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');