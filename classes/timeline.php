<?php

namespace mamiline;

class timeline {

    private static $_cache = array();

    public static function logs($courseid){
        global $DB;

        if (isset(self::$_cache['logs'])){
            return self::$_cache['logs'];
        }

        $sql = "SELECT l.id, l.course, l.module, l.action, l.url, c.fullname, l.cmid, l.time, l.userid FROM {log} as l
        JOIN {course} as c ON c.id = l.course
        WHERE l.course = :courseid
        ORDER BY l.time DESC";
        $logs = $DB->get_records_sql($sql, array('courseid' => $courseid), 0, 200);

        return self::$_cache['logs'] = $logs;
    }

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
        global $DB;
        $user = $DB->get_record('user', array('id' => $userid));
        $s_action = str_replace(" ", "_", $log->action);

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
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $module->name . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'へアクセスしました。';
                break;
            case 'view_summary' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $module->name . 'を' . $action;
                $message =  $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を表示しました。';
                break;
            case 'view_submit_assignment_form' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $module->name . 'を' . $action;
                $message =  $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を表示しました。';
                break;
            case 'submit' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $module->name . 'を' . $action;
                $message =  $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を提出しました。';
                break;
            case 'close_attempt' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $module->name . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を提出しました。';
                break;
            case 'review' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $module->name . 'が' . $action . '完了';
                $message =  \html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/mamiline/images/hanamaru.png'), 'height' => '60px', 'width' => '60px')) . $user_url . 'が' . userdate($log->time) . "に" . $action . 'されました';
                break;
            case 'update' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $log->module . 'を' . $action;
                $message = '';
                break;
            case 'new' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $log->module . 'を' . $action;
                $message = '';
                break;
            case 'edit' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $log->module . 'を' . $action;
                $message = '';
                break;
            case 'login' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $action;
                $message = \html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/mamiline/images/login.png'), 'height' => '60px', 'width' => '60px')) . $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'logout' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $action;
                $message = \html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/mamiline/images/logout.png'), 'height' => '60px', 'width' => '60px')) . $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'add' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $log->module . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'add_mod' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $log->module . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'pre-view' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $log->module . 'を' . $action;
                $message = $user_url . 'が' . userdate($log->time) . $action . 'しました';
                break;
            case 'report' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $log->module . 'を' . $action;
                $message =  $user_url . 'が' . $log->module . 'を表示しました。';
                break;
            case 'report_log' :
                $action =  get_string('timeline_' . $s_action, 'block_mamiline');
                $title = $log->module . 'を' . $action;
                $message =  $user_url . 'が' . $log->module . 'を表示しました。';
                break;
            default :
                $title = $module->name . 'を' . get_string('timeline_view', 'block_mamiline');
                $message = $module->name . '';

                return array('action' => get_string('timeline_view', 'block_mamiline'), 'title'=>$title, 'message' => $message);
        }
        $result = array('action' => get_string('timeline_' . $s_action, 'block_mamiline'), 'title'=>$title, 'message' => $message);
        return $result;
    }

} 