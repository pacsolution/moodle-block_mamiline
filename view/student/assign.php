<?php

require_once __DIR__ . '/../../../../config.php';
require_once(dirname(__FILE__) . '/../../locallib.php');
require_once(dirname(__FILE__) . '/../../classes/assign.php');
require_once __DIR__ . '/../../../../mod/assign/lib.php';

/* @var $DB moodle_database */
/* @var $CFG object */
/* @var $USER object */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

use mamiline\assign;

$basedir = $CFG->wwwroot . '/blocks/mamiline';

require_login();

$context = context::instance_by_id(1);
$PAGE->set_context($context);

$id = optional_param('id', 0, PARAM_INT);

echo html_writer::start_tag('html', array('lang' => 'ja'));
echo html_writer::start_tag('head');
echo html_writer::empty_tag('meta', array('charset' => 'UTF-8'));
echo html_writer::empty_tag('meta', array('http-equiv' => 'content-language', 'content' => 'ja'));
echo html_writer::empty_tag('meta', array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::tag('title', get_string('pluginname', 'block_mamiline'), array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
echo html_writer::script(null, $basedir . '/js/jquery.min.js');
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/bootstrap.min.css', 'rel' => 'stylesheet'));
echo html_writer::empty_tag('link', array('href' => $basedir . '/css/bootstrap-theme.min.css', 'rel' => 'stylesheet'));
echo html_writer::end_tag('head');

echo html_writer::start_tag('body');

echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('assign', 'block_mamiline'), array('href' => new moodle_url($basedir . '/index.php'), 'class' => 'navbar-brand'));
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

echo html_writer::start_tag('div', array('class' => 'container'));
echo html_writer::start_tag('div', array('class' => 'row'));

if($id){
    $sql = "SELECT a.id, asub.userid, asub.timemodified, asub.status, a.name, a.course, a.grade, a.duedate, co.fullname, asf.numfiles, ast.onlinetext, f.commenttext FROM {assign_submission} as asub
            JOIN {assign} as a ON asub.assignment = a.id
            JOIN {course} as co ON a.course = co.id
            LEFT OUTER JOIN {assignsubmission_file} as asf ON asf.assignment = a.id
            LEFT OUTER JOIN {assignsubmission_onlinetext} as ast ON ast.assignment = a.id
            LEFT OUTER JOIN {assignfeedback_comments} as f ON f.assignment = a.id
            WHERE asub.userid = :userid AND asub.id = :id AND asub.status = 'submitted'";

    $assign_submission = $DB->get_record_sql($sql, array('userid' => $USER->id, 'id' => $id));
    $cm = get_coursemodule_from_instance('assign', $assign_submission->id);
    $course = \mamiline\get_course($cm->course);
    $context_module = context_module::instance($cm->id);

    echo html_writer::start_tag('div', array('class' => 'col-md-9 well'));
    echo html_writer::tag('h3', get_string('assign_viewdetail', 'block_mamiline'));
    echo html_writer::start_tag('table' , array('class' => 'table table-striped'));
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', get_string('assign_name','block_mamiline'));
    echo html_writer::tag('td', $assign_submission->name);
    echo html_writer::end_tag('tr');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', get_string('assign_timemodified','block_mamiline'));
    echo html_writer::tag('td', userdate($assign_submission->timemodified));
    echo html_writer::end_tag('tr');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', get_string('assign_duedate','block_mamiline'));
    echo html_writer::tag('td', userdate($assign_submission->duedate));
    echo html_writer::end_tag('tr');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', get_string('assign_feedback','block_mamiline'));
    echo html_writer::tag('td', $assign_submission->commenttext);
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('table');
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('class' => 'col-md-9 well'));
    echo html_writer::tag('h3', get_string('assign_viewfile', 'block_mamiline'));
    $files = assign::files($context_module->id);
    foreach($files as $file){
        if($file->filesize != 0){
            echo html_writer::tag('h4', $file->filename);
            echo html_writer::start_tag('table' , array('class' => 'table'));
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', get_string('assign_filename','block_mamiline'));
            echo html_writer::tag('td', $file->filename);
            echo html_writer::end_tag('tr');
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', get_string('assign_filedescription','block_mamiline'));
            if(strstr($file->mimetype, 'image')){
                $url = moodle_url::make_pluginfile_url($file->contextid, $file->component, $file->filearea, $file->itemid, $file->filepath, $file->filename);
                echo html_writer::tag('td', html_writer::empty_tag('img', array('src' => $url, 'class' => 'img-thumbnail', 'width' => '140', 'height' => '140')));
                echo html_writer::end_tag('tr');
                echo html_writer::start_tag('tr');
                echo html_writer::tag('td', html_writer::link($url, get_string('assign_downloadfile', 'block_mamiline'), array('class' => 'btn btn-success')));
                echo html_writer::end_tag('tr');
            }else{
                $url = moodle_url::make_pluginfile_url($file->contextid, $file->component, $file->filearea, $file->itemid, $file->filepath, $file->filename);
                $image = new moodle_url('../../images/file.png');
                echo html_writer::tag('td', html_writer::empty_tag('img', array('src' => $image, 'class' => 'img-thumbnail', 'width' => '140', 'height' => '140')));
                echo html_writer::end_tag('tr');
                echo html_writer::start_tag('tr');
                echo html_writer::tag('td', html_writer::link($url, get_string('assign_downloadfile', 'block_mamiline'), array('class' => 'btn btn-success')));
                echo html_writer::end_tag('tr');
            }
            echo html_writer::end_tag('table');
        }
    }
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('class' => 'col-md-10 well'));
    echo html_writer::tag('h3', get_string('assign_viewonlinetext', 'block_mamiline'));
    echo html_writer::tag('div', $assign_submission->onlinetext);
    echo html_writer::end_tag('div');
}else{
    echo html_writer::start_tag('div', array('class' => 'row'));
    echo html_writer::start_tag('div', array('class' => 'col-md-9 well'));

    $sql = "SELECT asub.id, asub.userid, asub.timemodified, asub.status, a.name, a.course, a.grade, a.duedate, co.fullname FROM {assign_submission} as asub
            JOIN {assign} as a ON asub.assignment = a.id
            JOIN {course} as co ON a.course = co.id
            WHERE asub.userid = :userid";
    $assign_submission = $DB->get_records_sql($sql, array('userid' => $USER->id));

    echo html_writer::start_tag('table' , array('class' => 'table table-striped'));
    echo html_writer::start_tag('thread');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('assign_name','block_mamiline'));
    echo html_writer::tag('th', get_string('assign_course','block_mamiline'));
    echo html_writer::tag('th', get_string('assign_viewassign','block_mamiline'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thread');

    foreach($assign_submission as $assigns){
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', $assigns->name);
        echo html_writer::tag('td', html_writer::link(new moodle_url('/course/view.php', array('id' => $assigns->course)), $assigns->fullname));
        echo html_writer::tag('td', html_writer::link(new moodle_url('assign.php', array('id' => $assigns->id )), get_string('assign_viewassign','block_mamiline'), array('class' => 'btn btn-success')));
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('table');
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');