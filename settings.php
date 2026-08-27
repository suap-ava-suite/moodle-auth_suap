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
 * Admin settings for auth_suap plugin.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/auth.php');

if ($ADMIN->fulltree) {
    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_suap/pluginname', '', get_string('auth_suap_description', 'auth_suap')));

    $suapbaseurl = getenv('SUAP_BASE_URL') ?: 'https://suap.ifrn.edu.br';

    auth_suap_create_setting_configtext($settings, "client_id", "veja no SUAP");
    auth_suap_create_setting_configtext($settings, "client_secret", "veja no SUAP");
    auth_suap_create_setting_configtext($settings, "authorize_url", "$suapbaseurl/o/authorize/");
    auth_suap_create_setting_configtext($settings, "token_url", "$suapbaseurl/o/token/");
    auth_suap_create_setting_configtext($settings, "rh_eu_url", "$suapbaseurl/api/rh/eu/");
    auth_suap_create_setting_configtext($settings, "rh_meus_dados_url", "$suapbaseurl/api/rh/meus-dados/");
    auth_suap_create_setting_configtext($settings, "rh_meus_vinculos_url", "$suapbaseurl/api/rh/meus-vinculos/");
    auth_suap_create_setting_configtext(
        $settings,
        "ensino_meus_dados_aluno_url",
        "$suapbaseurl/api/ensino/meus-dados-aluno/"
    );
    auth_suap_create_setting_configtext($settings, "logout_url", "$suapbaseurl/comum/logout/");

    auth_suap_create_setting_configselect(
        $settings,
        "name_source_order",
        [
            \auth_plugin_suap::NAME_ORDER_SOCIAL_USUAL_REGISTRO,
            \auth_plugin_suap::NAME_ORDER_USUAL_SOCIAL_REGISTRO,
            \auth_plugin_suap::NAME_ORDER_USUAL_REGISTRO,
            \auth_plugin_suap::NAME_ORDER_SOCIAL_REGISTRO,
            \auth_plugin_suap::NAME_ORDER_REGISTRO,
        ],
        \auth_plugin_suap::NAME_ORDER_SOCIAL_USUAL_REGISTRO
    );

    auth_suap_create_setting_configselect(
        $settings,
        "name_split_rule",
        [
            \auth_plugin_suap::NAME_SPLIT_FIRST_LAST,
            \auth_plugin_suap::NAME_SPLIT_FIRSTS_LAST,
            \auth_plugin_suap::NAME_SPLIT_FIRST_REST,
        ],
        \auth_plugin_suap::NAME_SPLIT_FIRSTS_LAST
    );

    $authplugin = get_auth_plugin('suap');
    display_auth_lock_options(
        $settings,
        $authplugin->authtype,
        $authplugin->userfields,
        get_string('auth_fieldlocks_help', 'auth'),
        true,
        true,
        $authplugin->get_custom_user_profile_fields()
    );
}
