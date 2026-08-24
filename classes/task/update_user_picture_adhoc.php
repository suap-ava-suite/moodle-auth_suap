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
 * Adhoc task: update a single SUAP user's picture in the background.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_suap\task;

/**
 * Roda em segundo plano (fila de tarefas ad hoc, processada pelo cron) o download e
 * processamento da foto de um usuário, para não bloquear o fluxo síncrono que a enfileirou —
 * login via SUAP ou a ação em massa da listagem administrativa de usuários — enquanto a foto
 * é baixada e processada. Ver auth_plugin_suap::queue_update_picture_task().
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_user_picture_adhoc extends \core\task\adhoc_task {
    /**
     * Get task name for display.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_update_user_picture_adhoc', 'auth_suap');
    }

    /**
     * Execute the task.
     *
     * @return void
     */
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/auth/suap/auth.php');

        $data = $this->get_custom_data();
        $usuario = $DB->get_record('user', ['id' => $data->userid, 'deleted' => 0]);
        if (!$usuario) {
            mtrace("[AUTH SUAP] Usuário id {$data->userid} não encontrado ou excluído; tarefa ignorada.");
            return;
        }

        $auth = new \auth_plugin_suap();

        $fotosources = $auth->get_last_login_photo_sources($usuario);
        if (empty($fotosources)) {
            mtrace("[AUTH SUAP] Usuário {$usuario->username} (id {$usuario->id}) sem dados de foto do SUAP; nada a fazer.");
            return;
        }

        if ($auth->update_picture($usuario, $fotosources)) {
            mtrace("[AUTH SUAP] Atualização de foto em segundo plano concluída para {$usuario->username} (id {$usuario->id}).");
        } else {
            mtrace(
                "[AUTH SUAP] Falha ao atualizar a foto em segundo plano para {$usuario->username} (id {$usuario->id}). "
                    . "Ver Relatórios > Logs para o motivo."
            );
        }
    }
}
