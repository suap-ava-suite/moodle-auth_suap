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
 * Plugin upgrade helper functions are defined here.
 *
 * @package     auth_suap
 * @category    upgrade
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/auth/suap/locallib.php');


function auth_suap_bulk_user_custom_field() {
    global $DB;

    $suap_fields = [];

    $suap = auth_suap_get_or_create('user_info_category', ['name' => 'SUAP'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    $suap_fields[] = auth_suap_save_user_custom_field($suap, 'tipo_usuario', 'Tipo de usuário')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($suap, 'eh_servidor', 'É servidor', 'checkbox')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($suap, 'eh_aluno', 'É aluno', 'checkbox')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($suap, 'eh_prestador', 'É prestador', 'checkbox')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($suap, 'eh_usuarioexterno', 'É usuário externo', 'checkbox')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($suap, 'eh_docente', 'É docente', 'checkbox')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($suap, 'eh_tecnico_administrativo', 'É técnico administrativo', 'checkbox')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($suap, 'last_login', 'JSON do último login', 'textarea', 0)->shortname;

    $pessoais = auth_suap_get_or_create('user_info_category', ['name' => 'Dados pessoais'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'nome_apresentacao', 'Nome de apresentação')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'nome_completo', 'Nome completo')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'nome_social', 'Nome social')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'data_de_nascimento', 'Data de nascimento')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'sexo', 'Sexo')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'suap_id', 'SUAP ID')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'cpf', 'CPF')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'rg', 'RG')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'passaporte', 'Passaporte')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'naturalidade', 'Naturalidade')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'filiacao_mae', 'Nome da Mãe')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'filiacao_pai', 'Nome do Pai')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'id_doc_certificado', 'ID do documento para certificado')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'tipo_doc_certificado', 'Tipo de documento para certificado')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($pessoais, 'eh_estrangeiro', 'É estrangeiro', 'checkbox')->shortname;

    $contatos = auth_suap_get_or_create('user_info_category', ['name' => 'Dados de contato'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    $suap_fields[] = auth_suap_save_user_custom_field($contatos, 'email_google_classroom', 'E-mail @escolar (Google Classroom)')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($contatos, 'email_academico', 'E-mail @academico (Microsoft)')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($contatos, 'email_secundario', 'Secundário (servidores)')->shortname;

    $matricula = auth_suap_get_or_create('user_info_category', ['name' => 'Matrícula'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'programa_nome', 'Nome do programa')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'ingresso_periodo', 'Período de ingresso')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'outras_matriculas', 'Outras matrículas')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'situacao_vinculo', 'Situação do vínculo')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'situacao_sistemica', 'Situação do vínculo - Sistêmica')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'matricula_regular', 'Matrícula regular', 'checkbox')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'vinculo_ativo', 'Vínculo ativo', 'checkbox')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'vinculo_cargo', 'Cargo do vínculo')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'vinculo_categoria', 'Categoria do vínculo')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'ira', 'IRA')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'matriz_curricular', 'Matriz curricular')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($matricula, 'turno', 'Turno')->shortname;

    $polo = auth_suap_get_or_create('user_info_category', ['name' => 'Polo'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    $suap_fields[] = auth_suap_save_user_custom_field($polo, 'polo_id', 'ID do polo')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($polo, 'polo_nome', 'Nome do polo')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($polo, 'polo_sigla', 'Sigla do polo')->shortname;

    $campus = auth_suap_get_or_create('user_info_category', ['name' => 'Campus'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    $suap_fields[] = auth_suap_save_user_custom_field($campus, 'campus_id', 'ID do campus')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($campus, 'campus_descricao', 'Descrição do campus')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($campus, 'campus_sigla', 'Sigla do campus')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($campus, 'campus_curso', 'Campus e Curso')->shortname;

    $curso = auth_suap_get_or_create('user_info_category', ['name' => 'Curso'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    $suap_fields[] = auth_suap_save_user_custom_field($curso, 'curso_id', 'ID do curso')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($curso, 'curso_codigo', 'Código do curso')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($curso, 'curso_descricao', 'Descrição do curso')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($curso, 'curso_modalidade_id', 'Id da modalidade')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($curso, 'curso_modalidade_descricao', 'Descrição da modalidade')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($curso, 'curso_modalidade', 'Modalidade')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($curso, 'curso_nivel_ensino_id', 'Id do nível de ensino')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($curso, 'curso_nivel_ensino_descricao', 'Descrição do nível de ensino')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($curso, 'curso_nivel_ensino', 'Nível de ensino')->shortname;

    $turma = auth_suap_get_or_create('user_info_category', ['name' => 'Turma'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    $suap_fields[] = auth_suap_save_user_custom_field($turma, 'turma_id', 'ID da última turma')->shortname;
    $suap_fields[] = auth_suap_save_user_custom_field($turma, 'turma_codigo', 'Código última da turma')->shortname;

    // Ensure only the custom profile fields created by this method are locked for the user and visible only to the user (except last_login).
    list($in_sql, $params) = $DB->get_in_or_equal($suap_fields);
    $DB->execute("UPDATE {user_info_field} SET locked = 1, visible = 1 WHERE shortname $in_sql AND shortname <> 'last_login'", $params);
    $DB->execute("UPDATE {user_info_field} SET locked = 1, visible = 0 WHERE shortname = 'last_login'");

    // Lock all custom profile fields created by this method across all installed authentication methods.
    $authplugins = array_keys(\core_component::get_plugin_list('auth'));
    foreach ($authplugins as $auth) {
        foreach ($suap_fields as $shortname) {
            set_config('field_lock_profile_field_' . $shortname, 'locked', 'auth_' . $auth);
        }
    }

    return true;
}
