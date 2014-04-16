<?php

namespace mamiline;

class forum {
    public static function forum($forumid){
        global $DB;
        $sql = "SELECT * FROM {forum} WHERE id = :id";

        return $DB->get_record_sql($sql, array('id' => $forumid));
    }

    public static function discussion($discussionid){
        global $DB;
        $sql = "SELECT * FROM {forum_discussions} WHERE id = :discussionid";

        return $DB->get_record_sql($sql, array('discussionid' => $discussionid));
    }

    public static function discussion_posts($discussionid, $sort, $forumid) {
        global $DB;

        $allnames = get_all_user_name_fields(true, 'u');
        return $DB->get_records_sql("SELECT p.*, :forumid AS forum, $allnames, u.email, u.picture, u.imagealt, u.id AS uid
                              FROM {forum_posts} p
                         LEFT JOIN {user} u ON p.userid = u.id
                             WHERE p.discussion = :discussionid
                               ", array('forumid' => $forumid, 'discussionid' => $discussionid));
    }

    public static function post($postid){
        global $DB;
        $sql = "SELECT {forum_posts}.id,
                       {forum}.name,
                       {forum}.course,
                       {forum_discussions}.name,
                       {forum_posts}.created,
                       {forum_posts}.subject,
                       {forum_posts}.message,
                       {forum}.id AS fid,
                       {forum}.name
                FROM {forum_posts}
                JOIN {forum_discussions} ON {forum_posts}.discussion = {forum_discussions}.id
                JOIN {forum} ON {forum_discussions}.forum = {forum}.id
                WHERE {forum_posts}.userid = :postid
            ";
        $forums = $DB->get_records_sql($sql, array('postid'=> $postid));
        return $forums;
    }

    public static function posts($userid, $courseid = null){
        global $DB;
        $sql = "SELECT {forum_posts}.id,
                       {forum}.name,
                       {forum}.course,
                       {forum_discussions}.name,
                       {forum_posts}.created,
                       {forum_posts}.subject,
                       {forum_posts}.message,
                       {forum}.id AS fid,
                       {forum}.name
                FROM {forum_posts}
                JOIN {forum_discussions} ON {forum_posts}.discussion = {forum_discussions}.id
                JOIN {forum} ON {forum_discussions}.forum = {forum}.id
                WHERE {forum_posts}.userid = :userid
            ";
        if(!is_null($courseid)){
            $sql .= " AND {forum}.course = :courseid";
            $forums = $DB->get_records_sql($sql, array('userid'=> $userid, 'courseid' => $courseid));
        }else{
            $forums = $DB->get_records_sql($sql, array('userid'=> $userid));
        }
        return $forums;
    }

    public static function grade_ranking($userid){
        global $DB, $USER;
        $courses = forum_get_courses_user_posted_in($USER);


//        $grades = forum_get_user_grades($userid, $forum);

    }
}