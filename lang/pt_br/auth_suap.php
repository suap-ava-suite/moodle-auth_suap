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

$string['auth_description'] = 'Autenticação OAuth2';
$string['auth_suap_description'] = 'SUAP é o Sistema Unificado de Administração Pública utilizado em instituições federais brasileiras, incluindo o Instituto Federal do Rio Grande do Norte (IFRN). Este plugin possibilita integração de Single Sign-On (SSO), permitindo que alunos e servidores façam login no Moodle usando suas credenciais do SUAP. Sincroniza automaticamente dados do usuário (nome, email, CPF, status de matrícula) e suporta controle de acesso baseado em papéis conforme dados institucionais.';
$string['auth_token_error'] = 'Ocorreu um erro ao tentar autenticar com o SUAP. O código de autorização pode ter expirado ou ter sido utilizado anteriormente.';
$string['auth_token_error_button'] = 'Reiniciar login via SUAP';
$string['auth_token_error_title'] = 'Falha na Autenticação com o SUAP';
$string['authorize_url'] = "Ponto de Acesso de Autorização OAuth2 do SUAP";
$string['authorize_url_desc'] = "URL do ponto de acesso de autorização OAuth2 do SUAP (tipicamente https://suap.ifrn.edu.br/o/authorize/)";
$string['bulk_updatepicture'] = 'Atualizar foto (SUAP)';
$string['bulk_updatepicture_confirm'] = 'Agendar, em segundo plano, a atualização da foto dos seguintes usuários a partir dos dados salvos do SUAP (sem novo login)? O processamento roda de forma assíncrona (tarefa ad hoc via cron) e não trava esta tela: {$a}?';
$string['bulk_updatepicture_result'] = 'Concluído: {$a->enfileirados} usuário(s) com dados de foto do SUAP tiveram a atualização agendada para processamento em segundo plano; {$a->semdados} usuário(s) não possuíam dados de foto salvos e foram ignorados.';
$string['client_id'] = 'ID do Cliente OAuth2';
$string['client_id_desc'] = "Obtenha no SUAP: Gestão de Tecnologia > Serviços > Aplicações OAuth2. Registre sua instância do Moodle com tipo de autorização 'Código de autorização' (cliente público) e defina o URI de redirecionamento para: {$CFG->wwwroot}/auth/suap/authenticate.php";
$string['client_secret'] = 'Segredo do Cliente OAuth2';
$string['client_secret_desc'] = "Este segredo é exibido apenas uma vez quando você cria a aplicação OAuth2 no SUAP. Salve imediatamente pois não pode ser recuperado depois. Para gerar um novo segredo, registre uma nova aplicação no SUAP.";
$string['configincomplete'] = 'As configurações do plugin SUAP (authorize_url, client_id, client_secret) estão incompletas ou não foram salvas na administração do site.';
$string['ensino_meus_dados_aluno_url'] = "Ponto de Acesso da API Ensino/Meus Dados Aluno do SUAP";
$string['ensino_meus_dados_aluno_url_desc'] = "Ponto de acesso da API do SUAP para recuperar dados acadêmicos do aluno (tipicamente https://suap.ifrn.edu.br/api/ensino/meus-dados-aluno/)";
$string['eventpictureupdatefailed'] = 'Falha ao atualizar foto de usuário (SUAP)';
$string['logout_url'] = "Ponto de Acesso de Logout do SUAP";
$string['logout_url_desc'] = "Ponto de acesso de logout do SUAP para encerrar a sessão (tipicamente https://suap.ifrn.edu.br/o/logout/)";
$string['name_source_order'] = 'Ordem de prioridade do nome de exibição';
$string['name_source_order_desc'] = 'Qual(is) campo(s) de nome do SUAP usar como fonte do nome completo no Moodle, e em qual ordem de prioridade. O nome de registro (nome_registro) é sempre usado como último fallback, por ser o único garantidamente presente.';
$string['name_source_order_registro'] = 'Apenas nome de registro';
$string['name_source_order_social_registro'] = 'Nome social, depois nome de registro';
$string['name_source_order_social_usual_registro'] = 'Nome social, depois nome usual, depois nome de registro';
$string['name_source_order_usual_registro'] = 'Nome usual, depois nome de registro';
$string['name_source_order_usual_social_registro'] = 'Nome usual, depois nome social, depois nome de registro';
$string['name_split_rule'] = 'Regra de divisão do nome';
$string['name_split_rule_desc'] = 'Como dividir o nome completo escolhido em firstname/lastname no Moodle.';
$string['name_split_rule_first_last'] = 'Primeira palavra + última palavra (nomes do meio são descartados)';
$string['name_split_rule_first_rest'] = 'Primeira palavra + todo o restante';
$string['name_split_rule_firsts_last'] = 'Tudo exceto a última palavra + última palavra';
$string['pluginname'] = 'Autenticação OAuth2 SUAP';
$string['privacy:metadata:suap:cpf'] = 'CPF do usuário (documento fiscal brasileiro)';
$string['privacy:metadata:suap:email'] = 'Endereço de email';
$string['privacy:metadata:suap:explanation'] = 'Este plugin se comunica com o serviço externo SUAP para autenticação de usuários e sincronização de dados. Informações do usuário incluindo nome de usuário, email, nome, CPF e informações de papel são enviadas para o SUAP durante login e processos regulares de sincronização.';
$string['privacy:metadata:suap:firstname'] = 'Primeiro nome do usuário';
$string['privacy:metadata:suap:lastname'] = 'Sobrenome do usuário';
$string['privacy:metadata:suap:tipo'] = 'Tipo/papel do usuário (aluno, servidor, professor, etc)';
$string['privacy:metadata:suap:username'] = 'Nome de usuário (ID institucional)';
$string['rh_eu_url'] = "Ponto de Acesso da API RH/EU do SUAP";
$string['rh_eu_url_desc'] = "Ponto de acesso da API do SUAP para recuperar dados de identificação e documentos pessoais do usuário (tipicamente https://suap.ifrn.edu.br/api/eu/)";
$string['rh_meus_dados_url'] = "Ponto de Acesso da API RH/Meus Dados do SUAP";
$string['rh_meus_dados_url_desc'] = "Ponto de acesso da API do SUAP para recuperar dados pessoais e vínculo principal do usuário (tipicamente https://suap.ifrn.edu.br/api/rh/meus-dados/)";
$string['rh_meus_vinculos_url'] = "Ponto de Acesso da API RH/Meus Vínculos do SUAP";
$string['rh_meus_vinculos_url_desc'] = "Ponto de acesso da API do SUAP para recuperar a lista de vínculos do usuário (tipicamente https://suap.ifrn.edu.br/api/rh/meus-vinculos/)";
$string['suap:updatepicture'] = 'Atualizar foto de usuários SUAP (ação em massa/tarefa agendada)';
$string['task_backfill_user_pictures'] = 'SUAP: preencher fotos de usuários sem foto';
$string['task_sync_user_names'] = 'SUAP: sincronizar nomes de exibição dos usuários (nome social/usual/registro)';
$string['task_update_user_picture_adhoc'] = 'SUAP: atualizar foto de um usuário (em segundo plano)';
$string['token_url'] = "Ponto de Acesso de Token OAuth2 do SUAP";
$string['token_url_desc'] = "URL do ponto de acesso de troca de token OAuth2 do SUAP (tipicamente https://suap.ifrn.edu.br/o/token/)";
