<?php

namespace mamiline;

/**
 *
 * @package    block_mamiline
 * @copyright  2013 VERSION2 Inc.
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * */

defined('MOODLE_INTERNAL') || die();


/**
 * Returns finalgrade of the assign
 *
 * @global moodle_database $DB
 * @param cm_info $cm
 * @param int $userid
 * @return stdClass
 */
function mamiline_get_assign_grade($cm, $userid)
{
    global $DB;

    return $DB->get_record_sql(
        "SELECT g.id, g.finalgrade, gi.gradepass, gi.grademax,
                (SELECT COUNT(1) FROM {block_activity_portal_log} l
                 WHERE l.component = 'mod_assign' AND l.action = 'downloadsubmission'
                   AND l.parami = a.id AND l.userid <> a.userid) AS downloadedcount
           FROM {assign_submission} a
           JOIN {grade_items} gi ON gi.itemtype = 'mod' AND gi.itemmodule = 'assign' AND gi.iteminstance = a.assignment
      LEFT JOIN {grade_grades} g ON g.itemid = gi.id AND g.userid = a.userid
          WHERE a.assignment = :assignid AND a.userid = :userid AND a.status = :status",
        array('assignid' => $cm->instance, 'userid' => $userid, 'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED));
}

/**
 * Returns finalgrade and duration of the quiz
 *
 * @global moodle_database $DB
 * @param cm_info $cm
 * @param int $userid
 * @return stdClass
 */
function mamiline_get_quiz_grade($cm, $userid)
{
    global $DB;

    $quiz = $DB->get_record('quiz', array('id' => $cm->instance));
    $filter = quiz_report_qm_filter_select($quiz, 'qa') ?: 'TRUE';
    return $DB->get_record_sql(
        "SELECT g.id, g.finalgrade, gi.gradepass, gi.grademax,
                CASE WHEN qa.timefinish = 0 THEN NULL
                     WHEN qa.timefinish > qa.timestart THEN qa.timefinish - qa.timestart
                     ELSE 0 END AS duration
           FROM {quiz_attempts} qa
           JOIN {grade_items} gi ON gi.itemtype = 'mod' AND gi.itemmodule = 'quiz' AND gi.iteminstance = qa.quiz
           JOIN {grade_grades} g ON g.itemid = gi.id AND g.userid = qa.userid
          WHERE qa.quiz = :quizid AND qa.userid = :userid AND $filter",
        array ('quizid' => $quiz->id, 'userid' => $userid ));
}

function mamiline_get_quiz_grades($quizid, $userid){
    global $DB;

    $sql = "SELECT qa.id, qa.quiz, qa.state, qa.timestart, qa.timefinish, q.grade, q.sumgrades as q_sumgrades, q.course, qa.sumgrades as qa_sumgrades, q.id as qid, q.sumgrades as q_sumgrades
            FROM {quiz_attempts} as qa
            JOIN {quiz} as q ON qa.quiz = q.id
            JOIN {quiz_grades} ON qa.quiz = {quiz_grades}.quiz
            WHERE qa.quiz = :quizid && qa.userid = :userid";
    return $DB->get_records_sql($sql, array('quizid'=> $quizid, 'userid' => $userid));
}

/**
 * Returns finalgrade and duration of the quiz
 *
 * @global moodle_database $DB
 * @param int[] $cmids
 * @param int $userid
 * @return float[]
 */
function mamiline_get_overdues(array $cmids, $userid)
{
    global $DB;

    static $assignmoduleid = null;
    if ($assignmoduleid === null) {
        $assignmoduleid = $DB->get_field('modules', 'id', array('name' => 'assign'));
    }
    list ($cmin, $cmparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED, 'cmid');
    return $DB->get_records_sql_menu(
        "SELECT cm.id, s.timemodified - GREATEST(COALESCE(g.extensionduedate, 0), m.duedate) AS overdue
           FROM {course_modules} cm
           JOIN {assign} m ON m.id = cm.instance
           JOIN {assign_submission} s ON s.assignment = m.id AND s.userid = :userid
      LEFT JOIN {assign_grades} g ON g.assignment = m.id AND g.userid = s.userid
          WHERE cm.module = :moduleid AND cm.id $cmin AND s.status = :submitted
            AND s.timemodified > GREATEST(COALESCE(g.extensionduedate, 0), m.duedate)",
        array('userid' => $userid,
          'moduleid' => $assignmoduleid,
          'submitted' => ASSIGN_SUBMISSION_STATUS_SUBMITTED) + $cmparams);
}