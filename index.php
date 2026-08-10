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
 * Entry point redirecting to SUAP authentication login.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Página pública de autenticação.
// Não chama require_login(), pois isso redirecionaria para o login padrão.
// phpcs:ignore Moodle.Files.RequireLogin.Missing
require_once('../../config.php');
require_once("$CFG->dirroot/auth/suap/locallib.php");

$PAGE->set_url(new moodle_url('/auth/suap/index.php'));

auth_suap_redirect(new moodle_url('/auth/suap/login.php'));
