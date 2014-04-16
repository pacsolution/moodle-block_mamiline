<?php

namespace mamiline;

require_once(dirname(__FILE__) . '/../classes/quiz.php');
require_once(dirname(__FILE__) . '/../classes/grade.php');


class grade {
    public static function usergrade($quiz, $userid)
    {
        $grades = quiz_get_user_grades($quiz, $userid);
        $grade = array_shift($grades);

        return $grade;
    }

    public static function coursegrade($userid, $courseid)
    {
        $grade = grade_get_course_grade($userid, $courseid);

        return $grade;
    }
}