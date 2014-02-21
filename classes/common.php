<?php

namespace mamiline;

class common {
    public static function courses($userid)
    {
        $fullcourses = enrol_get_all_users_courses($userid, false, array('id', 'fullname'));

        foreach($fullcourses as $value)
        {
            $courses[$value->id] = $value->fullname;
        }

        return $courses;
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
} 