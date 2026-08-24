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
 * Scheduled task: backfill pictures for SUAP users who don't have one yet.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_suap\task;

/**
 * Percorre os usuários autenticados via SUAP que ainda não têm foto (``user.picture == 0``)
 * e tenta preenchê-la a partir do payload salvo em ``profile_field_last_login`` no último
 * login, sem exigir uma nova autenticação no SUAP.
 *
 * Pode ser disparada a qualquer momento pelo administrador em Site administration → Server →
 * Tasks → Scheduled tasks, usando a opção "Run now", além de rodar no cron conforme o
 * agendamento definido em db/tasks.php (desabilitada por padrão).
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backfill_user_pictures extends \core\task\scheduled_task {
    /**
     * Get task name for display.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_backfill_user_pictures', 'auth_suap');
    }

    /**
     * Execute the task.
     *
     * @return void
     */
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/auth/suap/auth.php');

        $auth = new \auth_plugin_suap();

        $sql = "SELECT u.*
                  FROM {user} u
                 WHERE u.auth = :auth
                   AND u.deleted = 0
                   AND u.picture = 0";
        $usuarios = $DB->get_recordset_sql($sql, ['auth' => 'suap']);

        $elegiveis = 0;
        $atualizados = 0;

        foreach ($usuarios as $usuario) {
            $fotosources = $auth->get_last_login_photo_sources($usuario);
            if (empty($fotosources)) {
                continue;
            }

            $elegiveis++;
            $sucesso = $auth->update_picture($usuario, $fotosources);
            mtrace(
                "[AUTH SUAP] " . ($sucesso ? "Foto atualizada" : "Falha ao atualizar a foto")
                    . " para {$usuario->username} (id {$usuario->id})."
            );

            if ($sucesso) {
                $atualizados++;
            }
        }

        $usuarios->close();

        mtrace(
            "[AUTH SUAP] Concluído: {$elegiveis} usuário(s) elegível(is) processado(s), "
                . "{$atualizados} foto(s) atualizada(s)."
        );
    }
}
