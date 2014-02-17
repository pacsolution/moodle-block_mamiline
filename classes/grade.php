<?php

namespace mamiline;

class grade {
    //TODO : currentは予約語なので修正
    public static function usergrade($quiz, $userid)
    {
        $grades = quiz_get_user_grades($quiz, $userid);
        $grade = array_shift($grades);

        return $grade;
    }
}