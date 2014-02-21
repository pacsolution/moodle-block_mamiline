<?php

require_once __DIR__ . '/../../../../config.php';
require_once(dirname(__FILE__) . '/../../locallib.php');
require_once(dirname(__FILE__) . '/../../classes/common.php');
require_once(dirname(__FILE__) . '/../../classes/timeline.php');
require_once("$CFG->libdir/formslib.php");

use mamiline\common;
use mamiline\timeline;

global $DB, $USER, $OUTPUT, $CFG, $PAGE;
define('PAGENUM', 15);
$basedir = $CFG->wwwroot . '/blocks/mamiline';

require_login();

$mode = optional_param('mode', 0, PARAM_INT);
$courseid = optional_param('courseid', 2, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);

$context = context::instance_by_id(1);
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
echo html_writer::script(null, $basedir . '/js/prefixfree.min.js');
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/bootstrap.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/bootstrap-theme.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/messi.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/style.css', 'rel' => 'stylesheet'));

echo html_writer::end_tag('head');

echo html_writer::start_tag('body');

echo html_writer::start_tag('div', array('class' => 'container-fluid'));

echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('timeline', 'block_mamiline'), array('href' => new moodle_url($basedir . '/index.php'), 'class' => 'navbar-brand'));
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
//コース切り替え
$courses = timeline::courses($USER->id);
echo html_writer::start_tag('div', array('class' => 'span2 well'));
echo html_writer::tag('h4', get_string('timeline_choose_course', 'block_mamiline'));
echo html_writer::select($courses, 'courseid', $courses[$courseid]);
echo html_writer::end_tag('div');
//メニュー
echo html_writer::start_tag('div', array('class' => 'list-group'));
echo html_writer::link('../../index.php', get_string('top', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('timeline.php', get_string('timeline', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('quiz.php', get_string('quiz', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('assign.php', get_string('assign', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::link('forum.php', get_string('forum', 'block_mamiline'), array('class' => 'list-group-item'));
echo html_writer::end_tag('div');

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
    echo html_writer::start_tag('div', array('class' => 'col-md-8'));
    echo html_writer::tag('h3', get_string('timeline_my', 'block_mamiline'));

    $limit = array(PAGENUM * $page, PAGENUM * $page - PAGENUM);
    $sql = "SELECT l.id, l.course, l.module, l.action, l.url, c.fullname, l.cmid, l.time, l.userid FROM {log} as l
        JOIN {course} as c ON c.id = l.course
        WHERE l.userid = :userid
        ORDER BY l.time DESC";
    $logs = $DB->get_records_sql($sql, array('userid' => $USER->id), $limit[1], $limit[0]);
    $work = 1;

    echo html_writer::start_tag('ul', array('id'=>'timeline'));

    foreach($logs as $log){
        $action = timeline::action($log, $log->userid);

        echo html_writer::start_tag('li', array('class' =>'work' . $work));
        echo html_writer::empty_tag('input', array('class'=>'radio', 'id'=>'work' . $work, 'name'=>'works', 'type' =>'radio'));
        echo html_writer::start_tag('div', array('class'=>'relative'));
        echo html_writer::tag('label', $action['title'], array('for' => 'work' . $work));
        echo html_writer::tag('span', userdate($log->time, '%Y/%m/%d'), array('class' => 'date'));
        echo html_writer::empty_tag('span', array('class' => 'circle'));
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'content'));
        echo html_writer::tag('p', $action['message']);
        echo html_writer::end_tag('div');

        $work++;
    }

    echo html_writer::end_tag('ul');
    echo html_writer::end_tag('div');

}elseif($mode == 2){
    //指定したコース内のタイムライン
    echo html_writer::start_tag('div', array('class' => 'col-md-8'));

    if($courseid != 0){
        echo html_writer::tag('h3', common::course($courseid)->fullname);

        $limit = array(PAGENUM * $page, PAGENUM * $page - PAGENUM);
        $sql = "SELECT l.id, l.course, l.module, l.action, l.url, c.fullname, l.cmid, l.time, l.userid FROM {log} as l
        JOIN {course} as c ON c.id = l.course
        WHERE l.course = :courseid
        ORDER BY l.time DESC";
        $logs = $DB->get_records_sql($sql, array('courseid' => $courseid), $limit[1], $limit[0]);

        echo html_writer::start_tag('ul', array('id'=>'timeline'));

        $work = 1;
        foreach($logs as $log){
            $action = timeline::action($log, $log->userid);

            echo html_writer::start_tag('li', array('class' =>'work' . $work));
            echo html_writer::empty_tag('input', array('class'=>'radio', 'id'=>'work' . $work, 'name'=>'works', 'type' =>'radio'));
            echo html_writer::start_tag('div', array('class'=>'relative'));
            echo html_writer::tag('label', $action['title'], array('for' => 'work' . $work));
            echo html_writer::tag('span', userdate($log->time, '%Y/%m/%d'), array('class' => 'date'));
            echo html_writer::empty_tag('span', array('class' => 'circle'));
            echo html_writer::end_tag('div');
            echo html_writer::start_tag('div', array('class' => 'content'));
            echo html_writer::tag('p', $action['message']);
            echo html_writer::end_tag('div');

            $work++;
        }
    }
    echo html_writer::end_tag('ul');
    echo html_writer::end_tag('div');
}

$js = '
$("#menucourseid").change( function(){
    var url = "timeline.php?mode=2&courseid=" + $(this).val();
    console.log(url);
    window.location = url;
});
';

echo html_writer::script($js);
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');