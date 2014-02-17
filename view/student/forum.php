<?php

require_once __DIR__ . '/../../../../config.php';
require_once(dirname(__FILE__) . '/../../locallib.php');

/* @var $DB moodle_database */
/* @var $CFG object */
/* @var $USER object */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$basedir = $CFG->wwwroot . '/blocks/mamiline';
$userid = $USER->id;
require_login();

$forumid = optional_param('page', 1, PARAM_INT);

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

echo html_writer::start_tag('div', array('class' => 'container'));
echo html_writer::start_tag('div', array('class' => 'row'));
echo html_writer::start_tag('div', array('class' => 'col-md-12 well'));
echo html_writer::tag('h3', get_string('timeline', 'block_mamiline'));

$limit = array(PAGENUM * $page, PAGENUM * $page - PAGENUM);
$sql = "SELECT l.id, l.course, l.module, l.action, l.url, c.fullname, l.cmid, l.time FROM {log} as l
        JOIN {course} as c ON c.id = l.course
        WHERE l.userid = :userid
        ORDER BY l.time DESC";
$logs = $DB->get_records_sql($sql, array('userid' => $USER->id), $limit[1], $limit[0]);

echo  '<div class="row">'
    . '<div class="col-md-9 well">'
    . '<legend>'.get_string('timeline', 'block_mamiline').'</legend>';

foreach($logs as $log){
    $year = userdate($log->time, '%Y/%m');
    $day  = userdate($log->time, '%Y/%m');

    $action = mamiline_get_action($log);
    echo  '<div class="col-md-12 well">'
        . '<h3>' . $action['title'] . '</h3>'
        . '<blockquote>' . $action['message'] . '</blockquote>'
        . '</div>'
    ;
}

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
