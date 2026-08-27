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
 * Scheduled task: recompute firstname/lastname for SUAP users from the last-login payload.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_suap\task;

/**
 * Percorre os usuários autenticados via SUAP e recalcula firstname/lastname a partir do payload
 * salvo em ``profile_field_last_login`` no último login, sem exigir uma nova autenticação no
 * SUAP — útil após alterar as configurações ``name_source_order``/``name_split_rule``
 * (Site administration → Plugins → Authentication → SUAP), para aplicar a nova regra
 * retroativamente aos usuários já existentes.
 *
 * Pode ser disparada a qualquer momento pelo administrador em Site administration → Server →
 * Tasks → Scheduled tasks, usando a opção "Run now", além de rodar no cron conforme o
 * agendamento definido em db/tasks.php (desabilitada por padrão).
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_user_names extends \core\task\scheduled_task {
    /**
     * Get task name for display.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_sync_user_names', 'auth_suap');
    }

    /**
     * Execute the task.
     *
     * @return void
     */
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/auth/suap/auth.php');
        require_once($CFG->dirroot . '/user/lib.php');

        $auth = new \auth_plugin_suap();

        $usuarios = $DB->get_recordset('user', ['auth' => 'suap', 'deleted' => 0]);

        $elegiveis = 0;
        $atualizados = 0;

        foreach ($usuarios as $usuario) {
            $userdata = $auth->get_last_login_payload($usuario);
            if (!$userdata) {
                continue;
            }

            [$firstname, $lastname] = $auth->resolve_firstname_lastname($userdata);
            if ($firstname === '' || $lastname === '') {
                continue;
            }

            $elegiveis++;

            if ($usuario->firstname === $firstname && $usuario->lastname === $lastname) {
                continue;
            }

            $record = (object) [
                'id' => $usuario->id,
                'firstname' => $firstname,
                'lastname' => $lastname,
            ];
            \user_update_user($record, false, false);
            $atualizados++;

            mtrace(
                "[AUTH SUAP] Nome atualizado para {$usuario->username} (id {$usuario->id}): "
                    . "'{$usuario->firstname} {$usuario->lastname}' -> '{$firstname} {$lastname}'."
            );
        }

        $usuarios->close();

        mtrace(
            "[AUTH SUAP] Concluído: {$elegiveis} usuário(s) elegível(is) processado(s), "
                . "{$atualizados} nome(s) atualizado(s)."
        );
    }
}
