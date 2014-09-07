<?php

namespace mamiline;

class quiz
{
    public static function quiz($quizid)
    {
        global $DB;
        $quiz = $DB->get_record('quiz', array('id' => $quizid));

        return $quiz;
    }

    public static function quizzes($courseid)
    {
        global $DB;
        $quizzes = $DB->get_records('quiz', array('course' => $courseid));

        return $quizzes;
    }

    public static function count_quizzes($courseid){
        global $DB;
        $quizzes = $DB->count_records('quiz', array('course' => $courseid));

        return $quizzes;
    }

    public static function attempts($userid, $quizid)
    {
        global $DB;
        $attempts = $DB->get_records('quiz_attempts', array('quiz' => $quizid, 'state' => 'finished', 'userid' => $userid), 'id', 'id, quiz, userid, attempt');

        return $attempts;
    }

    public static function recent_finished($userid)
    {
        global $DB;
        $sql = "SELECT DISTINCT qa.id,
                       q.id as qid,
                       q.name,
                       q.course,
                       qa.timestart,
                       qa.timefinish,
                       qa.state,
                       q.grade,
                       q.course,
                       q.sumgrades as q_sumgrades,
                       qa.sumgrades as qa_sumgrades,
                       qa.userid
                FROM {quiz_attempts} as qa
                JOIN {user} as u ON u.id = qa.userid
                JOIN {quiz} as q ON qa.quiz = q.id
                WHERE qa.userid = :userid && qa.preview = 0 && qa.state = 'finished'
                GROUP BY qid
                ORDER BY qa.timefinish DESC
                LIMIT 0, 10";

        $result = $DB->get_records_sql($sql, array('userid' => $userid));

        return $result;

    }

    public static function count_finished($userid)
    {
        global $DB;
        $attempts = $DB->count_records('quiz_attempts', array('userid' => $userid, 'state' => 'finished'), 'id', 'id, quiz, userid, attempt');

        return $attempts;
    }

    public static function finished_attenpts($userid, $courseid)
    {
        global $DB;
        $sql = "SELECT DISTINCT qa.id,
                       q.id as qid,
                       q.name,
                       q.course,
                       qa.timestart,
                       qa.timefinish,
                       qa.state,
                       q.grade,
                       q.course,
                       q.sumgrades as q_sumgrades,
                       qa.sumgrades as qa_sumgrades,
                       qa.userid
                FROM {quiz_attempts} as qa
                JOIN {user} as u ON u.id = qa.userid
                JOIN {quiz} as q ON qa.quiz = q.id
                WHERE qa.userid = :userid && qa.preview = 0 && q.course = :courseid
                GROUP BY qid
                ORDER BY qa.timefinish DESC";

        $result = $DB->get_records_sql($sql, array('userid' => $userid, 'courseid' => $courseid));

        return $result;
    }

    public static function finished_unattenpts($userid, $courseid)
    {
        global $DB;
        $sql = "SELECT DISTINCT qa.id,
                       q.id as qid,
                       q.name,
                       q.course,
                       qa.timestart,
                       qa.timefinish,
                       qa.state,
                       q.grade,
                       q.course,
                       q.sumgrades as q_sumgrades,
                       qa.sumgrades as qa_sumgrades,
                       qa.userid
                FROM {quiz_attempts} as qa
                JOIN {user} as u ON u.id = qa.userid
                JOIN {quiz} as q ON qa.quiz = q.id
                WHERE qa.userid = :userid && qa.preview = 0 && q.course = :courseid && qa.state != 'finished'
                GROUP BY qid
                ORDER BY qa.timefinish DESC";

        $result = $DB->get_records_sql($sql, array('userid' => $userid, 'courseid' => $courseid));

        return $result;
    }

    public static function count_finished_attempts($userid, $courseid){
        global $DB;
        $sql = "SELECT COUNT(*)
                FROM {quiz_attempts} as qa
                JOIN {user} as u ON u.id = qa.userid
                JOIN {quiz} as q ON qa.quiz = q.id
                WHERE qa.userid = :userid && qa.preview = 0 && q.course = :courseid
                GROUP BY q.id";

        $attempts = $DB->count_records_sql($sql, array('userid' => $userid, 'courseid' => $courseid, 'state' => 'finished'));

        return $attempts;
    }

    public static function count_attemts($userid, $quizid)
    {
        global $DB;
        $count_attempts = $DB->count_records('quiz_attempts', array('quiz' => $quizid, 'state' => 'finished', 'userid' => $userid), 'id', 'id, quiz, userid, attempt, rawgrade');

        return $count_attempts;
    }

    public static function count_students($quiz)
    {
        global $DB;
        $attempts = $DB->count_records('quiz_attempts', array('quiz' => $quiz->id, 'state' => 'finished'), 'id', 'id, quiz, userid, attempt');

        quiz_get_user_grades($quiz);

        return $attempts;
    }

    public static function grades($quiz, $userid = null)
    {
        $grades = grade_get_grades($quiz->course, 'mod', 'quiz', $quiz->id, $userid);

        return $grades;
    }

    public static function average($quiz)
    {
        global $DB;

        $average = $DB->get_field('quiz_grades', 'AVG(grade)', array('quiz' => $quiz->id));

        return $average;
    }

    public static function get_uploaded_file($questionattemptid)
    {
        global $DB;

        $steps = $DB->get_records('question_attempt_steps',
            array('questionattemptid' => $questionattemptid), 'sequencenumber DESC', 'id');

        $contextid = $DB->get_field_sql('
        		SELECT qu.contextid
        		FROM {question_usages} qu
        	    JOIN {question_attempts} qa ON qu.id = qa.questionusageid
        		WHERE qa.id = :questionattemptid
        		',
            array('questionattemptid' => $questionattemptid)
        );
        if (!$contextid) {
            return null;
        }

        $fs = get_file_storage();

        foreach ($steps as $step) {
            if ($files = $fs->get_area_files($contextid, 'question', 'response_answer', $step->id,
                'itemid, filepath, filename', false)
            ) {
                return reset($files);
            }
        }
        return null;
    }

    public static function unfinish($userid, $courseid){
        $count_quizzes = quiz::count_quizzes($courseid);
        $count_attemts = quiz::count_finished_attempts($userid, $courseid);

        return $count_quizzes - $count_attemts;
    }
}