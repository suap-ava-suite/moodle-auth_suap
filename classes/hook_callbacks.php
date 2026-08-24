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
 * Hook callback listeners for auth_suap.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_suap;

/**
 * Listeners for core hooks extended by auth_suap.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Adiciona a ação "Atualizar foto (SUAP)" ao dropdown de ações em massa da listagem
     * administrativa de usuários (Site administration → Users → Browse list of users),
     * visível apenas para quem tem a capability auth/suap:updatepicture.
     *
     * @param \core_user\hook\extend_bulk_user_actions $hook
     * @return void
     */
    public static function extend_bulk_user_actions(\core_user\hook\extend_bulk_user_actions $hook): void {
        if (!has_capability('auth/suap:updatepicture', \context_system::instance())) {
            return;
        }

        $hook->add_action(
            'auth_suap_updatepicture',
            new \action_link(
                new \moodle_url('/auth/suap/updatepicture_bulk.php'),
                get_string('bulk_updatepicture', 'auth_suap')
            ),
            get_string('pluginname', 'auth_suap')
        );
    }
}
