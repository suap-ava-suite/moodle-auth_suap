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

$string['auth_description'] = 'OAuth2 认证';
$string['auth_suap_description'] = 'SUAP 是巴西联邦机构（包括北里奥格兰德联邦学院 IFRN）广泛使用的公共管理统一系统（Sistema Unificado de Administração Pública）。该插件支持单点登录（SSO）集成，允许学生和教职员工使用其 SUAP 账号登录 Moodle。它会自动从 SUAP 同步用户数据（姓名、邮箱、CPF、注册状态），并支持基于机构数据的角色访问控制。';
$string['auth_token_error'] = '尝试通过 SUAP 进行身份验证时发生错误。授权码可能已过期或已被使用。';
$string['auth_token_error_button'] = '通过 SUAP 重新登录';
$string['auth_token_error_title'] = 'SUAP 身份验证失败';
$string['authorize_url'] = 'SUAP 授权端点';
$string['authorize_url_desc'] = "SUAP OAuth2 授权 URL（通常为 https://suap.ifrn.edu.br/o/authorize/）";
$string['bulk_updatepicture'] = '更新头像（SUAP）';
$string['bulk_updatepicture_confirm'] = '是否根据已保存的 SUAP 数据（无需重新登录），为以下用户安排后台头像更新？处理将异步进行（通过 cron 执行的临时任务），不会阻塞此页面：{$a}？';
$string['bulk_updatepicture_result'] = '完成：{$a->enfileirados} 个拥有 SUAP 头像数据的用户已被安排在后台更新；{$a->semdados} 个用户没有已保存的头像数据，已被跳过。';
$string['client_id'] = 'OAuth2 客户端 ID';
$string['client_id_desc'] = "在 SUAP 中获取：技术管理 > 服务 > OAuth2 应用程序。使用授权类型「授权码」（公共客户端）注册您的 Moodle 实例，并将重定向 URI 设置为：{$CFG->wwwroot}/auth/suap/authenticate.php";
$string['client_secret'] = 'OAuth2 客户端密钥';
$string['client_secret_desc'] = "此密钥仅在您于 SUAP 中创建 OAuth2 应用程序时显示一次。请立即保存，因为之后将无法再次获取。如需生成新密钥，请在 SUAP 中重新注册一个应用程序。";
$string['configincomplete'] = 'SUAP 插件的配置（authorize_url、client_id、client_secret）不完整，或尚未在网站管理中保存。';
$string['ensino_meus_dados_aluno_url'] = 'SUAP Ensino/Meus Dados Aluno API 端点';
$string['ensino_meus_dados_aluno_url_desc'] = "用于获取学生学籍数据的 SUAP API 端点（通常为 https://suap.ifrn.edu.br/api/ensino/meus-dados-aluno/）";
$string['eventpictureupdatefailed'] = '用户头像更新失败（SUAP）';
$string['logout_url'] = 'SUAP 注销 URL';
$string['logout_url_desc'] = "用于终止会话的 SUAP 注销端点（通常为 https://suap.ifrn.edu.br/o/logout/）";
$string['name_source_order'] = '显示姓名来源优先级';
$string['name_source_order_desc'] = '选择使用哪个（些）SUAP 姓名字段作为 Moodle 全名的来源，以及优先顺序。注册姓名（nome_registro）始终作为最终后备项，因为它是唯一保证存在的字段。';
$string['name_source_order_registro'] = '仅使用注册姓名';
$string['name_source_order_social_registro'] = '社会姓名，其次是注册姓名';
$string['name_source_order_social_usual_registro'] = '社会姓名，其次是常用姓名，最后是注册姓名';
$string['name_source_order_usual_registro'] = '常用姓名，其次是注册姓名';
$string['name_source_order_usual_social_registro'] = '常用姓名，其次是社会姓名，最后是注册姓名';
$string['name_split_rule'] = '姓名拆分规则';
$string['name_split_rule_desc'] = '如何将所选的全名拆分为 Moodle 的名字/姓氏。';
$string['name_split_rule_first_last'] = '第一个词 + 最后一个词（中间的名字将被舍弃）';
$string['name_split_rule_first_rest'] = '第一个词 + 其余全部';
$string['name_split_rule_firsts_last'] = '除最后一个词外的全部 + 最后一个词';
$string['pluginname'] = 'SUAP OAuth2 认证';
$string['privacy:metadata:suap:cpf'] = '用户 CPF（巴西税务证件号码）';
$string['privacy:metadata:suap:email'] = '电子邮箱地址';
$string['privacy:metadata:suap:explanation'] = '该插件与外部 SUAP 服务通信，用于用户身份验证和数据同步。用户信息，包括用户名、电子邮箱、姓名、CPF 和角色信息，会在登录及定期同步过程中发送给 SUAP。';
$string['privacy:metadata:suap:firstname'] = '用户名字';
$string['privacy:metadata:suap:lastname'] = '用户姓氏';
$string['privacy:metadata:suap:tipo'] = '用户类型/角色（学生、教职员工、教师等）';
$string['privacy:metadata:suap:username'] = '用户名（机构 ID）';
$string['rh_eu_url'] = 'SUAP RH/EU API 端点';
$string['rh_eu_url_desc'] = "用于获取用户身份信息和个人证件数据的 SUAP API 端点（通常为 https://suap.ifrn.edu.br/api/eu/）";
$string['rh_meus_dados_url'] = 'SUAP RH/Meus Dados API 端点';
$string['rh_meus_dados_url_desc'] = "用于获取用户个人数据及主要关联关系的 SUAP API 端点（通常为 https://suap.ifrn.edu.br/api/rh/meus-dados/）";
$string['rh_meus_vinculos_url'] = 'SUAP RH/Meus Vínculos API 端点';
$string['rh_meus_vinculos_url_desc'] = "用于获取用户关联关系列表的 SUAP API 端点（通常为 https://suap.ifrn.edu.br/api/rh/meus-vinculos/）";
$string['suap:updatepicture'] = '更新 SUAP 用户头像（批量操作/计划任务）';
$string['task_backfill_user_pictures'] = 'SUAP：为缺少头像的用户补充头像';
$string['task_sync_user_names'] = 'SUAP：同步用户显示姓名（社会姓名/常用姓名/注册姓名）';
$string['task_update_user_picture_adhoc'] = 'SUAP：更新单个用户的头像（后台）';
$string['token_url'] = 'SUAP 令牌端点';
$string['token_url_desc'] = "SUAP OAuth2 令牌交换 URL（通常为 https://suap.ifrn.edu.br/o/token/）";
