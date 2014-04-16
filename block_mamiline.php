<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * mamiline block caps.
 *
 * @package    block_mamiline
 * @copyright  VERSION2 Inc. <t-fuwa@ver2.jp>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_mamiline extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_mamiline');
    }

    function get_content() {
        global $OUTPUT;

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $html = html_writer::tag('div',
            $OUTPUT->action_link(
                new moodle_url('/blocks/mamiline/index.php', ['page' => 'top']),
                $OUTPUT->pix_icon('i/settings', '') . get_string('showmamiline', 'block_mamiline')
            )
        );
        return $this->content = (object)[ 'text' => $html ];
    }

    public function applicable_formats() {
        return array('all' => true,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true,
                     'course-view-social' => false,
                     'mod' => false,
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
        return true;
    }

    function has_config() {return false;}

    public function cron() {
        return true;
    }
}
