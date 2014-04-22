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

    public static function attempts($quizid)
    {
        global $DB;
        $attempts = $DB->get_records('quiz_attempts', array('quiz' => $quizid, 'state' => 'finished'), 'id', 'id, quiz, userid, attempt');

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

    /**
     * Returns all ungraded items
     *
     * @global moodle_database $DB
     * @return block_alert_item[]
     */
    public static function ungraded_items()
    {
        global $DB;

        $defaultdaysleaving = get_config('block_alert', 'defaultdaysleaving');

        $rs = $DB->get_recordset_sql(
            "SELECT gi.id, gi.courseid, c.fullname AS coursename, gi.itemname,
                    s.timemodified + COALESCE(ao.daysleaving, :defaultdaysleaving) * 24 * 3600 AS timealert,
                    cm.id AS cmid,
                    u.id AS userid, u.institution, u.idnumber, u.firstname, u.lastname, u.email
               FROM {grade_items} gi
               JOIN {modules} md ON md.name = gi.itemmodule
               JOIN {course} c ON c.id = gi.courseid
               JOIN {course_modules} cm ON cm.course = c.id
                                       AND cm.module = md.id
                                       AND cm.instance = gi.iteminstance
               JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel
               JOIN {role_assignments} ra ON ra.contextid = ctx.id
               JOIN {role} r ON r.id = ra.roleid AND r.archetype = :archetype
               JOIN {user} u ON u.id = ra.userid AND u.deleted = 0
               JOIN {assign_submission} s ON s.assignment = cm.instance AND s.userid = u.id
          LEFT JOIN {grade_grades} g ON g.itemid = gi.id AND g.userid = u.id
          LEFT JOIN {block_alert_overrides} ao ON ao.itemid = gi.id
              WHERE gi.itemtype = 'mod' AND gi.itemmodule = 'assign' AND g.finalgrade IS NULL
                AND EXISTS (SELECT 1
                              FROM {context} t_ctx
                              JOIN {role_assignments} t_ra ON t_ra.contextid = t_ctx.id
                              JOIN {role_capabilities} t_rc ON t_rc.roleid = t_ra.roleid
                                                           AND t_rc.capability = :receivercap
                              JOIN {user} t ON t.id = t_ra.userid AND t.deleted = 0
                             WHERE (t_ctx.contextlevel = :systemlevel OR
                                    t_ctx.contextlevel = :courselevel AND t_ctx.instanceid = c.id OR
                                    t_ctx.contextlevel = :modulelevel AND t_ctx.instanceid = cm.id)
                               AND NOT EXISTS (SELECT 1 FROM {block_alert_messages} t_msg
                                               WHERE t_msg.itemid = gi.id AND t_msg.userid = t.id))
             HAVING timealert <= :now
           ORDER BY gi.sortorder ASC, u.institution ASC, u.idnumber ASC",
            ['defaultdaysleaving' => $defaultdaysleaving,
                'contextlevel' => CONTEXT_COURSE, 'archetype' => 'student',
                'systemlevel' => CONTEXT_SYSTEM,
                'courselevel' => CONTEXT_COURSE,
                'modulelevel' => CONTEXT_MODULE,
                'receivercap' => 'block/alert:receivemail',
                'now' => time()]);
        $items = [];
        foreach ($rs as $r) {
            if (!isset($items[$r->id])) {
                $items[$r->id] = (object)[
                    'id' => $r->id,
                    'courseid' => $r->courseid,
                    'coursename' => $r->coursename,
                    'itemname' => $r->itemname,
                    'cmid' => $r->cmid,
                    //'duedate'    => $r->duedate,
                    'timealert' => $r->timealert,
                    //'gradepass'  => $r->gradepass,
                    'students' => [],
                ];
            }
            $items[$r->id]->students[$r->userid] = (object)[
                'id' => $r->userid,
                'institution' => $r->institution,
                'idnumber' => $r->idnumber,
                'firstname' => $r->firstname,
                'lastname' => $r->lastname,
                'email' => $r->email,
                //'mailformat'  => $r->mailformat,
                //'lang'        => $r->lang,
                //'timezone'    => $r->timezone,
                //'finalgrade'  => $r->finalgrade,
            ];
        }
        $rs->close();

        return $items;
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
            ['questionattemptid' => $questionattemptid]
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
}