<?php

namespace mamiline;


class assign {
    public static function files($cmid)
    {
        $files = get_file_storage()->get_area_files($cmid, 'assignsubmission_file', 'submission_files');

        return $files->file_record;

    }
} 