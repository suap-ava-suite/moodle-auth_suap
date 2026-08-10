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
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/auth/suap/locallib.php');

/**
 * Bulk create and configure custom user profile fields required by SUAP.
 *
 * @return bool
 */
function auth_suap_bulk_user_custom_field() {
    global $DB;

    $suapfields = [];

    $suapcategory = auth_suap_get_or_create(
        'user_info_category',
        ['name' => 'SUAP'],
        ['sortorder' => auth_suap_get_last_sort_order('user_info_category')]
    );
    $suap = $suapcategory->id;

    $suapfields[] = auth_suap_save_user_custom_field($suap, 'tipo_usuario', 'Tipo de usuário')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($suap, 'eh_servidor', 'É servidor', 'checkbox')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($suap, 'eh_aluno', 'É aluno', 'checkbox')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($suap, 'eh_prestador', 'É prestador', 'checkbox')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($suap, 'eh_usuarioexterno', 'É usuário externo', 'checkbox')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($suap, 'eh_docente', 'É docente', 'checkbox')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field(
        $suap,
        'eh_tecnico_administrativo',
        'É técnico administrativo',
        'checkbox'
    )->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($suap, 'last_login', 'JSON do último login', 'textarea', 0)->shortname;

    $pessoaiscategory = auth_suap_get_or_create(
        'user_info_category',
        ['name' => 'Dados pessoais'],
        ['sortorder' => auth_suap_get_last_sort_order('user_info_category')]
    );
    $pessoais = $pessoaiscategory->id;

    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'nome_apresentacao', 'Nome de apresentação')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'nome_completo', 'Nome completo')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'nome_social', 'Nome social')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'data_de_nascimento', 'Data de nascimento')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'sexo', 'Sexo')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'suap_id', 'SUAP ID')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'cpf', 'CPF')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'rg', 'RG')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'passaporte', 'Passaporte')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'naturalidade', 'Naturalidade')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'filiacao_mae', 'Nome da Mãe')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'filiacao_pai', 'Nome do Pai')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field(
        $pessoais,
        'id_doc_certificado',
        'ID do documento para certificado'
    )->shortname;
    $suapfields[] = auth_suap_save_user_custom_field(
        $pessoais,
        'tipo_doc_certificado',
        'Tipo de documento para certificado'
    )->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($pessoais, 'eh_estrangeiro', 'É estrangeiro', 'checkbox')->shortname;

    $contatoscategory = auth_suap_get_or_create(
        'user_info_category',
        ['name' => 'Dados de contato'],
        ['sortorder' => auth_suap_get_last_sort_order('user_info_category')]
    );
    $contatos = $contatoscategory->id;

    $suapfields[] = auth_suap_save_user_custom_field(
        $contatos,
        'email_google_classroom',
        'E-mail @escolar (Google Classroom)'
    )->shortname;
    $suapfields[] = auth_suap_save_user_custom_field(
        $contatos,
        'email_academico',
        'E-mail @academico (Microsoft)'
    )->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($contatos, 'email_secundario', 'Secundário (servidores)')->shortname;

    $matriculacategory = auth_suap_get_or_create(
        'user_info_category',
        ['name' => 'Matrícula'],
        ['sortorder' => auth_suap_get_last_sort_order('user_info_category')]
    );
    $matricula = $matriculacategory->id;

    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'programa_nome', 'Nome do programa')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'ingresso_periodo', 'Período de ingresso')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'outras_matriculas', 'Outras matrículas')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'situacao_vinculo', 'Situação do vínculo')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field(
        $matricula,
        'situacao_sistemica',
        'Situação do vínculo - Sistêmica'
    )->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'matricula_regular', 'Matrícula regular', 'checkbox')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'vinculo_ativo', 'Vínculo ativo', 'checkbox')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'vinculo_cargo', 'Cargo do vínculo')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'vinculo_categoria', 'Categoria do vínculo')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'ira', 'IRA')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'matriz_curricular', 'Matriz curricular')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($matricula, 'turno', 'Turno')->shortname;

    $polocategory = auth_suap_get_or_create(
        'user_info_category',
        ['name' => 'Polo'],
        ['sortorder' => auth_suap_get_last_sort_order('user_info_category')]
    );
    $polo = $polocategory->id;

    $suapfields[] = auth_suap_save_user_custom_field($polo, 'polo_id', 'ID do polo')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($polo, 'polo_nome', 'Nome do polo')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($polo, 'polo_sigla', 'Sigla do polo')->shortname;

    $campuscategory = auth_suap_get_or_create(
        'user_info_category',
        ['name' => 'Campus'],
        ['sortorder' => auth_suap_get_last_sort_order('user_info_category')]
    );
    $campus = $campuscategory->id;

    $suapfields[] = auth_suap_save_user_custom_field($campus, 'campus_id', 'ID do campus')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($campus, 'campus_descricao', 'Descrição do campus')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($campus, 'campus_sigla', 'Sigla do campus')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($campus, 'campus_curso', 'Campus e Curso')->shortname;

    $cursocategory = auth_suap_get_or_create(
        'user_info_category',
        ['name' => 'Curso'],
        ['sortorder' => auth_suap_get_last_sort_order('user_info_category')]
    );
    $curso = $cursocategory->id;

    $suapfields[] = auth_suap_save_user_custom_field($curso, 'curso_id', 'ID do curso')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($curso, 'curso_codigo', 'Código do curso')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($curso, 'curso_descricao', 'Descrição do curso')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($curso, 'curso_modalidade_id', 'Id da modalidade')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field(
        $curso,
        'curso_modalidade_descricao',
        'Descrição da modalidade'
    )->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($curso, 'curso_modalidade', 'Modalidade')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($curso, 'curso_nivel_ensino_id', 'Id do nível de ensino')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field(
        $curso,
        'curso_nivel_ensino_descricao',
        'Descrição do nível de ensino'
    )->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($curso, 'curso_nivel_ensino', 'Nível de ensino')->shortname;

    $turmacategory = auth_suap_get_or_create(
        'user_info_category',
        ['name' => 'Turma'],
        ['sortorder' => auth_suap_get_last_sort_order('user_info_category')]
    );
    $turma = $turmacategory->id;

    $suapfields[] = auth_suap_save_user_custom_field($turma, 'turma_id', 'ID da última turma')->shortname;
    $suapfields[] = auth_suap_save_user_custom_field($turma, 'turma_codigo', 'Código última da turma')->shortname;

    // Ensure custom profile fields are locked for the user and visible only to the user (except last_login).
    [$insql, $params] = $DB->get_in_or_equal($suapfields);
    $DB->execute(
        "UPDATE {user_info_field} SET locked = 1, visible = 1 WHERE shortname $insql AND shortname <> 'last_login'",
        $params
    );
    $DB->execute("UPDATE {user_info_field} SET locked = 1, visible = 0 WHERE shortname = 'last_login'");

    // Lock all custom profile fields across all installed authentication methods.
    $authplugins = array_keys(\core_component::get_plugin_list('auth'));
    foreach ($authplugins as $auth) {
        foreach ($suapfields as $shortname) {
            set_config('field_lock_profile_field_' . $shortname, 'locked', 'auth_' . $auth);
        }
    }

    return true;
}
