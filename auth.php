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
 *
 * @category    auth
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/user/lib.php");
require_once("$CFG->dirroot/user/profile/lib.php");
require_once("$CFG->dirroot/lib/authlib.php");
require_once("$CFG->dirroot/lib/classes/user.php");
require_once("$CFG->dirroot/auth/suap/locallib.php");


class auth_plugin_suap extends auth_oauth2\auth
{
    public $authtype;
    public $roleauth;
    public $errorlogtag;
    public $config;
    public $usuario;

    public function __construct() {
        $this->authtype = 'suap';
        $this->roleauth = 'auth_suap';
        $this->errorlogtag = '[AUTH SUAP] ';
        $this->config = get_auth_suap_config();
        $this->usuario = null;
    }

    public function user_login($username, $password) {
        return false;
    }

    public function can_change_password() {
        return false;
    }

    public function is_internal() {
        return false;
    }

    function postlogout_hook($user) {
        global $CFG;
        if ($user->auth != 'suap') {
            return 0;
        }
        $config = get_auth_suap_config();
        redirect($CFG->wwwroot . '/auth/suap/logout.php');
    }

    public function login() {
        global $CFG, $USER, $SESSION;

        $next = optional_param('next', '', PARAM_LOCALURL);
        if (empty($next)) {
            if (property_exists($SESSION, 'wantsurl')) {
                $next = $SESSION->wantsurl;
            } else {
                $next = $CFG->wwwroot;
            }
        }

        if ($USER->id) {
            redirect($next);
        } else {
            $SESSION->next_after_next = $next;
            $redirect_uri = urlencode("$CFG->wwwroot/auth/suap/authenticate.php");
            redirect("{$this->config->authorize_url}?response_type=code&client_id={$this->config->client_id}&redirect_uri=$redirect_uri");
        }
    }

