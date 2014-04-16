<?php

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../lib/gradelib.php';
require_once __DIR__ . '/../../../../grade/querylib.php';
require_once __DIR__ . '/../../../../mod/quiz/lib.php';
require_once(dirname(__FILE__) . '/../../locallib.php');
require_once("$CFG->libdir/formslib.php");

require_once __DIR__ . '/../../classes/common.php';
require_once __DIR__ . '/../../classes/grade.php';
require_once __DIR__ . '/../../classes/timeline.php';

use mamiline\common;
use mamiline\timeline;

/*
   $mode = 0 : 選択画面を表示
   $mode = 1 : 自分のみのタイムラインを表示
   $mode = 2 : 指定したコースのタイムラインを表示
*/
$mode = optional_param('mode', 0, PARAM_INT);
$courseid = optional_param('courseid', 2, PARAM_INT);
//タイムラインを30件ごとに分ける
define('PAGENUM', 30);
$pagenum = optional_param('page', 1, PARAM_INT);

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
echo html_writer::tag('title', get_string('pluginname', 'block_mamiline'), array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/bootstrap.min.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/sb-admin.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/timeline.css'), 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => new moodle_url('/blocks/mamiline/css/profile.css'), 'rel' => 'stylesheet'));

//Script
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/jquery.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/prefixfree.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/raphael-min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/morris-0.4.3.min.js'));

echo html_writer::start_tag('body');

echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('pluginname', 'block_mamiline') . '/' . get_string('timeline', 'block_mamiline'), array('href' => new moodle_url('/blocks/mamiline/index.php'), 'class' => 'navbar-brand'));
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
if(has_capability('block/mamiline:viewteacher', $context)){ //ロール(学生/教員)を表示
    echo html_writer::tag('p', get_string('roleasteacher', 'block_mamiline'));
}else{
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
    array('class' => 'list-group-item active')
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
    array('class' => 'list-group-item')
);
echo html_writer::end_tag('ul');
echo html_writer::end_tag('article');
echo html_writer::end_tag('section');
echo html_writer::end_tag('nav');
//左側メニューここまで

echo html_writer::start_tag('div', array('id' => 'page-wrapper'));
echo html_writer::start_tag('div', array('class' => 'row'));

//コース切り替え
if($mode == 2){
    echo html_writer::start_tag('div', array('class' => 'span2 well'));
    echo html_writer::tag('h4', get_string('timeline_choose_course', 'block_mamiline'));
    echo html_writer::select(timeline::courses($USER->id), 'courseid', timeline::courses($USER->id)[$courseid]);
    echo html_writer::end_tag('div');
}

echo html_writer::end_tag('div');

