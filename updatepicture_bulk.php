<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Bulk action: attempt to update the picture of the selected SUAP users, from the last-login
 * data already stored in profile_field_last_login (no new SUAP authentication required).
 *
 * Reached from the "With selected users..." dropdown on the administrative user list, via the
 * core_user\hook\extend_bulk_user_actions hook (see classes/hook_callbacks.php).
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/auth/suap/auth.php');

require_login(null, false);

$context = context_system::instance();
require_capability('auth/suap:updatepicture', $context);

$confirm = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$return = new moodle_url($returnurl ?: '/admin/user.php');

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/auth/suap/updatepicture_bulk.php'));
$PAGE->set_title(get_string('bulk_updatepicture', 'auth_suap'));
$PAGE->set_heading(get_string('bulk_updatepicture', 'auth_suap'));
$PAGE->set_primary_active_tab('siteadminnode');
$PAGE->set_secondary_active_tab('users');

if (empty($SESSION->bulk_users)) {
    redirect($return);
}

echo $OUTPUT->header();

if ($confirm && confirm_sesskey()) {
    $auth = new auth_plugin_suap();

    $enfileirados = 0;
    $semdados = 0;

    // O download/processamento da foto é lento; cada usuário é enfileirado como uma tarefa ad
    // hoc para rodar em segundo plano via cron, em vez de bloquear esta requisição.
    $parts = array_chunk($SESSION->bulk_users, 300);
    foreach ($parts as $ids) {
        [$insql, $params] = $DB->get_in_or_equal($ids);
        $rs = $DB->get_recordset_select('user', "id $insql AND deleted = 0", $params);
        foreach ($rs as $usuario) {
            if ($auth->queue_update_picture_task($usuario)) {
                $enfileirados++;
            } else {
                $semdados++;
            }
        }
        $rs->close();
    }

    $SESSION->bulk_users = [];

    echo $OUTPUT->notification(
        get_string('bulk_updatepicture_result', 'auth_suap', (object) [
            'enfileirados' => $enfileirados,
            'semdados' => $semdados,
        ]),
        \core\output\notification::NOTIFY_SUCCESS
    );
    echo $OUTPUT->continue_button($return);
} else {
    [$insql, $params] = $DB->get_in_or_equal($SESSION->bulk_users);
    $userlist = $DB->get_records_select_menu(
        'user',
        "id $insql",
        $params,
        'fullname',
        'id,' . $DB->sql_fullname() . ' AS fullname'
    );
    $usernames = implode(', ', $userlist);

    echo $OUTPUT->heading(get_string('confirmation', 'admin'));
    $formcontinue = new single_button(
        new moodle_url('/auth/suap/updatepicture_bulk.php', ['confirm' => 1, 'returnurl' => $returnurl]),
        get_string('yes')
    );
    $formcancel = new single_button($return, get_string('no'), 'get');
    echo $OUTPUT->confirm(
        get_string('bulk_updatepicture_confirm', 'auth_suap', $usernames),
        $formcontinue,
        $formcancel
    );
}

echo $OUTPUT->footer();
