<?php

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../locallib.php';
require_once __DIR__ . '/../../classes/common.php';

use mamiline\common;

/* @var $DB moodle_database */
/* @var $CFG object */
/* @var $USER object */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$basedir = $CFG->wwwroot . '/blocks/mamiline';
$userid = $USER->id;
require_login();

$context = context::instance_by_id(1);
$PAGE->set_context($context);

$forumid = optional_param('page', 1, PARAM_INT);

echo html_writer::start_tag('html', array('lang' => 'ja'));
echo html_writer::start_tag('head');
echo html_writer::empty_tag('meta', array('charset' => 'UTF-8'));
echo html_writer::empty_tag('meta', array('http-equiv' => 'content-language', 'content' => 'ja'));
echo html_writer::empty_tag('meta', array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::tag('title', get_string('forum', 'block_mamiline'), array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::script(null, $basedir . '/js/jquery.min.js');
echo html_writer::script(null, $basedir . '/js/ccchart.js');
echo html_writer::script(null, $basedir . '/js/messi.min.js');
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/bootstrap.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/bootstrap-theme.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/messi.min.css', 'rel' => 'stylesheet'));
echo html_writer::end_tag('head');

echo html_writer::start_tag('body');

echo html_writer::start_tag('div', array('class' => 'container-fluid'));

echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('pluginname', 'block_mamiline'), array('href' => new moodle_url($basedir . '/index.php'), 'class' => 'navbar-brand'));
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
echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size'=>100)), array('id' => 'userinfo', 'class' => '', 'align' => 'center'));
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

echo html_writer::start_tag('div', array('class' => 'row'));
echo html_writer::start_tag('div', array('class' => 'col-md-8 well'));
echo html_writer::tag('h3', get_string('forum', 'block_mamiline'));

global $DB, $USER, $CFG;
$forum_graph_data = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

$sql = "SELECT {forum_posts}.id, {forum}.name, {forum}.course, {forum_discussions}.name, {forum_posts}.created, {forum_posts}.subject, {forum_posts}.message, {forum}.name FROM {forum_posts}
            JOIN {forum_discussions} ON {forum_posts}.discussion = {forum_discussions}.id
            JOIN {forum} ON {forum_discussions}.forum = {forum}.id
            WHERE {forum_posts}.userid = :userid
            ";
$forums = $DB->get_records_sql($sql, array('userid'=> $USER->id));

echo html_writer::empty_tag('canvas', array('id'=>'forum_chart'));

echo html_writer::start_tag('table', array('class' => 'table table-striped'));
echo html_writer::start_tag('tr');
echo html_writer::start_tag('thread');
echo html_writer::tag('th', get_string('forum_course_name','block_mamiline'));
echo html_writer::tag('th', get_string('forum_name','block_mamiline'));
echo html_writer::tag('th', get_string('forum_subject','block_mamiline'));
echo html_writer::tag('th', get_string('forum_timemodified','block_mamiline'));
echo html_writer::end_tag('thread');
echo html_writer::end_tag('tr');
foreach($forums as $forum){
    $course = common::course($forum->course);
    $js_title = s($forum->subject);
    $js_subject = $forum->message;

    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', html_writer::link(new moodle_url('/mod/forum/view.php', array('id' => $course->id)), $course->fullname), array('class' => 'subscribelink'));
    echo html_writer::tag('td', html_writer::link(new moodle_url('/mod/forum/view.php', array('id' => $forum->id)), $forum->name), array('class' => 'subscribelink'));
    echo html_writer::tag('td', html_writer::link(new moodle_url('/mod/forum/view.php', array('id' => $course->id)), $course->fullname), array('class' => 'subscribelink'));
    echo html_writer::tag('td', $forum->subject, array('class'=>'modal_forum', 'id'=>$forum->id));
    echo html_writer::tag('td', userdate($forum->created));
    echo html_writer::end_tag('tr');

    $js = '$("#' . $forum->id . '").click(function(){
            new Messi("
                ' . $js_subject . '",
                {title :' . $js_title . ',
                 modal : true
                });
            });';

    echo html_writer::script($js);

    $utime = date('m', $forum->created);

    switch ($utime){
        case 1 :
            $forum_graph_data[0]++;
            break;
        case 2 :
            $forum_graph_data[1]++;
            break;
        case 3 :
            $forum_graph_data[2]++;
            break;
        case 4 :
            $forum_graph_data[3]++;
            break;
        case 5 :
            $forum_graph_data[4]++;
            break;
        case 6 :
            $forum_graph_data[5]++;
            break;
        case 7 :
            $forum_graph_data[6]++;
            break;
        case 8 :
            $forum_graph_data[7]++;
            break;
        case 9 :
            $forum_graph_data[8]++;
            break;
        case 10 :
            $forum_graph_data[9]++;
            break;
        case 11 :
            $forum_graph_data[10]++;
            break;
        case 12 :
            $forum_graph_data[11]++;
            break;
    }
}

echo html_writer::end_tag('table');

$js = 'var chartdata53 = {
                "config": {
                    "title": "'. get_string('forum_graph_numofpost_title', 'block_mamiline') .'",
                    "subTitle": "'. get_string('forum_graph_numofpost_subject', 'block_mamiline') .'",
                    "type": "stackedarea",
                    "bg": "#fff",
                    "xColor": "rgba(150,150,150,0.6)",
                    "colorSet":
                        ["rgba(0,150,250,0.5)","rgba(200,0,250,0.4)","rgba(250,250,0,0.3)"],
                    "textColor": "#444",
                    "useMarker": "arc",
                    "useVal": "yes"
                },
                "data": [
                    ["'. get_string('forum_month', 'block_mamiline') . '",1,2,3,4,5,6,7,8,9,10,11,12],
                    ["'. get_string('forum_graph_numofpost_title', 'block_mamiline') . '", ' .$forum_graph_data[0] . ',
                               '.$forum_graph_data[1] . ','
    .$forum_graph_data[2] . ','
    .$forum_graph_data[3] . ','
    .$forum_graph_data[4] . ','
    .$forum_graph_data[5] . ','
    .$forum_graph_data[6] . ','
    .$forum_graph_data[7] . ','
    .$forum_graph_data[8] . ','
    .$forum_graph_data[9] . ','
    .$forum_graph_data[10]. ','
    .$forum_graph_data[11]. ']
                ]
            };
        ccchart.init("forum_chart", chartdata53)
';

echo html_writer::script($js);



echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