if($mode == 0){
    //「自分のみのタイムライン」か「コースメンバーのタイムラインにするかどうか」を選択させる画面
    echo html_writer::start_tag('div', array('class' => 'col-md-8'));
    echo html_writer::tag('h3', get_string('timeline_choose_mode', 'block_mamiline'));

    echo html_writer::start_tag('table', array('class' => 'table table-striped table-hover'));
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('timeline_my_desc','block_mamiline'));
    echo html_writer::tag('td', html_writer::tag('a', get_string('timeline_my', 'block_mamiline'),  array('href'=>new moodle_url('timeline.php', array('mode'=>1)), 'class' => 'btn btn-success')));
    echo html_writer::end_tag('tr');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('timeline_course_desc','block_mamiline'));
    echo html_writer::tag('td', html_writer::tag('a', get_string('timeline_course', 'block_mamiline'),  array('href'=>new moodle_url('timeline.php', array('mode'=>2)), 'class' => 'btn btn-success')));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('table');
    echo html_writer::end_tag('div');

}elseif($mode == 1){
    //自分のみのタイムライン
    $limit = array(PAGENUM * $pagenum, PAGENUM * $pagenum - PAGENUM);
    $sql = "SELECT l.id, l.course, l.module, l.action, l.url, c.fullname, l.cmid, l.time, l.userid FROM {log} as l
        JOIN {course} as c ON c.id = l.course
        WHERE l.userid = :userid
        ORDER BY l.time DESC";
    $logs = $DB->get_records_sql($sql, array('userid' => $USER->id), $limit[1], $limit[0]);

    echo html_writer::start_tag('div', array('class' => 'page-header'));
    echo html_writer::tag('h1', get_string('timeline_my', 'block_mamiline'),array('id' => 'timeline'));
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('ul', array('class' => 'timeline'));

    foreach($logs as $log){
        $action = timeline::action($log, $log->userid);

        echo html_writer::start_tag('li');
        echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size'=>50, 'class' => 'img-circle')), array('class' =>'timeline-badge'));
        echo html_writer::start_tag('div', array('class' =>'timeline-panel'));
        echo html_writer::start_tag('div', array('class' =>'timeline-heading'));
        echo html_writer::tag('h4', $action['title'], array('class' =>'timeline-panel'));
        echo html_writer::start_tag('p');
        echo html_writer::start_tag('small', array('class' => 'text-muted'));
        echo html_writer::end_tag('small');
        echo userdate($log->time, '%Y/%m/%d %H:%m');
        echo html_writer::end_tag('p');
        echo html_writer::start_tag('div', array('class' => 'timeline-body'));
        echo html_writer::tag('p', $action['message']);
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('li');
    }
    echo html_writer::end_tag('ul');

}elseif($mode == 2){
    //指定したコース内のタイムライン
    if($courseid != 0){
        $limit = array(PAGENUM * $pagenum, PAGENUM * $pagenum - PAGENUM);
        $sql = "SELECT l.id, l.course, l.module, l.action, l.url, c.fullname, l.cmid, l.time, l.userid FROM {log} as l
        JOIN {course} as c ON c.id = l.course
        WHERE l.course = :courseid
        ORDER BY l.time DESC";
        $logs = $DB->get_records_sql($sql, array('courseid' => $courseid), $limit[1], $limit[0]);

        echo html_writer::start_tag('ul', array('id'=>'timeline'));

        echo html_writer::start_tag('div', array('class' => 'page-header'));
        echo html_writer::tag('h1', common::course($courseid)->fullname, array('id' => 'timeline'));
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('ul', array('class' => 'timeline'));

        $isteacher = 1;
        foreach($logs as $log){
            $context = context_course::instance($courseid);
            $roles = get_user_roles($context, $log->userid);

            foreach($roles as $role){
                if($role->roleid != 5){
                    $isteacher = 1;
                    break;
                }else{
                    $isteacher = 0;
                    break;
                }
           }
           if($isteacher){
               continue;
           }
            $action = timeline::action($log, $log->userid);
            $t_user = mamiline\common::user($log->userid);

            echo html_writer::start_tag('li');
            echo html_writer::start_tag('div', array('class' =>'timeline-badge'));
            echo html_writer::tag('div', $OUTPUT->user_picture($t_user, array('size'=>50, 'class' => 'img-circle')), array('class' =>'timeline-badge'));
            echo html_writer::end_tag('div');

            echo html_writer::start_tag('div', array('class' =>'timeline-panel'));
            echo html_writer::start_tag('div', array('class' =>'timeline-heading'));
            echo html_writer::tag('h4', $action['title'], array('class' =>'timeline-panel'));
            echo html_writer::start_tag('p');
            echo html_writer::start_tag('small', array('class' => 'text-muted'));
            echo html_writer::end_tag('small');
            echo userdate($log->time, '%Y/%m/%d %H:%m');
            echo html_writer::end_tag('p');
            echo html_writer::start_tag('div', array('class' => 'timeline-body'));
            echo html_writer::tag('p', $action['message']);
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('li');
        }
        echo html_writer::end_tag('ul');
    }
}

$js_change = '
$("#menucourseid").change( function(){
    var url = "timeline.php?mode=2&courseid=" + $(this).val();
    console.log(url);
    window.location = url;
});
';

echo html_writer::script($js_change);

echo html_writer::end_tag('body');
echo html_writer::end_tag('html');