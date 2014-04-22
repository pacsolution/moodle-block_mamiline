<?php

namespace mamiline;

class role {
    public static function is_moderator($user = null, $courseid = null)
    {
        global $USER, $COURSE;
        return \has_capability('moodle/course:manageactivities',
            \context_course::instance($courseid ?: $COURSE->id), $user ?: $USER);
    }

    public static function is_moderator_courses($user = null, $courses)
    {
        foreach($courses as $course)
        {
            if($role = role::is_moderator($user, $course->id))
            {
                return $role;
            }
        }
        return false;
    }
} 