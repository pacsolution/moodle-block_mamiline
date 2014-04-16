<?php

namespace mamiline;

class common {
    public static function user($userid){
        global $DB;

        return $DB->get_record('user', array('id' => $userid));
    }

    public static function course($courseid, $clone = true) {
        global $DB, $COURSE, $SITE;
        if (!empty($COURSE->id) && $COURSE->id == $courseid) {
            return $clone ? clone($COURSE) : $COURSE;
        } else if (!empty($SITE->id) && $SITE->id == $courseid) {
            return $clone ? clone($SITE) : $SITE;
        } else {
            return $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        }
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

    public static function logins($userid)
    {
        global $DB;
        $date = strtotime(date("Y/m/d"));
        $sql = "SELECT id,time FROM {log} WHERE {log}.action = 'login' AND {log}.time > :time  AND {log}.userid = :userid";
        $logs = $DB->get_records_sql($sql, array('userid' => $userid, 'time' => time() - 604800));
        $logins = array_fill(0, 7, 0);
        foreach($logs as $log){
            if($log->time > $date){
                $logins[6] += 1;
                continue;
            }elseif($log->time > $date - 86400*1){
                $logins[5] += 1;
                continue;
            }elseif($log->time > ($date - 86400*2)){
                $logins[4] += 1;
                continue;
            }elseif($log->time > ($date - 86400*3)){
                $logins[3] += 1;
                continue;
            }elseif($log->time > ($date - 86400*4)){
                $logins[2] += 1;
                continue;
            }elseif($log->time > ($date - 86400*5)){
                $logins[1] += 1;
                continue;
            }elseif($log->time > ($date - 86400*6)){
                $logins[0] += 1;
                continue;
            }
        }
        return $logins;
    }
}