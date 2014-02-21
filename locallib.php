<?php

namespace mamiline;

/**
 *
 * @package    block_mamiline
 * @copyright  2013 VERSION2 Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Gets a course object from database. If the course id corresponds to an
 * already-loaded $COURSE or $SITE object, then the loaded object will be used,
 * saving a database query.
 *
 * If it reuses an existing object, by default the object will be cloned. This
 * means you can modify the object safely without affecting other code.
 *
 * @param int $courseid Course id
 * @param bool $clone If true (default), makes a clone of the record
 * @return stdClass A course object
 * @throws dml_exception If not found in database
 */
function get_course($courseid, $clone = true) {
    global $DB, $COURSE, $SITE;
    if (!empty($COURSE->id) && $COURSE->id == $courseid) {
        return $clone ? clone($COURSE) : $COURSE;
    } else if (!empty($SITE->id) && $SITE->id == $courseid) {
        return $clone ? clone($SITE) : $SITE;
    } else {
        return $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    }
}

function mamiline_print_forum_posts(){
    global $DB, $USER, $CFG;
    $forum_graph_data = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

    $sql = "SELECT {forum_posts}.id, {forum}.name, {forum}.course, {forum_discussions}.name, {forum_posts}.created, {forum_posts}.subject, {forum_posts}.message, {forum}.name FROM {forum_posts}
            JOIN {forum_discussions} ON {forum_posts}.discussion = {forum_discussions}.id
            JOIN {forum} ON {forum_discussions}.forum = {forum}.id
            WHERE {forum_posts}.userid = $USER->id
            ";
    $forums = $DB->get_records_sql($sql);

    echo '<h3>'.get_string('forum','block_mamiline').'</h3>';

    echo '<canvas id="forum_chart"></canvas>';

    echo '<table class="table table-striped">';
    echo '<tr><thread>';
    echo '<th>'.get_string('forum_course_name','block_mamiline').'</th>';
    echo '<th>'.get_string('forum_name','block_mamiline').'</th>';
    echo '<th>'.get_string('forum_subject','block_mamiline').'</th>';
    echo '<th>'.get_string('forum_timemodified','block_mamiline').'</th>';
    echo '</thread></tr>';

    foreach($forums as $forum){
        $course = get_course($forum->course);
        $js_title = s($forum->subject);
        $js_subject = $forum->message;

        echo "<tr>";
        echo "<td><a href='$CFG->wwwroot/mod/forum/view.php?id=$course->id'>$course->fullname</a></td>";
        echo "<td><a href='$CFG->wwwroot/mod/forum/view.php?id=$forum->id'>$forum->name</a></td>";
        echo "<td class='modal_forum' id='$forum->id'><a>" . $forum->subject."</a></td>";
        echo "<td>" . userdate($forum->created)."</td>";
        echo "</tr>";

        echo '
            <script>
                $("#' . $forum->id . '").click(function(){
                    new Messi("
                        ' . $js_subject . '",
                            {title :' . $js_title . ',
                            　modal : true
                            });
                    });
            </script>';

        $utime = date('m', $forum->created);

        switch ($utime){
            case 1 :
                $forum_graph_data[0]++;
                break;
            case 2 :
                $forum_graph_data[1]++;
                break;
            case 3 :
                $forum_graph_data[2]++;
                break;
            case 4 :
                $forum_graph_data[3]++;
                break;
            case 5 :
                $forum_graph_data[4]++;
                break;
            case 6 :
                $forum_graph_data[5]++;
                break;
            case 7 :
                $forum_graph_data[6]++;
                break;
            case 8 :
                $forum_graph_data[7]++;
                break;
            case 9 :
                $forum_graph_data[8]++;
                break;
            case 10 :
                $forum_graph_data[9]++;
                break;
            case 11 :
                $forum_graph_data[10]++;
                break;
            case 12 :
                $forum_graph_data[11]++;
                break;
        }
    }
    echo '</table>';
    echo '<script>
            var chartdata53 = {
                "config": {
                    "title": "'. get_string('forum_graph_numofpost_title', 'block_mamiline') .'",
                    "subTitle": "'. get_string('forum_graph_numofpost_subject', 'block_mamiline') .'",
                    "type": "stackedarea",
                    "bg": "#fff",
                    "xColor": "rgba(150,150,150,0.6)",
                    "colorSet":
                        ["rgba(0,150,250,0.5)","rgba(200,0,250,0.4)","rgba(250,250,0,0.3)"],
                    "textColor": "#444",
                    "useMarker": "arc",
                    "useVal": "yes"
                },
                "data": [
                    ["'. get_string('forum_month', 'block_mamiline') . '",1,2,3,4,5,6,7,8,9,10,11,12],
                    ["'. get_string('forum_graph_numofpost_title', 'block_mamiline') . '", ' .$forum_graph_data[0] . ',
                               '.$forum_graph_data[1] . ','
                                .$forum_graph_data[2] . ','
                                .$forum_graph_data[3] . ','
                                .$forum_graph_data[4] . ','
                                .$forum_graph_data[5] . ','
                                .$forum_graph_data[6] . ','
                                .$forum_graph_data[7] . ','
                                .$forum_graph_data[8] . ','
                                .$forum_graph_data[9] . ','
                                .$forum_graph_data[10]. ','
                                .$forum_graph_data[11]. ']
                ]
            };
        ccchart.init("forum_chart", chartdata53)
</script>';
}

function mamiline_common_calcgrade($quiz){
    if($quiz->qa_sumgrades == 0 || $quiz->q_sumgrades == 0){
        return 0;
    }else{
        return round((($quiz->qa_sumgrades / $quiz->q_sumgrades) * 100), 1);
    }
}

function mamiline_get_action($log, $userid){
    global $CFG, $DB;
    $basedir = $CFG->wwwroot . '/blocks/mamiline';

    $user = $DB->get_record('user', array('id' => $userid));

    $s_action = str_replace(" ", "_", $log->action);
    $action =  get_string('timeline_' . $s_action, 'block_mamiline');

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
            $title = $module->name . 'を' . $action;
            $message = $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'へアクセスしました。';
            break;
        case 'view_summary' :
            $title = $module->name . 'を' . $action;
            $message =  $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を表示しました。';
            break;
        case 'view_submit_assignment_form' :
            $title = $module->name . 'を' . $action;
            $message =  $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を表示しました。';
            break;
        case 'submit' :
            $title = $module->name . 'を' . $action;
            $message =  $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を提出しました。';
            break;
        case 'close_attempt' :
            $title = $module->name . 'を' . $action;
            $message = $user_url . 'が' . userdate($log->time) . "に" . $module->name . 'を提出しました。';
            break;
        case 'review' :
            $title = $module->name . 'が' . $action . '完了';
            $message =  \html_writer::empty_tag('img', array('src' => $basedir . '/images/hanamaru.png', 'height' => '60px', 'width' => '60px')) . $user_url . 'が' . userdate($log->time) . "に" . $action . 'されました';
            break;
        case 'update' :
            $title = $log->module . 'を' . $action;
            $message = '';
            break;
        case 'new' :
            $title = $log->module . 'を' . $action;
            $message = '';
            break;
        case 'edit' :
            $title = $log->module . 'を' . $action;
            $message = '';
            break;
        case 'login' :
            $title = $action;
            $message = \html_writer::empty_tag('img', array('src' => $basedir . '/images/login.png', 'height' => '60px', 'width' => '60px')) . $user_url . 'が' . userdate($log->time) . $action . 'しました';
            break;
        case 'logout' :
            $title = $action;
            $message = \html_writer::empty_tag('img', array('src' => $basedir . '/images/logout.png', 'height' => '60px', 'width' => '60px')) . $user_url . 'が' . userdate($log->time) . $action . 'しました';
            break;
        case 'add' :
            $title = $log->module . 'を' . $action;
            $message = $user_url . 'が' . userdate($log->time) . $action . 'しました';
            break;
        case 'add_mod' :
            $title = $log->module . 'を' . $action;
            $message = $user_url . 'が' . userdate($log->time) . $action . 'しました';
            break;
        case 'pre-view' :
            $title = $log->module . 'を' . $action;
            $message = $user_url . 'が' . userdate($log->time) . $action . 'しました';
            break;
        case 'report' :
            $title = $log->module . 'を' . $action;
            $message =  $user_url . 'が' . $log->module . 'を表示しました。';
            break;
        case 'report_log' :
            $title = $log->module . 'を' . $action;
            $message =  $user_url . 'が' . $log->module . 'を表示しました。';
            break;
        default :
            $title = $module->name . 'を' . $action;
            $message = $module->name . '';
            break;
    }
    $action = array('action' => get_string('timeline_' . $s_action, 'block_mamiline'), 'title'=>$title ,'message' => $message);

    return $action;
}

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
        [ 'assignid' => $cm->instance, 'userid' => $userid, 'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED ]);
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

    $quiz = $DB->get_record('quiz', [ 'id' => $cm->instance ]);
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
        $assignmoduleid = $DB->get_field('modules', 'id', [ 'name' => 'assign' ]);
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
        [ 'userid' => $userid,
          'moduleid' => $assignmoduleid,
          'submitted' => ASSIGN_SUBMISSION_STATUS_SUBMITTED ] + $cmparams);
}