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
 * Ponte para a auditoria de usuários do local_suap.
 *
 * O auth_suap não exige o local_suap: sem ele, as funções abaixo apenas executam a escrita core.
 *
 * @package     auth_suap
 * @copyright   2026 IFRN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_suap;

defined('MOODLE_INTERNAL') || die();

/**
 * Auditoria de alterações de usuário feitas pelo auth_suap.
 */
class audit {
    /**
     * Auditoria disponível (local_suap instalado)?
     *
     * @return bool
     */
    public static function available(): bool {
        return class_exists('\local_suap\audit\writer');
    }

    /**
     * Define o ator da requisição atual.
     *
     * @param string $actorlabel Rótulo do ator.
     * @param string|null $requestref Referência da requisição.
     */
    public static function set_context(string $actorlabel, ?string $requestref = null): void {
        if (self::available()) {
            \local_suap\audit\context::set($actorlabel, $requestref);
        }
    }

    /**
     * Cria o usuário (user_create_user) registrando a criação.
     *
     * @param \stdClass $user Dados do usuário.
     * @return int Id.
     */
    public static function create_user(\stdClass $user): int {
        if (self::available()) {
            return \local_suap\audit\writer::create_user($user);
        }
        return \user_create_user($user);
    }

    /**
     * Atualiza campos do usuário (user_update_user) registrando o diff.
     *
     * @param \stdClass $user Campos (com id).
     * @param bool $updatepassword Atualizar senha.
     * @param bool $triggerevent Disparar evento.
     */
    public static function update_user(\stdClass $user, bool $updatepassword = true, bool $triggerevent = true): void {
        if (self::available()) {
            \local_suap\audit\writer::update_user($user, $updatepassword, $triggerevent);
            return;
        }
        \user_update_user($user, $updatepassword, $triggerevent);
    }

    /**
     * Executa uma escrita opaca registrando o diff do usuário.
     *
     * @param int $userid Usuário.
     * @param callable $write Escrita a executar.
     * @return mixed Retorno de $write.
     */
    public static function around(int $userid, callable $write) {
        if (!self::available()) {
            return $write();
        }
        $before = \local_suap\audit\writer::snapshot_user($userid);
        $result = $write();
        \local_suap\audit\writer::record_diff($userid, $before, \local_suap\audit\writer::snapshot_user($userid));
        return $result;
    }

    /**
     * Grava a foto do usuário em {user}.picture registrando a alteração.
     *
     * @param int $userid Usuário.
     * @param int $picture Valor de picture.
     */
    public static function set_picture(int $userid, int $picture): void {
        global $DB;
        if (self::available()) {
            \local_suap\audit\writer::set_user_field($userid, 'picture', $picture);
            return;
        }
        $DB->set_field('user', 'picture', $picture, ['id' => $userid]);
    }
}
