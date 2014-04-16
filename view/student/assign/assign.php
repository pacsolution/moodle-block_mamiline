<?php

require_once __DIR__ . '/../../../../../config.php';
require_once(dirname(__FILE__) . '/../../../locallib.php');
require_once(dirname(__FILE__) . '/../../../classes/assign.php');
require_once __DIR__ . '/../../../../../mod/assign/lib.php';

require_login();

$id = required_param('id', PARAM_INT);

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

echo html_writer::start_tag('body');

//上部メニューここから
echo html_writer::start_tag('nav', array('class' => 'navbar navbar-default', 'role' => 'navigation'));
echo html_writer::start_tag('div', array('class' => 'navbar-header'));
echo html_writer::tag('a', get_string('pluginname', 'block_mamiline') . '/' . get_string('assign', 'block_mamiline'), array('href' => new moodle_url('/blocks/mamiline/index.php'), 'class' => 'navbar-brand'));
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('class' => 'collapse navbar-collapse', 'id' => 'bs-example-navbar-collapse-1'));
echo html_writer::start_tag('ul', array('class' => 'nav navbar-nav navbar-right'));
echo html_writer::tag('li', html_writer::link(new moodle_url('/blocks/mamiline/index.php'), get_string('top', 'block_mamiline')));
echo html_writer::tag('li', html_writer::link(new moodle_url('/blocks/mamiline/index.php'), get_string('close', 'block_mamiline')));
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::end_tag('nav');
//上部メニューここまで

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
    html_writer::link('/blocks/mamiline/', get_string('top', 'block_mamiline')),
    array('class' => 'list-group-item')
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
    array('class' => 'list-group-item active')
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

$assign_submission = \mamiline\assign::submission($id, $USER->id);
$cm = get_coursemodule_from_instance('assign', $assign_submission->id);
$context_module = context_module::instance($cm->id);

echo html_writer::start_tag('div', array('class' => 'col-md-9 well'));
echo html_writer::tag('h3', get_string('assign_viewdetail', 'block_mamiline'));
echo html_writer::start_tag('table', array('class' => 'table table-striped'));
echo html_writer::start_tag('tr');
echo html_writer::tag('td', get_string('assign_name', 'block_mamiline'));
echo html_writer::tag('td', $assign_submission->name);
echo html_writer::end_tag('tr');
echo html_writer::start_tag('tr');
echo html_writer::tag('td', get_string('assign_timemodified', 'block_mamiline'));
echo html_writer::tag('td', userdate($assign_submission->timemodified));
echo html_writer::end_tag('tr');
echo html_writer::start_tag('tr');
echo html_writer::tag('td', get_string('assign_duedate', 'block_mamiline'));
echo html_writer::tag('td', userdate($assign_submission->duedate));
echo html_writer::end_tag('tr');
echo html_writer::start_tag('tr');
echo html_writer::tag('td', get_string('assign_feedback', 'block_mamiline'));
echo html_writer::tag('td', $assign_submission->commenttext);
echo html_writer::end_tag('tr');
echo html_writer::start_tag('tr');
echo html_writer::tag('td', get_string('assign_download', 'block_mamiline'));
echo html_writer::tag('td', $assign_submission->commenttext);
echo html_writer::end_tag('tr');
echo html_writer::end_tag('table');
echo html_writer::end_tag('div');

//提出ファイルの表示
echo html_writer::start_tag('div', array('class' => 'col-md-8'));
echo html_writer::tag('h3', get_string('assign_viewfile', 'block_mamiline'));
$files = \mamiline\assign::files($context_module->id, $USER->id);
foreach ($files as $file) {
    if ($file->filesize != 0) {
        echo html_writer::tag('h4', $file->filename);
        echo html_writer::start_tag('table', array('class' => 'table'));
        echo html_writer::start_tag('tr');

        echo html_writer::tag('td', get_string('assign_filename', 'block_mamiline'));
        echo html_writer::tag('td', $file->filename);
        echo html_writer::end_tag('tr');
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', get_string('assign_filedescription', 'block_mamiline'));

        if (strstr($file->mimetype, 'image')) {
            $url = moodle_url::make_pluginfile_url($file->contextid, $file->component, $file->filearea, $file->itemid, $file->filepath, $file->filename);
            echo html_writer::tag('td', html_writer::empty_tag('img', array('src' => $url, 'class' => 'img-thumbnail', 'width' => '140', 'height' => '140')));
            echo html_writer::end_tag('tr');
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', html_writer::link($url, get_string('assign_downloadfile', 'block_mamiline'), array('class' => 'btn btn-success')));
            echo html_writer::end_tag('tr');
        } else {
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

//オンラインテキスト
echo html_writer::start_tag('div', array('class' => 'col-md-9'));
echo html_writer::tag('h3', get_string('assign_viewonlinetext', 'block_mamiline'));
echo html_writer::tag('div', $assign_submission->onlinetext);
echo html_writer::end_tag('div');

//Script
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/jquery.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/prefixfree.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/raphael-min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/morris-0.4.3.min.js'));

echo html_writer::end_tag('body');
echo html_writer::end_tag('html');