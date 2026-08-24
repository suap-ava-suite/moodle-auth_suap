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
 * Event triggered when updating a SUAP user's picture fails.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_suap\event;

/**
 * Disparado quando não é possível atualizar a foto de um usuário SUAP (todas as URLs
 * candidatas falharam no download, ou process_new_icon() não conseguiu processar a imagem).
 *
 * Complementar às mensagens de mtrace() emitidas para o mesmo fim em auth.php (visíveis no
 * *Task output* de Administração do site → Servidor → Tarefas → Logs de tarefas, mas exigem
 * abrir a execução certa e ler o texto). Este evento é gravado pelo log store padrão do
 * Moodle e aparece em Administração do site → Relatórios → Logs, filtrável diretamente pelo
 * usuário afetado (coluna "Affected user") ou pelo nome do evento, sem precisar localizar a
 * execução da tarefa.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class picture_update_failed extends \core\event\base {
    /**
     * Initialise required event data properties.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'user';
    }

    /**
     * Returns localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventpictureupdatefailed', 'auth_suap');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        $reason = $this->other['reason'] ?? '';
        return "A atualização da foto do usuário com id '{$this->relateduserid}' via SUAP falhou: {$reason}";
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/user/editadvanced.php', ['id' => $this->relateduserid]);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' value must be specified in the event.');
        }
        if (!isset($this->other['reason'])) {
            throw new \coding_exception('The \'reason\' value must be specified in the \'other\' event data.');
        }
    }
}
