<?php

namespace mamiline;


class timeline {
    public static function courses($userid)
    {
        $fullcourses = enrol_get_all_users_courses($userid, false, array('id', 'fullname'));

        foreach($fullcourses as $value)
        {
            $courses[$value->id] = $value->fullname;
        }

        return $courses;
    }

    public static function action($log, $userid){
        global $CFG, $DB;
        $basedir = $CFG->wwwroot . '/blocks/mamiline';

        $user = $DB->get_record('user', array('id' => $userid));

        $s_action = str_replace(" ", "_", $log->action);
        $action =  get_string('timeline_' . $s_action, 'block_mamiline');

        if($log->cmid == 0){
            $sql = "SELECT c.id, c.fullname as name FROM {course} as c
                WHERE c.id = :courseid
               ";
            $module = $DB->get_record_sql($sql, array('courseid'=>$log->course));
        }else{
            $sql = "SELECT cm.id, cm.course, cm.module, cm.instance, {course}.fullname, {modules}.name, {" . $log->module . "}.name as name FROM {course_modules} as cm
                JOIN {course} ON {course}.id = cm.course
                JOIN {modules} ON {modules}.id = cm.module
                JOIN {" . $log->module . "} ON {" . $log->module . "}.id = cm.instance
                WHERE cm.id = :cmid
               ";
            $module = $DB->get_record_sql($sql, array('cmid' => $log->cmid));
        }

        $user_url = \html_writer::link(new \moodle_url('/user/profile.php', array('id' => $userid)), fullname($user));

        switch($s_action){
            case 'view' :
                $title = $module->name . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'へアクセスしました。';
                break;
            case 'view_summary' :
                $title = $module->name . 'を' . $action;
                $message =  $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を表示しました。';
                break;
            case 'view_submit_assignment_form' :
                $title = $module->name . 'を' . $action;
                $message =  $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を表示しました。';
                break;
            case 'submit' :
                $title = $module->name . 'を' . $action;
                $message =  $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を提出しました。';
                break;
            case 'close_attempt' :
                $title = $module->name . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を提出しました。';
                break;
            case 'review' :
                $title = $module->name . 'が' . $action . '完了';
                $message =  \html_writer::empty_tag('img', array('src' => $basedir . '/images/hanamaru.png', 'height' => '60px', 'width' => '60px')) . $user_url . 'が' . userdate($log->time) . "に" . $action . 'されました';
                break;
            case 'update' :
                $title = $log->module . 'を' . $action;
                $message = '';
                break;
            case 'new' :
                $title = $log->module . 'を' . $action;
                $message = '';
                break;
            case 'edit' :
                $title = $log->module . 'を' . $action;
                $message = '';
                break;
            case 'login' :
                $title = $action;
                $message = \html_writer::empty_tag('img', array('src' => $basedir . '/images/login.png', 'height' => '60px', 'width' => '60px')) . $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'logout' :
                $title = $action;
                $message = \html_writer::empty_tag('img', array('src' => $basedir . '/images/logout.png', 'height' => '60px', 'width' => '60px')) . $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'add' :
                $title = $log->module . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'add_mod' :
                $title = $log->module . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'pre-view' :
                $title = $log->module . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'report' :
                $title = $log->module . 'を' . $action;
                $message =  $user_url . 'が' . $log->module . 'を表示しました。';
                break;
            case 'report_log' :
                $title = $log->module . 'を' . $action;
                $message =  $user_url . 'が' . $log->module . 'を表示しました。';
                break;
            default :
                $title = $module->name . 'を' . $action;
                $message = $module->name . '';
                break;
        }
        $action = array('action' => get_string('timeline_' . $s_action, 'block_mamiline'), 'title'=>$title ,'message' => $message);

        return $action;
    }

} 