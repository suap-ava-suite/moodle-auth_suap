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
 * Dispatch web service requests for SUAP authentication.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
define('REQUIRE_CORRECT_ACCESS', true);
define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../lib/externallib.php');
require_once(__DIR__ . '/locallib.php');

// Permissões de CORS para requisições PREFLIGHT (ionic).
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authentication");
    exit;
}

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

/**
 * Validate that web services are enabled on the site.
 *
 * @return stdClass The external service record.
 * @throws moodle_exception
 */
function validate_enabled_web_services() {
    global $DB, $CFG;

    if (!$CFG->enablewebservices) {
        throw new moodle_exception('enablewsdescription', 'webservice');
    }

    // Não pode se o serviço não existir e não estiver habilitado.
    $servicename = required_param('service', PARAM_ALPHANUMEXT);
    $service = $DB->get_record('external_services', ['shortname' => $servicename, 'enabled' => 1]);
    if (empty($service)) {
        throw new moodle_exception('servicenotavailable', 'webservice');
    }

    // This script is used by the mobile app to check that the site is available and web services
    // are allowed. In this mode, no further action is needed.
    if (optional_param('appsitecheck', 0, PARAM_INT)) {
        echo json_encode((object)['appsitecheck' => 'ok']);
        exit;
    }

    return $service;
}

/**
 * Authenticate the caller of the web service via token.
 *
 * @return string Username of the caller.
 * @throws Exception
 */
function authenticate_service_caller() {
    $config = get_auth_suap_config();
    $headers = getallheaders();

    // Verifica se o token de autenticação está no header.
    $authenticationkey = array_key_exists('Authentication', $headers) ? "Authentication" : "authentication";
    if (!array_key_exists($authenticationkey, $headers)) {
        throw new \Exception("Bad Request - Authentication not informed", 400);
    }

    // Recorta o token do header "Token ...".
    $token = substr($headers[$authenticationkey], 6);

    $verifyresponse = auth_suap_curl_post(
        $config->verify_token_url,
        json_encode(["token" => $token]),
        'application/json'
    );
    $response = json_decode($verifyresponse);

    return $response->username;
}

/**
 * Authenticate and load the user record into memory.
 *
 * @param string $username
 * @return void
 * @throws moodle_exception
 */
function authenticate_user($username) {
    global $USER, $DB;

    // Verifica se o usuário necessita trocar a senha.
    $username = trim(core_text::strtolower($username));
    if (is_restored_user($username)) {
        throw new moodle_exception('restoredaccountresetpassword', 'webservice');
    }

    // Não pode se o usuário não existir.
    $USER = $DB->get_record("user", ["username" => $username]);
    if (empty($USER)) {
        throw new moodle_exception('invalidlogin');
    }
}

/**
 * Check user authorization and setup session.
 *
 * @return void
 * @throws moodle_exception
 */
function authorize_user() {
    global $USER, $CFG;

    // Não pode guest user.
    if (isguestuser($USER)) {
        throw new moodle_exception('noguest');
    }

    // Não pode usuário que ainda não confirmaram a senha.
    if (empty($USER->confirmed)) {
        throw new moodle_exception('usernotconfirmed', 'moodle', '', $USER->username);
    }

    // Para controlar: autorização.
    $systemcontext = context_system::instance();

    // Não pode em modo de manutenção, exceto administradores.
    $hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', $systemcontext, $USER);
    if (!empty($CFG->maintenance_enabled) && !$hasmaintenanceaccess) {
        throw new moodle_exception('sitemaintenance', 'admin');
    }

    // Let enrol plugins deal with new enrolments if necessary.
    enrol_check_plugins($USER);

    // Setup user session to check capability.
    \core\session\manager::set_user($USER);

    $USER->site_admin = has_capability('moodle/site:config', $systemcontext, $USER->id);
}

/**
 * Generate and return web service token response.
 *
 * @param stdClass $service
 * @return void
 */
function response_token($service) {
    global $USER;

    $token = external_generate_token_for_current_user($service);

    echo json_encode(
        [
            "token" => $token->token,
            "privatetoken" => is_https() && !$USER->site_admin ? $token->privatetoken : null,
        ]
    );

    external_log_token_request($token);
}

$service = validate_enabled_web_services();
$username = authenticate_service_caller();
authenticate_user($username);
authorize_user();
response_token($service);
