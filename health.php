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
 * Health check endpoint for auth_suap plugin.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_login(null, false);
require_once(__DIR__ . "/locallib.php");
require_once(__DIR__ . "/version.php");

$PAGE->set_url(new moodle_url('/auth/suap/health.php'));

$conf = get_auth_suap_config();
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('X-Moodle-Plugin-Version: ' . $plugin->version);
header('X-Moodle-Plugin-Release: ' . $plugin->release);
echo json_encode(
    [
        "component" => $plugin->component,
        "release" => $plugin->release,
        "version" => $plugin->version,
        "client_id" => $conf->client_id == optional_param('client_id', '', PARAM_RAW),
        "authorize_url" => $conf->authorize_url,
        "token_url" => $conf->token_url,
        "rh_eu_url" => $conf->rh_eu_url,
        "logout_url" => $conf->logout_url,
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
