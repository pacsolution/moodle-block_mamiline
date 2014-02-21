<?php

namespace mamiline;

/* @var $DB moodle_database */
/* @var $CFG object */
/* @var $USER object */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

class assign {
    public static function files($cmid, $userid)
    {
        global $DB;

        $files = $DB->get_records('files',
            array('contextid' => $cmid,
                'userid' => $userid,
                'component' => 'assignsubmission_file',
                'filearea' => 'submission_files'
            ),
            '',
            'id, contextid, itemid, component, filearea, filename, filesize, filepath, timemodified, mimetype');

        return $files;

    }

    public static function submission($id){
        global $DB, $USER;

        $sql = "SELECT a.id, asub.userid, asub.timemodified, asub.status, a.name, a.course, a.grade, a.duedate, co.fullname, asf.numfiles, ast.onlinetext, f.commenttext FROM {assign_submission} as asub
            JOIN {assign} as a ON asub.assignment = a.id
            JOIN {course} as co ON a.course = co.id
            LEFT OUTER JOIN {assignsubmission_file} as asf ON asf.assignment = a.id
            LEFT OUTER JOIN {assignsubmission_onlinetext} as ast ON ast.assignment = a.id
            LEFT OUTER JOIN {assignfeedback_comments} as f ON f.assignment = a.id
            WHERE asub.userid = :userid AND asub.id = :id AND asub.status = 'submitted'";

        $submission = $DB->get_record_sql($sql, array('id' => $id, 'userid' => $USER->id));

        return $submission;
    }

    public static function submissions(){
        global $DB, $USER;

        $sql = "SELECT asub.id, asub.userid, asub.timemodified, asub.status, a.name, a.course, a.grade, a.duedate, co.fullname FROM {assign_submission} as asub
            JOIN {assign} as a ON asub.assignment = a.id
            JOIN {course} as co ON a.course = co.id
            WHERE asub.userid = :userid";
        $submissions = $DB->get_records_sql($sql, array('userid' => $USER->id));

        return $submissions;
    }
} 