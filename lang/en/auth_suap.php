<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['auth_description'] = 'OAuth2 Authentication';
$string['auth_suap_description'] = 'SUAP is the Unified System of Public Administration (Sistema Unificado de Administração Pública) used throughout Brazilian federal institutions, including the Federal Institute of Rio Grande do Norte (IFRN). This plugin enables single sign-on (SSO) integration, allowing students and staff to log into Moodle using their SUAP credentials. It automatically synchronizes user data from SUAP (name, email, CPF, enrollment status) and supports role-based access control based on institutional data.';
$string['auth_token_error'] = 'An error occurred while attempting to authenticate with SUAP. The authorization code may have expired or already been used.';
$string['auth_token_error_button'] = 'Restart login via SUAP';
$string['auth_token_error_title'] = 'SUAP Authentication Failure';
$string['authorize_url'] = 'SUAP Authorization Endpoint';
$string['authorize_url_desc'] = "SUAP OAuth2 authorization URL (typically https://suap.ifrn.edu.br/o/authorize/)";
$string['bulk_updatepicture'] = 'Update picture (SUAP)';
$string['bulk_updatepicture_confirm'] = 'Schedule a background picture update for the following users, from the SUAP data already on file (no new login required)? Processing runs asynchronously (an adhoc task via cron) and will not block this screen: {$a}?';
$string['bulk_updatepicture_result'] = 'Done: {$a->enfileirados} user(s) with SUAP picture data had a background update scheduled; {$a->semdados} user(s) had no picture data on file and were skipped.';
$string['client_id'] = 'OAuth2 Client ID';
$string['client_id_desc'] = "Obtain from SUAP: Technology Management > Services > OAuth2 Applications. Register your Moodle instance with authorization type 'Authorization code' (public client) and set redirect URI to: {$CFG->wwwroot}/auth/suap/authenticate.php";
$string['client_secret'] = 'OAuth2 Client Secret';
$string['client_secret_desc'] = "This secret is displayed only once when you create the OAuth2 application in SUAP. Save it immediately as it cannot be retrieved later. To generate a new secret, register a new application in SUAP.";
$string['configincomplete'] = 'SUAP plugin configurations (authorize_url, client_id, client_secret) are incomplete or have not been saved in site administration.';
$string['ensino_meus_dados_aluno_url'] = 'SUAP Ensino/Meus Dados Aluno API Endpoint';
$string['ensino_meus_dados_aluno_url_desc'] = "SUAP API endpoint for retrieving student academic data (typically https://suap.ifrn.edu.br/api/ensino/meus-dados-aluno/)";
$string['eventpictureupdatefailed'] = 'Picture update failed (SUAP)';
$string['logout_url'] = 'SUAP Logout URL';
$string['logout_url_desc'] = "SUAP logout endpoint for session termination (typically https://suap.ifrn.edu.br/o/logout/)";
$string['name_source_order'] = 'Display name source order';
$string['name_source_order_desc'] = 'Which SUAP name field(s) to use as the source for the Moodle full name, and in what priority order. The registration name (nome_registro) is always used as the final fallback, since it is the only one guaranteed to exist.';
$string['name_source_order_registro'] = 'Registration name only';
$string['name_source_order_social_registro'] = 'Social name, then registration name';
$string['name_source_order_social_usual_registro'] = 'Social name, then usual name, then registration name';
$string['name_source_order_usual_registro'] = 'Usual name, then registration name';
$string['name_source_order_usual_social_registro'] = 'Usual name, then social name, then registration name';
$string['name_split_rule'] = 'Name splitting rule';
$string['name_split_rule_desc'] = 'How to split the chosen full name into Moodle firstname/lastname.';
$string['name_split_rule_first_last'] = 'First word + last word (middle names are dropped)';
$string['name_split_rule_first_rest'] = 'First word + everything else';
$string['name_split_rule_firsts_last'] = 'Everything except the last word + last word';
$string['pluginname'] = 'SUAP OAuth2 Authentication';
$string['privacy:metadata:suap:cpf'] = 'User CPF (Brazilian tax ID)';
$string['privacy:metadata:suap:email'] = 'Email address';
$string['privacy:metadata:suap:explanation'] = 'This plugin communicates with the external SUAP service for user authentication and data synchronization. User information including username, email, name, CPF, and role information is sent to SUAP during login and regular synchronization processes.';
$string['privacy:metadata:suap:firstname'] = 'User first name';
$string['privacy:metadata:suap:lastname'] = 'User last name';
$string['privacy:metadata:suap:tipo'] = 'User type/role (student, staff, teacher, etc)';
$string['privacy:metadata:suap:username'] = 'Username (institutional ID)';
$string['rh_eu_url'] = 'SUAP RH/EU API Endpoint';
$string['rh_eu_url_desc'] = "SUAP API endpoint for retrieving user identification and personal documents data (typically https://suap.ifrn.edu.br/api/eu/)";
$string['rh_meus_dados_url'] = 'SUAP RH/Meus Dados API Endpoint';
$string['rh_meus_dados_url_desc'] = "SUAP API endpoint for retrieving user personal data and primary relationship (typically https://suap.ifrn.edu.br/api/rh/meus-dados/)";
$string['rh_meus_vinculos_url'] = 'SUAP RH/Meus Vínculos API Endpoint';
$string['rh_meus_vinculos_url_desc'] = "SUAP API endpoint for retrieving user relationships list (typically https://suap.ifrn.edu.br/api/rh/meus-vinculos/)";
$string['suap:updatepicture'] = 'Update SUAP users\' pictures (bulk action/scheduled task)';
$string['task_backfill_user_pictures'] = 'SUAP: backfill missing user pictures';
$string['task_sync_user_names'] = 'SUAP: sync user display names (nome social/usual/registro)';
$string['task_update_user_picture_adhoc'] = 'SUAP: update one user\'s picture (background)';
$string['token_url'] = 'SUAP Token Endpoint';
$string['token_url_desc'] = "SUAP OAuth2 token exchange URL (typically https://suap.ifrn.edu.br/o/token/)";
