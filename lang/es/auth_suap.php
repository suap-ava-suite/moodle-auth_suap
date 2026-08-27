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

$string['auth_description'] = 'Autenticación OAuth2';
$string['auth_suap_description'] = 'SUAP es el Sistema Unificado de Administración Pública (Sistema Unificado de Administração Pública) utilizado en instituciones federales brasileñas, incluyendo el Instituto Federal de Rio Grande do Norte (IFRN). Este complemento permite la integración de inicio de sesión único (SSO), permitiendo que estudiantes y personal accedan a Moodle usando sus credenciales de SUAP. Sincroniza automáticamente los datos del usuario desde SUAP (nombre, correo electrónico, CPF, estado de matrícula) y admite control de acceso basado en roles según los datos institucionales.';
$string['auth_token_error'] = 'Se produjo un error al intentar autenticarse con SUAP. El código de autorización puede haber expirado o ya haber sido utilizado.';
$string['auth_token_error_button'] = 'Reiniciar el inicio de sesión vía SUAP';
$string['auth_token_error_title'] = 'Fallo de autenticación con SUAP';
$string['authorize_url'] = 'Punto de acceso de autorización OAuth2 de SUAP';
$string['authorize_url_desc'] = "URL de autorización OAuth2 de SUAP (normalmente https://suap.ifrn.edu.br/o/authorize/)";
$string['bulk_updatepicture'] = 'Actualizar foto (SUAP)';
$string['bulk_updatepicture_confirm'] = '¿Programar en segundo plano la actualización de la foto de los siguientes usuarios, a partir de los datos de SUAP ya guardados (sin necesidad de un nuevo inicio de sesión)? El procesamiento se ejecuta de forma asíncrona (una tarea ad hoc vía cron) y no bloqueará esta pantalla: {$a}?';
$string['bulk_updatepicture_result'] = 'Completado: {$a->enfileirados} usuario(s) con datos de foto de SUAP tuvieron una actualización en segundo plano programada; {$a->semdados} usuario(s) no tenían datos de foto guardados y fueron omitidos.';
$string['client_id'] = 'ID de cliente OAuth2';
$string['client_id_desc'] = "Obténgalo en SUAP: Gestión de Tecnología > Servicios > Aplicaciones OAuth2. Registre su instancia de Moodle con el tipo de autorización 'Código de autorización' (cliente público) y configure el URI de redirección como: {$CFG->wwwroot}/auth/suap/authenticate.php";
$string['client_secret'] = 'Secreto de cliente OAuth2';
$string['client_secret_desc'] = "Este secreto se muestra solo una vez al crear la aplicación OAuth2 en SUAP. Guárdelo de inmediato, ya que no se puede recuperar después. Para generar un nuevo secreto, registre una nueva aplicación en SUAP.";
$string['configincomplete'] = 'Las configuraciones del complemento SUAP (authorize_url, client_id, client_secret) están incompletas o no se han guardado en la administración del sitio.';
$string['ensino_meus_dados_aluno_url'] = 'Punto de acceso de la API Ensino/Meus Dados Aluno de SUAP';
$string['ensino_meus_dados_aluno_url_desc'] = "Punto de acceso de la API de SUAP para obtener los datos académicos del estudiante (normalmente https://suap.ifrn.edu.br/api/ensino/meus-dados-aluno/)";
$string['eventpictureupdatefailed'] = 'Fallo al actualizar la foto del usuario (SUAP)';
$string['logout_url'] = 'URL de cierre de sesión de SUAP';
$string['logout_url_desc'] = "Punto de acceso de cierre de sesión de SUAP para finalizar la sesión (normalmente https://suap.ifrn.edu.br/o/logout/)";
$string['name_source_order'] = 'Orden de prioridad del nombre para mostrar';
$string['name_source_order_desc'] = 'Qué campo(s) de nombre de SUAP usar como fuente del nombre completo en Moodle, y en qué orden de prioridad. El nombre de registro (nome_registro) siempre se usa como último recurso, ya que es el único que existe garantizadamente.';
$string['name_source_order_registro'] = 'Solo nombre de registro';
$string['name_source_order_social_registro'] = 'Nombre social y luego nombre de registro';
$string['name_source_order_social_usual_registro'] = 'Nombre social, luego nombre habitual, luego nombre de registro';
$string['name_source_order_usual_registro'] = 'Nombre habitual y luego nombre de registro';
$string['name_source_order_usual_social_registro'] = 'Nombre habitual, luego nombre social, luego nombre de registro';
$string['name_split_rule'] = 'Regla de división del nombre';
$string['name_split_rule_desc'] = 'Cómo dividir el nombre completo elegido en nombre/apellido de Moodle.';
$string['name_split_rule_first_last'] = 'Primera palabra + última palabra (se descartan los nombres intermedios)';
$string['name_split_rule_first_rest'] = 'Primera palabra + todo lo demás';
$string['name_split_rule_firsts_last'] = 'Todo excepto la última palabra + última palabra';
$string['pluginname'] = 'Autenticación OAuth2 de SUAP';
$string['privacy:metadata:suap:cpf'] = 'CPF del usuario (documento fiscal brasileño)';
$string['privacy:metadata:suap:email'] = 'Dirección de correo electrónico';
$string['privacy:metadata:suap:explanation'] = 'Este complemento se comunica con el servicio externo SUAP para la autenticación de usuarios y la sincronización de datos. La información del usuario, incluyendo nombre de usuario, correo electrónico, nombre, CPF e información de rol, se envía a SUAP durante el inicio de sesión y los procesos periódicos de sincronización.';
$string['privacy:metadata:suap:firstname'] = 'Nombre del usuario';
$string['privacy:metadata:suap:lastname'] = 'Apellido del usuario';
$string['privacy:metadata:suap:tipo'] = 'Tipo/rol del usuario (estudiante, personal, docente, etc.)';
$string['privacy:metadata:suap:username'] = 'Nombre de usuario (ID institucional)';
$string['rh_eu_url'] = 'Punto de acceso de la API RH/EU de SUAP';
$string['rh_eu_url_desc'] = "Punto de acceso de la API de SUAP para obtener los datos de identificación y documentos personales del usuario (normalmente https://suap.ifrn.edu.br/api/eu/)";
$string['rh_meus_dados_url'] = 'Punto de acceso de la API RH/Meus Dados de SUAP';
$string['rh_meus_dados_url_desc'] = "Punto de acceso de la API de SUAP para obtener los datos personales y el vínculo principal del usuario (normalmente https://suap.ifrn.edu.br/api/rh/meus-dados/)";
$string['rh_meus_vinculos_url'] = 'Punto de acceso de la API RH/Meus Vínculos de SUAP';
$string['rh_meus_vinculos_url_desc'] = "Punto de acceso de la API de SUAP para obtener la lista de vínculos del usuario (normalmente https://suap.ifrn.edu.br/api/rh/meus-vinculos/)";
$string['suap:updatepicture'] = 'Actualizar fotos de usuarios de SUAP (acción masiva/tarea programada)';
$string['task_backfill_user_pictures'] = 'SUAP: completar fotos de usuarios sin foto';
$string['task_sync_user_names'] = 'SUAP: sincronizar nombres para mostrar de los usuarios (nombre social/habitual/registro)';
$string['task_update_user_picture_adhoc'] = 'SUAP: actualizar la foto de un usuario (en segundo plano)';
$string['token_url'] = 'Punto de acceso de token de SUAP';
$string['token_url_desc'] = "URL de intercambio de token OAuth2 de SUAP (normalmente https://suap.ifrn.edu.br/o/token/)";
