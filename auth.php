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
 * Authentication plugin for SUAP OAuth2.
 *
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

/**
 * SUAP authentication plugin class.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_suap extends auth_oauth2\auth {
    /** @var string Authentication type */
    public $authtype;

    /** @var string Role authentication name */
    public $roleauth;

    /** @var string Tag used in log messages */
    public $errorlogtag;

    /** @var stdClass Plugin configuration settings */
    public $config;

    /** @var stdClass|null User object */
    public $usuario;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'suap';
        $this->roleauth = 'auth_suap';
        $this->errorlogtag = '[AUTH SUAP] ';
        $this->config = get_auth_suap_config();
        $this->usuario = null;
    }

    /**
     * Can user change password (disabled for OAuth2).
     *
     * @return bool
     */
    public function can_change_password() {
        return false;
    }

    /**
     * Is this internal authentication?
     *
     * @return bool
     */
    public function is_internal() {
        return false;
    }

    /**
     * Post-logout hook.
     *
     * @param stdClass $user User object
     * @return int
     */
    public function postlogout_hook($user) {
        global $CFG;
        if ($user->auth != 'suap') {
            return 0;
        }
        redirect($CFG->wwwroot . '/auth/suap/logout.php');
    }

    /**
     * Initiate OAuth2 login redirect.
     *
     * @return void
     */
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
            $redirecturi = urlencode("$CFG->wwwroot/auth/suap/authenticate.php");
            $url = "{$this->config->authorize_url}?response_type=code&client_id={$this->config->client_id}"
                . "&redirect_uri=$redirecturi";
            redirect($url);
        }
    }

    /**
     * Handle authentication callback from SUAP.
     *
     * @return void
     */
    public function authenticate() {
        global $CFG, $USER;

        if (!empty($USER->id)) {
            redirect($CFG->wwwroot);
        }

        $code = required_param('code', PARAM_RAW);

        $tokenresponse = "";
        $userdataresponse = "";
        try {
            // Exchange code for access token.
            $tokenresponse = auth_suap_curl_post(
                $this->config->token_url,
                [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => "{$CFG->wwwroot}/auth/suap/authenticate.php",
                    'client_id' => $this->config->client_id,
                    'client_secret' => $this->config->client_secret,
                ]
            );
            $auth = json_decode($tokenresponse);
            if (empty($auth) || !is_object($auth) || empty($auth->access_token)) {
                $errordetails = '';
                if (is_object($auth)) {
                    $errordetails = $auth->error_description ?? ($auth->error ?? '');
                }
                throw new Exception("Resposta de token inválida do SUAP: " . ($errordetails ?: $tokenresponse));
            }

            // Get user data from SUAP (/api/rh/eu/).
            $rheuurl = !empty($this->config->rh_eu_url) ? $this->config->rh_eu_url : 'https://suap.ifrn.edu.br/api/rh/eu/';
            $headers = [
                "Authorization: Bearer {$auth->access_token}",
                "x-api-key: {$this->config->client_secret}",
                "Accept: application/json",
            ];

            $userdataresponse = auth_suap_curl_get(
                "{$rheuurl}?scope=" . urlencode('identificacao documentos_pessoais'),
                $headers
            );
            if (empty($userdataresponse) || strpos($userdataresponse, '"identificacao"') === false) {
                throw new Exception("Erro ao tentar obter dados do SUAP.");
            }

            $userdata = json_decode($userdataresponse);
            if (empty($userdata) || !is_object($userdata)) {
                throw new Exception("Dados de usuário retornados pelo SUAP são inválidos.");
            }

            $suapbase = getenv('SUAP_BASE_URL') ?: 'https://suap.ifrn.edu.br';

            // Get personal data from SUAP (/api/rh/meus-dados/).
            try {
                $meusdadosurl = !empty($this->config->rh_meus_dados_url) ?
                    $this->config->rh_meus_dados_url : "{$suapbase}/api/rh/meus-dados/";
                $meusdadosresponse = auth_suap_curl_get($meusdadosurl, $headers);
                if (!empty($meusdadosresponse)) {
                    $meusdados = json_decode($meusdadosresponse);
                    if (!empty($meusdados) && is_object($meusdados)) {
                        foreach ($meusdados as $key => $val) {
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
                debugging('[AUTH SUAP] Warning ao obter /api/rh/meus-dados/: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }

            // Get relationships list from SUAP (/api/rh/meus-vinculos/).
            try {
                $meusvinculosurl = !empty($this->config->rh_meus_vinculos_url) ?
                    $this->config->rh_meus_vinculos_url : "{$suapbase}/api/rh/meus-vinculos/";
                $meusvinculosresponse = auth_suap_curl_get($meusvinculosurl, $headers);
                if (!empty($meusvinculosresponse)) {
                    $meusvinculos = json_decode($meusvinculosresponse);
                    if (
                        !empty($meusvinculos) && is_object($meusvinculos)
                        && isset($meusvinculos->results) && is_array($meusvinculos->results)
                    ) {
                        $userdata->vinculos = $meusvinculos->results;
                    }
                }
            } catch (\Throwable $e) {
                debugging('[AUTH SUAP] Warning ao obter /api/rh/meus-vinculos/: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }

            // Get student data from SUAP (/api/ensino/meus-dados-aluno/).
            if (isset($userdata->tipo_usuario) && $userdata->tipo_usuario === 'Aluno') {
                try {
                    $ensinoalunourl = !empty($this->config->ensino_meus_dados_aluno_url) ?
                        $this->config->ensino_meus_dados_aluno_url : "{$suapbase}/api/ensino/meus-dados-aluno/";
                    $ensinoalunoresponse = auth_suap_curl_get($ensinoalunourl, $headers);
                    if (!empty($ensinoalunoresponse)) {
                        $ensinoaluno = json_decode($ensinoalunoresponse);
                        if (!empty($ensinoaluno) && is_object($ensinoaluno)) {
                            if (!isset($userdata->vinculo) || !is_object($userdata->vinculo)) {
                                $userdata->vinculo = new \stdClass();
                            }
                            foreach ($ensinoaluno as $key => $val) {
                                if (in_array($key, ['email_academico', 'email_escolar', 'cpf'])) {
                                    continue;
                                }
                                $userdata->vinculo->{$key} = $val;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    debugging('[AUTH SUAP] Warning ao obter /api/ensino/meus-dados-aluno/: ' . $e->getMessage(), DEBUG_DEVELOPER);
                }
            }

            $this->create_or_update_user($userdata);
        } catch (\Throwable $e) {
            // Log error for administrators.
            debugging('[AUTH SUAP] OAuth2 Authentication Error: ' . $e->getMessage(), DEBUG_DEVELOPER);

            // Display user-friendly error message.
            throw new \moodle_exception('auth_failure', 'auth_suap', '', null, $e->getMessage());
        }
    }

    /**
     * Create or update user account from SUAP user data.
     *
     * @param stdClass $userdata User data object from SUAP API.
     * @return void
     * @throws moodle_exception
     */
    public function create_or_update_user($userdata) {
        global $DB, $SESSION, $CFG;

        $identificador = !empty($userdata->identificacao) ?
            $userdata->identificacao : (!empty($userdata->matricula) ? $userdata->matricula : null);
        if (empty($identificador)) {
            throw new \moodle_exception('identificacao_ausente', 'auth_suap');
        }
        $username = strtolower($identificador);
        $usuario = $DB->get_record("user", ["username" => $username]);

        $parts = explode(' ', $userdata->nome_registro ?? '');
        $primeironome = implode(' ', array_slice($parts, 0, -1));
        $ultimonome = end($parts);
        $email = $userdata->email_preferencial ?? ($userdata->email ?? $userdata->email_secundario);

        if (!$usuario) {
            $usuario = (object)[
                'username' => $username,
                'idnumber' => $identificador,
                'firstname' => $primeironome,
                'lastname' => $ultimonome,
                'email' => $email,
                'auth' => 'suap',
                'suspended' => 0,
                'password' => '!aA1' . uniqid(),
                'timezone' => '99',
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

            $defaultprefs = get_config('local/suap', 'default_user_preferences');
            if ($defaultprefs) {
                foreach (preg_split('/\r\n|\r|\n/', $defaultprefs) as $preference) {
                    $parts = explode("=", $preference);
                    if (count($parts) == 2) {
                        \set_user_preference($parts[0], $parts[1], $usuario);
                    }
                }
            }
        }

        $usuario->firstname = $primeironome;
        $usuario->lastname = $userdata->ultimo_nome;
        $usuario->email = $email;
        $usuario->auth = 'suap';
        $usuario->suspended = 0;

        // Custom Profile Fields.
        $usuario->profile_field_nome_apresentacao = $userdata->nome_usual ?? null;
        $usuario->profile_field_nome_completo = $userdata->nome_registro ?? ($userdata->nome ?? null);
        $usuario->profile_field_nome_social = $userdata->nome_social ?? null;
        $usuario->profile_field_email_secundario = $userdata->email_secundario ?? null;
        $usuario->profile_field_email_google_classroom = $userdata->email_google_classroom ?? null;
        $usuario->profile_field_email_academico = $userdata->email_academico ?? null;
        $usuario->profile_field_campus_sigla = $userdata->campus ?? ($userdata->vinculo->campus ?? null);
        $usuario->profile_field_last_login = \json_encode($userdata);
        $usuario->profile_field_tipo_usuario = $userdata->tipo_usuario ?? null;

        $rawcpfdigits = preg_replace('/\D/', '', $userdata->cpf ?? '');
        $cpfunmasked = null;
        $cpfmasked = null;
        if ($rawcpfdigits !== '') {
            $cpfunmasked = str_pad($rawcpfdigits, 11, '0', STR_PAD_LEFT);
            $cpfmasked = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfunmasked);
        }

        $usuario->profile_field_data_de_nascimento = $userdata->data_nascimento ?? ($userdata->data_de_nascimento ?? null);
        $usuario->profile_field_sexo = $userdata->sexo ?? null;
        $usuario->profile_field_suap_id = $userdata->id ?? null;
        $usuario->profile_field_cpf = $cpfunmasked;
        $usuario->profile_field_rg = $userdata->rg ?? null;
        $usuario->profile_field_passaporte = $userdata->passaporte ?? null;
        $usuario->profile_field_naturalidade = $userdata->naturalidade ?? null;

        if (property_exists($userdata, 'filiacao') && is_array($userdata->filiacao)) {
            $usuario->profile_field_filiacao_mae = $userdata->filiacao[0] ?? null;
            $usuario->profile_field_filiacao_pai = $userdata->filiacao[1] ?? null;
        }

        if ($usuario->profile_field_cpf || $usuario->profile_field_passaporte) {
            $usuario->profile_field_id_doc_certificado = $usuario->profile_field_cpf ?
                $cpfmasked : $usuario->profile_field_passaporte;
            $usuario->profile_field_tipo_doc_certificado = $usuario->profile_field_cpf ? "CPF" : "Passaporte";
        }

        // Flags de vínculo.
        $tipovinculo = $userdata->tipo_vinculo ?? '';
        $tipousuario = $userdata->tipo_usuario ?? '';
        $usuario->profile_field_eh_servidor = $tipovinculo == 'Servidor';
        $usuario->profile_field_eh_aluno = $tipousuario === 'Aluno';
        $usuario->profile_field_eh_prestador = $tipovinculo === 'Prestador de Serviço';
        $usuario->profile_field_eh_usuarioexterno = $tipovinculo === 'Prestador de Serviço';

        // Vínculo equivalente & detalhamento.
        $vinculoequivalente = null;
        if (property_exists($userdata, 'vinculos') && is_array($userdata->vinculos) && !empty($userdata->vinculos)) {
            foreach ($userdata->vinculos as $v) {
                $vid = $v->identificador ?? ($v->matricula ?? null);
                if (!empty($vid) && $vid == $identificador) {
                    $vinculoequivalente = $v;
                    break;
                }
            }
        }

        if ($vinculoequivalente) {
            if (property_exists($vinculoequivalente, 'estrangeiro')) {
                $usuario->profile_field_eh_estrangeiro = $vinculoequivalente->estrangeiro;
            }
            $detalhamento = property_exists($vinculoequivalente, 'detalhamento') ?
                $vinculoequivalente->detalhamento : null;
            if ($detalhamento) {
                $usuario->profile_field_curso_modalidade = $detalhamento->modalidade ?? null;
                $usuario->profile_field_curso_nivel_ensino = $detalhamento->nivel_ensino ?? null;
                $usuario->profile_field_vinculo_ativo = $detalhamento->ativo ?? 0;
                $usuario->profile_field_vinculo_cargo = $detalhamento->cargo ?? null;
                $usuario->profile_field_vinculo_categoria = $detalhamento->categoria ?? null;
            }
        }

        // Vínculo corrente.
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

        $fotosources = [];
        if (!empty($userdata->url_foto_150x200)) {
            $fotosources[] = $userdata->url_foto_150x200;
        }
        if (!empty($userdata->url_foto_75x100)) {
            $fotosources[] = $userdata->url_foto_75x100;
        }
        if (!empty($userdata->foto)) {
            $fotosources[] = $userdata->foto;
        }
        if (!empty($fotosources)) {
            $this->update_picture($usuario, $fotosources);
        }

        $usuario = $DB->get_record("user", ["username" => $username]);

        complete_user_login($usuario);

        redirect($next);
    }

    /**
     * Fetch user photo from SUAP and update user picture in Moodle.
     *
     * @param stdClass $usuario User object.
     * @param array $fotosources List of photo URLs.
     * @return void
     */
    public function update_picture($usuario, $fotosources) {
        global $CFG, $DB;
        require_once($CFG->libdir . '/gdlib.php');

        if (!is_array($fotosources)) {
            $fotosources = [$fotosources];
        }

        $content = false;
        foreach ($fotosources as $url) {
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
            $tmpfilename = $CFG->tempdir . '/suapfoto' . $usuario->id;
            file_put_contents($tmpfilename, $content);
            $usuario->imagefile = process_new_icon(
                context_user::instance($usuario->id, MUST_EXIST),
                'user',
                'icon',
                0,
                $tmpfilename
            );
            if ($usuario->imagefile) {
                $DB->set_field('user', 'picture', $usuario->imagefile, ['id' => $usuario->id]);
            }
        }
    }

    /**
     * Get user info as an associative array.
     *
     * @param string $username Username.
     * @return array User attributes array.
     */
    public function get_userinfo($username) {
        return get_object_vars($this->usuario);
    }
}
