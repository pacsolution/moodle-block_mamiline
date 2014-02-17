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
} 