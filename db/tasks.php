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
 * Scheduled tasks for auth_suap.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'auth_suap\task\backfill_user_pictures',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '3',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
        // Desabilitada por padrão: destina-se a ser disparada manualmente via "Run now"
        // em Site administration > Server > Tasks > Scheduled tasks. O admin pode habilitar
        // o agendamento diário acima se preferir execução automática.
        'disabled' => 1,
    ],
    [
        'classname' => 'auth_suap\task\sync_user_names',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '3',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
        // Desabilitada por padrão: destina-se a ser disparada manualmente via "Run now"
        // em Site administration > Server > Tasks > Scheduled tasks, tipicamente logo após
        // alterar name_source_order/name_split_rule, para aplicar a nova regra
        // retroativamente. O admin pode habilitar o agendamento diário acima se preferir
        // execução automática.
        'disabled' => 1,
    ],
];
