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
        auth_suap_redirect($CFG->wwwroot . '/auth/suap/logout.php');
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
            auth_suap_redirect($next);
        } else {
            if (empty($this->config->authorize_url) || empty($this->config->client_id)) {
                throw new \moodle_exception('configincomplete', 'auth_suap');
            }
            $SESSION->next_after_next = $next;
            $redirecturi = urlencode("$CFG->wwwroot/auth/suap/authenticate.php");
            $url = "{$this->config->authorize_url}?response_type=code&client_id={$this->config->client_id}"
                . "&redirect_uri=$redirecturi";
            auth_suap_redirect($url);
        }
    }

    /**
     * Autentica o token no SUAP.
     *
     * @return array Headers de autenticação com o Bearer token
     */
    public function authenticate_token() {
        global $CFG;

        try {
            $code = required_param('code', PARAM_RAW);
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
            return [
                "Authorization: Bearer {$auth->access_token}",
                "x-api-key: {$this->config->client_secret}",
                "Accept: application/json",
            ];
        } catch (\Throwable $e) {
            global $OUTPUT, $PAGE;
            debugging('[AUTH SUAP] Erro ao obter token do SUAP: ' . $e->getMessage(), DEBUG_DEVELOPER);

            $PAGE->set_context(\context_system::instance());
            $PAGE->set_title(get_string('auth_token_error_title', 'auth_suap'));
            $PAGE->set_heading(get_string('auth_token_error_title', 'auth_suap'));

            $templatecontext = [
                'title' => get_string('auth_token_error_title', 'auth_suap'),
                'message' => get_string('auth_token_error', 'auth_suap'),
                'loginurl' => (new \moodle_url('/auth/suap/login.php'))->out(false),
                'buttontext' => get_string('auth_token_error_button', 'auth_suap'),
            ];

            echo $OUTPUT->header();
            echo $OUTPUT->render_from_template('auth_suap/auth_error', $templatecontext);
            echo $OUTPUT->footer();
            exit;
        }
    }

    /**
     * Get user data from SUAP (/api/rh/eu/).
     *
     * @param array $credentials Credentials with access token
     * @return stdClass Object with user data
     */
    public function get_user_info_rh_eu($credentials) {
        $rheuurl = !empty($this->config->rh_eu_url) ? $this->config->rh_eu_url : 'https://suap.ifrn.edu.br/api/rh/eu/';
        $userdataresponse = auth_suap_curl_get(
            "{$rheuurl}?scope=" . urlencode('identificacao documentos_pessoais'),
            $credentials
        );
        if (empty($userdataresponse) || strpos($userdataresponse, '"identificacao"') === false) {
            throw new Exception("Erro ao tentar obter dados do SUAP.");
        }
        $userdata = json_decode($userdataresponse);
        if (empty($userdata) || !is_object($userdata)) {
            throw new Exception("Dados de usuário retornados pelo SUAP são inválidos.");
        }
        return $userdata;
    }

    /**
     * Get user personal data from SUAP (/api/rh/meus-dados/).
     *
     * @param array $credentials Credentials with access token
     * @return stdClass Object with user personal data
     */
    public function get_user_info_rh_meus_dados($credentials) {
        $meusdadosurl = !empty($this->config->rh_meus_dados_url) ?
            $this->config->rh_meus_dados_url : 'https://suap.ifrn.edu.br/api/rh/meus-dados/';
        $meusdadosresponse = auth_suap_curl_get($meusdadosurl, $credentials);
        if (empty($meusdadosresponse)) {
            throw new Exception("Erro ao tentar obter dados pessoais do SUAP.");
        }
        $meusdados = json_decode($meusdadosresponse);
        if (empty($meusdados) || !is_object($meusdados)) {
            throw new Exception("Dados pessoais de usuário retornados pelo SUAP são inválidos.");
        }
        unset($meusdados->tipo_sanguineo);
        return $meusdados;
    }

    /**
     * Get user relationships list from SUAP (/api/rh/meus-vinculos/).
     *
     * @param array $credentials Credentials with access token
     * @return array List of user relationships
     */
    public function get_user_info_rh_meus_vinculos($credentials) {
        $meusvinculosurl = !empty($this->config->rh_meus_vinculos_url) ?
            $this->config->rh_meus_vinculos_url : 'https://suap.ifrn.edu.br/api/rh/meus-vinculos/';
        $meusvinculosresponse = auth_suap_curl_get($meusvinculosurl, $credentials);
        if (empty($meusvinculosresponse)) {
            throw new Exception("Erro ao tentar obter dados de vínculos do SUAP.");
        }
        $meusvinculos = json_decode($meusvinculosresponse);
        if (
            empty($meusvinculos) || !is_object($meusvinculos)
            || !isset($meusvinculos->results) || !is_array($meusvinculos->results)
        ) {
            throw new Exception("Dados de vínculos retornados pelo SUAP são inválidos.");
        }
        return ["vinculos" => $meusvinculos->results];
    }

    /**
     * Get student data from SUAP (/api/ensino/meus-dados-aluno/).
     *
     * @param array $credentials Credentials with access token
     * @return stdClass Object with student data
     */
    public function get_user_info_ensino_meus_dados_aluno($credentials) {
        try {
            $ensinoalunourl = !empty($this->config->ensino_meus_dados_aluno_url) ?
                $this->config->ensino_meus_dados_aluno_url : 'https://suap.ifrn.edu.br/api/ensino/meus-dados-aluno/';
            $ensinoalunoresponse = auth_suap_curl_get($ensinoalunourl, $credentials);
            if (empty($ensinoalunoresponse)) {
                throw new Exception("Erro ao tentar obter dados de aluno do SUAP.");
            }
            $ensinoaluno = json_decode($ensinoalunoresponse);
            if (empty($ensinoaluno) || !is_object($ensinoaluno)) {
                throw new Exception("Dados de aluno retornados pelo SUAP são inválidos.");
            }
            foreach (['email_academico', 'email_escolar', 'cpf'] as $key) {
                unset($ensinoaluno->{$key});
            }
            return $ensinoaluno;
        } catch (\Throwable $e) {
            return (object)[];
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
            auth_suap_redirect($CFG->wwwroot);
        }

        $userdata = null;
        try {
            $credentials = $this->authenticate_token();

            $rheu = (array) $this->get_user_info_rh_eu($credentials);
            $meusdados = (array) $this->get_user_info_rh_meus_dados($credentials);
            $meusvinculos = (array) $this->get_user_info_rh_meus_vinculos($credentials);
            $ensinomeusdadosaluno = (array) $this->get_user_info_ensino_meus_dados_aluno($credentials);
            $userdata = (object) array_merge($rheu, $meusdados, $ensinomeusdadosaluno, $meusvinculos);

            $usuario = $this->create_or_update_user($userdata);

            complete_user_login($usuario);
            $next = !empty($SESSION->next_after_next) ? $SESSION->next_after_next : $CFG->wwwroot;
            unset($SESSION->next_after_next);
            auth_suap_redirect($next);
        } catch (\Throwable $e) {
            // Log error for administrators.
            debugging('[AUTH SUAP] OAuth2 Authentication Error: ' . $e->getMessage(), DEBUG_DEVELOPER);

            echo $e->getMessage();
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
        global $DB;

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

        return $DB->get_record("user", ["username" => $username]);
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
     * @return array User attributes array.
     */
    public function get_userinfo() {
        return get_object_vars($this->usuario);
    }
}
