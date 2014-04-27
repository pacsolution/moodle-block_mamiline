<?php

require_once __DIR__ . '/../../../../../config.php';
require_once __DIR__ . '/../../../../../lib/gradelib.php';
require_once __DIR__ . '/../../../../../grade/querylib.php';
require_once __DIR__ . '/../../../classes/common.php';
require_once __DIR__ . '/../../../classes/timeline.php';

use mamiline\common;
use mamiline\timeline;

require_login();

$courseid = required_param('courseid', PARAM_INT);
//タイムラインを30件ごとに分ける
define('PAGENUM', 500);
$pagenum = optional_param('page', 1, PARAM_INT);

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
    html_writer::link('/blocks/mamiline/view/student/timeline/', get_string('timeline', 'block_mamiline')),
    array('class' => 'list-group-item active')
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

echo html_writer::start_tag('div', array('id' => 'page-wrapper'));
echo html_writer::start_tag('div', array('class' => 'row'));

//指定したコース内のタイムライン
$logs = mamiline\timeline::logs($courseid);

echo html_writer::start_tag('ul', array('id' => 'timeline'));
echo html_writer::start_tag('div', array('class' => 'page-header'));
echo html_writer::tag('h1', common::course($courseid)->fullname, array('id' => 'timeline'));
echo html_writer::end_tag('div');
echo html_writer::start_tag('ul', array('class' => 'timeline'));

$isteacher = 1;
foreach ($logs as $log) {
    $context = context_course::instance($courseid);
    $roles = get_user_roles($context, $log->userid);

    foreach ($roles as $role) {
        if ($role->roleid != 5) {
            $isteacher = 1;
            break;
        } else {
            $isteacher = 0;
            break;
        }
    }
    if ($isteacher || $roles == null) {
        continue;
    }
    $action = timeline::action($log, $log->userid);
    $t_user = mamiline\common::user($log->userid);

    echo html_writer::start_tag('li');
    echo html_writer::start_tag('div', array('class' => 'timeline-badge'));
    echo html_writer::tag('div', $OUTPUT->user_picture($t_user, array('size' => 50, 'class' => 'img-circle')), array('class' => 'timeline-badge'));
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('class' => 'timeline-panel'));
    echo html_writer::start_tag('div', array('class' => 'timeline-heading'));
    echo html_writer::tag('h4', $action['title'], array('class' => 'timeline-panel'));
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


//Script
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/jquery.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/prefixfree.min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/raphael-min.js'));
echo html_writer::script(null, new moodle_url('/blocks/mamiline/js/morris-0.4.3.min.js'));

echo html_writer::end_tag('body');
echo html_writer::end_tag('html');