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
 * @package   block_mamiline
 * @copyright VERSION2 Inc. <yue@eldoom.me>
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'block/mamiline:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW

        )
    ),
    'block/mamiline:addinstance' => array(
        'riskbitmask'  => RISK_SPAM | RISK_XSS,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes'   => array(
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/grade:edit',
    ),
    'block/mamiline:viewteacher' => array(
        'riskbitmask'  => RISK_SPAM | RISK_XSS,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes'   => array(
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
        )
    ),
    'block/mamiline:config' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/grade:edit',
    )
);