    public function authenticate() {
        global $CFG, $USER;

        if (!empty($USER->id)) {
            redirect($CFG->wwwroot);
        }

        $code = required_param('code', PARAM_RAW);

        $token_response = "";
        $user_data_response = "";
        try {
            // Exchange code for access token
            $token_response = auth_suap_curl_post(
                $this->config->token_url,
                [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => "{$CFG->wwwroot}/auth/suap/authenticate.php",
                    'client_id' => $this->config->client_id,
                    'client_secret' => $this->config->client_secret,
                ]
            );
            $auth = json_decode($token_response);
            if (empty($auth) || !is_object($auth) || empty($auth->access_token)) {
                $error_details = '';
                if (is_object($auth)) {
                    $error_details = $auth->error_description ?? ($auth->error ?? '');
                }
                throw new Exception("Resposta de token inválida do SUAP: " . ($error_details ?: $token_response));
            }

            // Get user data from SUAP (/api/rh/eu/)
            $rh_eu_url = !empty($this->config->rh_eu_url) ? $this->config->rh_eu_url : 'https://suap.ifrn.edu.br/api/rh/eu/';
            $headers = [
                "Authorization: Bearer {$auth->access_token}",
                "x-api-key: {$this->config->client_secret}",
                "Accept: application/json",
            ];

            $user_data_response = auth_suap_curl_get(
                "{$rh_eu_url}?scope=" . urlencode('identificacao documentos_pessoais'),
                $headers
            );
            if (empty($user_data_response) || strpos($user_data_response, '"identificacao"') === false) {
                throw new Exception("Erro ao tentar obter dados do SUAP.");
            }

            $userdata = json_decode($user_data_response);
            if (empty($userdata) || !is_object($userdata)) {
                throw new Exception("Dados de usuário retornados pelo SUAP são inválidos.");
            }

            $suap_base = getenv('SUAP_BASE_URL') ?: 'https://suap.ifrn.edu.br';

            // Get personal data from SUAP (/api/rh/meus-dados/)
            try {
                $meus_dados_url = !empty($this->config->rh_meus_dados_url) ? $this->config->rh_meus_dados_url : "{$suap_base}/api/rh/meus-dados/";
                $meus_dados_response = auth_suap_curl_get($meus_dados_url, $headers);
                if (!empty($meus_dados_response)) {
                    $meus_dados = json_decode($meus_dados_response);
                    if (!empty($meus_dados) && is_object($meus_dados)) {
                        foreach ($meus_dados as $key => $val) {
                            if ($key === 'tipo_sanguineo') {
                                continue;
                            }
                            if (!property_exists($userdata, $key) || (empty($userdata->{$key}) && !empty($val))) {
                                $userdata->{$key} = $val;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log('[AUTH SUAP] Warning ao obter /api/rh/meus-dados/: ' . $e->getMessage());
            }

            // Get relationships list from SUAP (/api/rh/meus-vinculos/)
            try {
                $meus_vinculos_url = !empty($this->config->rh_meus_vinculos_url) ? $this->config->rh_meus_vinculos_url : "{$suap_base}/api/rh/meus-vinculos/";
                $meus_vinculos_response = auth_suap_curl_get($meus_vinculos_url, $headers);
                if (!empty($meus_vinculos_response)) {
                    $meus_vinculos = json_decode($meus_vinculos_response);
                    if (!empty($meus_vinculos) && is_object($meus_vinculos) && isset($meus_vinculos->results) && is_array($meus_vinculos->results)) {
                        $userdata->vinculos = $meus_vinculos->results;
                    }
                }
            } catch (\Throwable $e) {
                error_log('[AUTH SUAP] Warning ao obter /api/rh/meus-vinculos/: ' . $e->getMessage());
            }

            // Get student data from SUAP (/api/ensino/meus-dados-aluno/)
            if (isset($userdata->tipo_usuario) && $userdata->tipo_usuario === 'Aluno') {
                try {
                    $ensino_aluno_url = !empty($this->config->ensino_meus_dados_aluno_url) ? $this->config->ensino_meus_dados_aluno_url : "{$suap_base}/api/ensino/meus-dados-aluno/";
                    $ensino_aluno_response = auth_suap_curl_get($ensino_aluno_url, $headers);
                    if (!empty($ensino_aluno_response)) {
                        $ensino_aluno = json_decode($ensino_aluno_response);
                        if (!empty($ensino_aluno) && is_object($ensino_aluno)) {
                            if (!isset($userdata->vinculo) || !is_object($userdata->vinculo)) {
                                $userdata->vinculo = new \stdClass();
                            }
                            foreach ($ensino_aluno as $key => $val) {
                                if (in_array($key, ['email_academico', 'email_escolar', 'cpf'])) {
                                    continue;
                                }
                                $userdata->vinculo->{$key} = $val;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    error_log('[AUTH SUAP] Warning ao obter /api/ensino/meus-dados-aluno/: ' . $e->getMessage());
                }
            }

            $this->create_or_update_user($userdata);
        } catch (\Throwable $e) {
            // Log error for administrators
            error_log('[AUTH SUAP] OAuth2 Authentication Error: ' . $e->getMessage());

            // Display user-friendly error message
            print_error('auth_failure', 'auth_suap', '', null, $e->getMessage());
            die();
        }
    }

    function create_or_update_user($userdata) {
        global $DB, $SESSION, $CFG;

        $identificador = !empty($userdata->identificacao) ? $userdata->identificacao : (!empty($userdata->matricula) ? $userdata->matricula : null);
        if (empty($identificador)) {
            echo "<p>Erro ao integrar com o SUAP: identificação ou matrícula ausente.</p>";
            echo "<pre style='display: None'>";
            var_dump($userdata);
            echo "</pre>";
            die();
        }
        $username = strtolower($identificador);
        $usuario = $DB->get_record("user", ["username" => $username]);

        $parts = explode(' ', $userdata->nome_registro ?? '');
        $primeiro_nome = implode(' ', array_slice($parts, 0, -1));
        $ultimo_nome = end($parts);
        $email = $userdata->email_preferencial ?? ($userdata->email ?? $userdata->email_secundario);

        if (!$usuario) {
            $usuario = (object)[
                'username' => $username,
                'idnumber' => $identificador,
                'firstname' => $primeiro_nome,
                'lastname' => $ultimo_nome,
                'email' => $email,
                'auth' => 'suap',
                'suspended' => 0,
                'password' => '!aA1' . uniqid(),
                'timezone' => '99',
                // 'lang'=>'pt_br',
                'confirmed' => 1,
                'mnethostid' => 1,
                'policyagreed' => 0,
                'deleted' => 0,
                'firstaccess' => time(),
                'currentlogin' => time(),
                'lastip' => getremoteaddr(),
                'firstnamephonetic' => null,
                'lastnamephonetic' => null,
                'middlename' => null,
                'alternatename' => null,
            ];
            $usuario->id = \user_create_user($usuario);

            $default_user_preferences = get_config('local/suap', 'default_user_preferences');
            if ($default_user_preferences) {
                foreach (preg_split('/\r\n|\r|\n/', $default_user_preferences) as $preference) {
                    $parts = explode("=", $preference);
                    if (count($parts) == 2) {
                        \set_user_preference($parts[0], $parts[1], $usuario);
                    }
                }
            }
        }

        $usuario->firstname = $primeiro_nome;
        $usuario->lastname = $userdata->ultimo_nome;
        $usuario->email = $email;
        $usuario->auth = 'suap';
        $usuario->suspended = 0;

        // Custom Profile Fields
        $usuario->profile_field_nome_apresentacao = $userdata->nome_usual ?? null;
        $usuario->profile_field_nome_completo = $userdata->nome_registro ?? ($userdata->nome ?? null);
        $usuario->profile_field_nome_social = $userdata->nome_social ?? null;
        $usuario->profile_field_email_secundario = $userdata->email_secundario ?? null;
        $usuario->profile_field_email_google_classroom = $userdata->email_google_classroom ?? null;
        $usuario->profile_field_email_academico = $userdata->email_academico ?? null;
        $usuario->profile_field_campus_sigla = $userdata->campus ?? ($userdata->vinculo->campus ?? null);
        $usuario->profile_field_last_login = \json_encode($userdata);
        $usuario->profile_field_tipo_usuario = $userdata->tipo_usuario ?? null;

        $raw_cpf_digits = preg_replace('/\D/', '', $userdata->cpf ?? '');
        $cpf_unmasked = null;
        $cpf_masked = null;
        if ($raw_cpf_digits !== '') {
            $cpf_unmasked = str_pad($raw_cpf_digits, 11, '0', STR_PAD_LEFT);
            $cpf_masked = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf_unmasked);
        }

        $usuario->profile_field_data_de_nascimento = $userdata->data_nascimento ?? ($userdata->data_de_nascimento ?? null);
        $usuario->profile_field_sexo = $userdata->sexo ?? null;
        $usuario->profile_field_suap_id = $userdata->id ?? null;
        $usuario->profile_field_cpf = $cpf_unmasked;
        $usuario->profile_field_rg = $userdata->rg ?? null;
        $usuario->profile_field_passaporte = $userdata->passaporte ?? null;
        $usuario->profile_field_naturalidade = $userdata->naturalidade ?? null;

        if (property_exists($userdata, 'filiacao') && is_array($userdata->filiacao)) {
            $usuario->profile_field_filiacao_mae = $userdata->filiacao[0] ?? null;
            $usuario->profile_field_filiacao_pai = $userdata->filiacao[1] ?? null;
        }

        if ($usuario->profile_field_cpf || $usuario->profile_field_passaporte) {
            $usuario->profile_field_id_doc_certificado = $usuario->profile_field_cpf ? $cpf_masked : $usuario->profile_field_passaporte;
            $usuario->profile_field_tipo_doc_certificado = $usuario->profile_field_cpf ? "CPF" : "Passaporte";
        }

        // Flags de vínculo
        $tipo_vinculo = $userdata->tipo_vinculo ?? '';
        $tipo_usuario = $userdata->tipo_usuario ?? '';
        $usuario->profile_field_eh_servidor = $tipo_vinculo == 'Servidor';
        $usuario->profile_field_eh_aluno = $tipo_usuario === 'Aluno';
        $usuario->profile_field_eh_prestador = $tipo_vinculo === 'Prestador de Serviço';
        $usuario->profile_field_eh_usuarioexterno = $tipo_vinculo === 'Prestador de Serviço';

        // Vínculo equivalente & detalhamento
        $vinculo_equivalente = null;
        if (property_exists($userdata, 'vinculos') && is_array($userdata->vinculos) && !empty($userdata->vinculos)) {
            foreach ($userdata->vinculos as $v) {
                $v_id = $v->identificador ?? ($v->matricula ?? null);
                if (!empty($v_id) && $v_id == $identificador) {
                    $vinculo_equivalente = $v;
                    break;
                }
            }
        }

        if ($vinculo_equivalente) {
            if (property_exists($vinculo_equivalente, 'estrangeiro')) {
                $usuario->profile_field_eh_estrangeiro = $vinculo_equivalente->estrangeiro;
            }
            $detalhamento = property_exists($vinculo_equivalente, 'detalhamento') ? $vinculo_equivalente->detalhamento : null;
            if ($detalhamento) {
                $usuario->profile_field_curso_modalidade = $detalhamento->modalidade ?? null;
                $usuario->profile_field_curso_nivel_ensino = $detalhamento->nivel_ensino ?? null;
                $usuario->profile_field_vinculo_ativo = $detalhamento->ativo ?? 0;
                $usuario->profile_field_vinculo_cargo = $detalhamento->cargo ?? null;
                $usuario->profile_field_vinculo_categoria = $detalhamento->categoria ?? null;
            }
        }

        // Vínculo corrente
        if (property_exists($userdata, 'vinculo') && is_object($userdata->vinculo)) {
            $usuario->profile_field_matricula_regular = $userdata->vinculo->matricula_regular ?? 0;
            $usuario->profile_field_situacao_vinculo = $userdata->vinculo->situacao ?? null;
            $usuario->profile_field_situacao_sistemica = $userdata->vinculo->situacao_sistemica ?? null;
            $usuario->profile_field_ira = $userdata->vinculo->ira ?? null;
            $usuario->profile_field_matriz_curricular = $userdata->vinculo->matriz ?? null;
            $usuario->profile_field_ingresso_periodo = $userdata->vinculo->ingresso ?? null;
            $usuario->profile_field_curso_descricao = $userdata->vinculo->curso ?? null;
            $usuario->profile_field_turno = $userdata->vinculo->turno ?? null;

            if (empty($usuario->profile_field_vinculo_cargo) && !empty($userdata->vinculo->cargo)) {
                $usuario->profile_field_vinculo_cargo = $userdata->vinculo->cargo;
            }
            if (empty($usuario->profile_field_vinculo_categoria) && !empty($userdata->vinculo->categoria)) {
                $usuario->profile_field_vinculo_categoria = $userdata->vinculo->categoria;
            }

            if (!empty($userdata->vinculo->campus) && !empty($userdata->vinculo->curso)) {
                $usuario->profile_field_campus_curso = $userdata->vinculo->campus . ': ' . $userdata->vinculo->curso;
            } else {
                $usuario->profile_field_campus_curso = null;
            }
        }

        $this->usuario = $usuario;
        $next = !empty($SESSION->next_after_next) ? $SESSION->next_after_next : $CFG->wwwroot;
        unset($SESSION->next_after_next);

        $this->update_user_record($this->usuario->username);

        $foto_sources = [];
        if (!empty($userdata->url_foto_150x200)) {
            $foto_sources[] = $userdata->url_foto_150x200;
        }
        if (!empty($userdata->url_foto_75x100)) {
            $foto_sources[] = $userdata->url_foto_75x100;
        }
        if (!empty($userdata->foto)) {
            $foto_sources[] = $userdata->foto;
        }
        if (!empty($foto_sources)) {
            $this->update_picture($usuario, $foto_sources);
        }

        $usuario = $DB->get_record("user", ["username" => $username]);

        complete_user_login($usuario);

        redirect($next);
    }

    function update_picture($usuario, $foto_sources) {
        global $CFG, $DB;
        require_once($CFG->libdir . '/gdlib.php');

        if (!is_array($foto_sources)) {
            $foto_sources = [$foto_sources];
        }

        $content = false;
        foreach ($foto_sources as $url) {
            if (!empty($url)) {
                try {
                    $content = auth_suap_curl_get($url, [], 5);
                    if ($content !== false && strlen($content) > 0) {
                        break;
                    }
                } catch (\Throwable $e) {
                    $content = false;
                }
            }
        }

        if ($content !== false && strlen($content) > 0) {
            $tmp_filename = $CFG->tempdir . '/suapfoto' . $usuario->id;
            file_put_contents($tmp_filename, $content);
            $usuario->imagefile = process_new_icon(context_user::instance($usuario->id, MUST_EXIST), 'user', 'icon', 0, $tmp_filename);
            if ($usuario->imagefile) {
                $DB->set_field('user', 'picture', $usuario->imagefile, ['id' => $usuario->id]);
            }
        }
    }

    function get_userinfo($username) {
        return get_object_vars($this->usuario);
    }
}
