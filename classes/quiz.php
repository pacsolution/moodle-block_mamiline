<?php

namespace mamiline;

class quiz {
    public static function quizzes($courseid)
    {
        global $DB;
        $quizes = $DB->get_records('quiz', array('course' => $courseid));

        return $quizes;
    }

    public static function quiz($quizid)
    {
        global $DB;
        $quiz = $DB->get_record('quiz', array('id' => $quizid));

        return $quiz;
    }

    public static function attempts($quizid)
    {
        global $DB;
        $attempts = $DB->get_records('quiz_attempts', array('quiz' => $quizid, 'state' => 'finished'), 'id', 'id, quiz, userid, attempt');

        return $attempts;
    }

    public static function count_attemts($quizid)
    {
        global $DB;
        $count_attempts = $DB->count_records('quiz_attempts', array('quiz' => $quizid, 'state' => 'finished'), 'id', 'id, quiz, userid, attempt, rawgrade');

        return $count_attempts;
    }

    public static function count_students($quiz)
    {
        global $DB;
        $attempts = $DB->count_records('quiz_attempts', array('quiz' => $quiz->id, 'state' => 'finished'), 'id', 'id, quiz, userid, attempt');

        quiz_get_user_grades($quiz);

        return $attempts;
    }

    public static function grades($quiz)
    {
        $grades = grade_get_grades($quiz->course, 'mod', 'quiz', $quiz->id, 3);

        return $grades;
    }

    public static function average($quiz)
    {
        global $DB;

        $average = $DB->get_field('quiz_grades', 'AVG(grade)',  array('quiz' => $quiz->id));

        return $average;
    }
}