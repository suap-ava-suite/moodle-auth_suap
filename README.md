# auth_suap

Plugin de autenticacao via OAuth2 do SUAP. Faz o login no Moodle e sincroniza dados do usuario, foto e campos de perfil customizados.

Documentação: publicada em https://suap-ava-suite.github.io/moodle-auth_suap/ (gerada
automaticamente a cada push em `docs/` via `.github/workflows/docs.yml`, usando o tema Sphinx
[moodle-docs-theme](https://pypi.org/project/moodle-docs-theme/)). Para gerar localmente:

```bash
pip install sphinx moodle-docs-theme
sphinx-build -W -b html docs docs/_build/html
```

Páginas: `docs/visao-geral.rst`, `docs/instalacao.rst`, `docs/fluxo-autenticacao.rst`,
`docs/sincronizacao-usuario.rst`, `docs/privacidade.rst`, `docs/desenvolvimento.rst`.

## Requisitos
- Moodle 4.5.0+ (require 2024_10_07_00)
- PHP 8.3+ com extensão cURL habilitada
- Plugin `auth_oauth2` habilitado (core do Moodle)

## Dependencias opcionais
- **local_suap** (opcional) - Se instalado, aplica preferencias customizadas de usuario conforme `default_user_preferences` no primeiro login

## 1. Configuracao no SUAP
- No SUAP, pesquise por auth e selecione **Aplicacoes OAUTH2**
- No canto superior direito, clique em **Adicionar Aplicacao OAUTH2**

### Preencha os campos
- **Nome:** Escolha um nome descritivo para o seu Moodle
- **Authorization grant type:** Authorization code
- **Redirect URIs:** `http://moodle/auth/suap/authenticate.php http://moodle/admin/oauth2callback.php http://moodle/authenticate.php`
- **Client type:** Public
- **Algorithm:** No OIDC support
- **Ativo:** ✅ Marque este campo

### Chaves
- O **Client ID** e o **Client Secret** serao usados no Moodle
- ⚠️ **Guarde o Client Secret**, pois ele nao podera ser visualizado novamente

Clique em **Salvar mudancas**

## 2. Configuracao no Moodle
1. Ativar plugins de Autenticacao:
   - Acesse **Administracao do site > Plugins > Autenticacao > Gerenciar autenticacao**
   - Habilite SUAP (caso ainda não esteja ativados)
2. Definir URL alternativa para login:
   - Role ate **URL alternativa para login (alternateloginurl)** e preencha com: `http://moodle/auth/suap/login.php`

⚠️ **Atencao:** Ao definir a URL alternativa, **todas** as tentativas de login serao redirecionadas para essa pagina. Certifique-se de que **ha pelo menos um usuario com autenticacao OAuth2 e permissoes de administrador** antes de prosseguir, para evitar ficar "preso do lado de fora" do Moodle.

Role ate o final e clique em **Salvar mudancas**

## 3. Configuracao do SUAP no Moodle
1. Acesse **Administracao do site > Plugins > Autenticacao > SUAP**
2. Preencha os campos:
   - **Client ID:** Client ID gerado no SUAP (você gerou no passo 1)
   - **Client Secret:** Client Secret gerado no SUAP(você gerou no passo 1)
   - **Authorize URL:** URL de autorizacao do SUAP, ex.: https://suap.ifrn.edu.br/o/authorize/
   - **Token URL:** URL de token do SUAP, ex.: https://suap.ifrn.edu.br/o/token/
   - **RH/EU URL:** URL da API `eu` do SUAP, ex.: https://suap.ifrn.edu.br/api/rh/eu/
   - **Logout URL:** URL de logout do SUAP, ex.: https://suap.ifrn.edu.br/comum/logout/
3. Clique em **Salvar mudancas**

## 4. Testando o acesso
Agora, ao clicar no botao de login, voce sera redirecionado para a tela de autenticacao do SUAP.
- Se o usuario ja existir no Moodle, suas informacoes serao atualizadas.
- Caso contrario, um novo usuario sera criado automaticamente.

## Fluxo de autenticacao (resumo)
1. `login.php` redireciona para `authorize_url`.
2. O SUAP retorna `code` para `authenticate.php`.
3. `authenticate.php` troca o `code` por `access_token` em `token_url`.
4. O perfil eh buscado em `rh_eu_url` e os dados sao sincronizados.
5. O usuario eh autenticado no Moodle e redirecionado ao destino.

## Endpoints e utilitarios
| Endpoint | Finalidade |
| --- | --- |
| `/auth/suap/login.php` | Inicia o login SUAP |
| `/auth/suap/authenticate.php` | Callback OAuth2 |
| `/auth/suap/logout.php` | Logout completo (SUAP + Moodle) |
| `/auth/suap/dispatch.php` | Gera token de webservice para apps |
| `/auth/suap/health.php` | Exibe configuracoes ativas (debug) para quem tem a credencial |

## Webservice para apps (dispatch.php)
`dispatch.php` valida o token recebido no header `Authentication: Token <token>` e gera um token de webservice do Moodle.

Para funcionar, configure `verify_token_url` em `auth_suap` (tabela `config_plugins`), pois o plugin nao possui este campo na tela de configuracao.

## Campos de perfil criados automaticamente
## Campos de perfil criados automaticamente
Na instalação/atualização o plugin cria os grupos de campos customizados (**SUAP**, **Dados pessoais**, **Dados de contato**, **Matrícula**, **Polo**, **Campus**, **Curso** e **Turma**) e registra:
- `tipo_usuario`, `eh_servidor`, `eh_aluno`, `eh_prestador`, `eh_usuarioexterno`, `eh_docente`, `eh_tecnico_administrativo`, `last_login`
- `nome_apresentacao`, `nome_completo`, `nome_social`, `data_de_nascimento`, `sexo`, `cpf`, `rg`, `passaporte`, `naturalidade`, `filiacao_mae`, `filiacao_pai`, `id_doc_certificado`, `tipo_doc_certificado`, `eh_estrangeiro`
- `email_google_classroom`, `email_academico`, `email_secundario`
- `programa_nome`, `ingresso_periodo`, `outras_matriculas`, `situacao_vinculo`, `matricula_regular`, `vinculo_ativo`, `vinculo_cargo`, `vinculo_categoria`, `ira`, `matriz_curricular`
- `polo_id`, `polo_nome`, `polo_sigla`
- `campus_id`, `campus_descricao`, `campus_sigla`
- `curso_id`, `curso_codigo`, `curso_descricao`, `curso_modalidade_id`, `curso_modalidade_descricao`, `curso_modalidade`, `curso_nivel_ensino_id`, `curso_nivel_ensino_descricao`, `curso_nivel_ensino`
- `turma_id`, `turma_codigo`

## Campos alterados no primeiro login x nos logins seguintes

Tabela baseada no fluxo de criacao/atualizacao em auth.php.

| Campo | Primeiro login | Logins seguintes | Observacoes |
| --- | --- | --- | --- |
| user.username | Sim (criacao) | Nao | `identificacao` ou `matricula` (em minúsculas) vindo do SUAP. |
| user.password | Sim (criacao) | Nao | Senha aleatoria local (é ignorada). |
| user.timezone | Sim (criacao) | Nao | `99` |
| user.confirmed | Sim (criacao) | Nao | `1` |
| user.mnethostid | Sim (criacao) | Nao | `1` |
| user.policyagreed | Sim (criacao) | Nao | `0` |
| user.deleted | Sim (criacao) | Nao | `0` |
| user.firstaccess | Sim (criacao) | Nao | Timestamp atual. |
| user.currentlogin | Sim (criacao) | Nao | Timestamp atual. |
| user.lastip | Sim (criacao) | Nao | IP remoto. |
| user.firstnamephonetic | Sim (criacao) | Nao | `null`. |
| user.lastnamephonetic | Sim (criacao) | Nao | `null`. |
| user.middlename | Sim (criacao) | Nao | `null`. |
| user.alternatename | Sim (criacao) | Nao | `null`. |
| user.firstname | Sim | Sim | Derivado de `nome_social` ou `nome_registro`, nessa ordem. Exceto a última parte. |
| user.lastname | Sim | Sim | Derivado de `nome_social` ou `nome_registro`, nessa ordem. Apenas a última parte. |
| user.email | Sim | Sim | `email_preferencial` ou `email` |
| user.auth | Sim | Sim | `suap` |
| user.suspended | Sim | Sim | `0` |
| user.picture | Sim (se foto) | Sim (se foto) | `url_foto_150x200` $\rightarrow$ `url_foto_75x100` $\rightarrow$ `foto` via `process_new_icon`. |
| profile_field_nome_apresentacao | Sim | Sim | `nome_usual` |
| profile_field_nome_completo | Sim | Sim | `nome_registro` |
| profile_field_nome_social | Sim | Sim | `nome_social` |
| profile_field_email_secundario | Sim | Sim | `email_secundario` |
| profile_field_email_google_classroom | Sim | Sim | `email_google_classroom` |
| profile_field_email_academico | Sim | Sim | `email_academico` |
| profile_field_campus_sigla | Sim | Sim | `campus` |
| profile_field_last_login | Sim | Sim | JSON com o payload do SUAP. Usado para suporte. |
| profile_field_tipo_usuario | Sim | Sim | `tipo_usuario` |
| profile_field_eh_servidor | Sim | Sim | `tipo_vinculo == "Servidor"` |
| profile_field_eh_aluno | Sim | Sim | `tipo_usuario == "Aluno"` |
| profile_field_eh_prestador | Sim | Sim | `tipo_vinculo == "Prestador de Serviço"` |
| profile_field_eh_usuarioexterno | Sim | Sim | `tipo_vinculo == "Prestador de Serviço"` |
| profile_field_data_de_nascimento | Sim | Sim | `data_nascimento` ou `data_de_nascimento` |
| profile_field_sexo | Sim | Sim | `sexo` |
| profile_field_cpf | Sim | Sim | `cpf` (apenas números, 11 dígitos com zeros à esquerda). Descontinuado. |
| profile_field_rg | Sim | Sim | `rg` |
| profile_field_passaporte | Sim | Sim | `passaporte`. Descontinuado. |
| profile_field_naturalidade | Sim | Sim | `naturalidade` |
| profile_field_filiacao_mae | Sim | Sim | `filiacao[0]` |
| profile_field_filiacao_pai | Sim | Sim | `filiacao[1]` |
| profile_field_id_doc_certificado | Sim (se cpf/passaporte) | Sim (se cpf/passaporte) | `cpf` (mascarado com zeros à esquerda: `000.000.000-00`) ou `passaporte`, se não houver CPF. |
| profile_field_tipo_doc_certificado | Sim (se cpf/passaporte) | Sim (se cpf/passaporte) | `CPF` ou `Passaporte`, se não houver CPF. |
| profile_field_curso_modalidade | Sim | Sim | `vinculos[].detalhamento.modalidade` |
| profile_field_curso_nivel_ensino | Sim | Sim | `vinculos[].detalhamento.nivel_ensino` |
| profile_field_vinculo_ativo | Sim | Sim | `vinculos[].detalhamento.ativo` |
| profile_field_vinculo_cargo | Sim | Sim | `vinculos[].detalhamento.cargo` |
| profile_field_vinculo_categoria | Sim | Sim | `vinculos[].detalhamento.categoria` |
| profile_field_matricula_regular | Sim | Sim | `vinculo.matricula_regular` |
| preferencia de usuario (local_suap) | Sim (criacao) | Nao | Conforme `default_user_preferences` configurado no admin do Moodle. |

## Observacoes
- O campo `cpf` e `passaporte` estao marcados como descontinuados, mas ainda podem ser recebidos do SUAP.
- Se a foto estiver disponivel (`url_foto_150x200`, `url_foto_75x100` ou `foto`), ela eh salva como `user.picture` via `process_new_icon`.
- `profile_field_last_login` guarda o JSON completo recebido do SUAP para suporte.

## Como testar as actions

Para testar os workflows do GitHub Actions localmente utilizando o `act`:

1. Instale o `act`:
```bash
curl -s https://raw.githubusercontent.com/nektos/act/master/install.sh | sudo bash
sudo mv ./bin/act /usr/local/bin/
```

2. Execute o job de testes localmente:
```bash
act -j test --matrix php:8.3 --matrix database:pgsql
```

## Configuração e Uso do Pre-commit (Obrigatório)

O uso do **pre-commit** é **obrigatório** neste repositório para garantir que nenhum commit seja realizado sem a validação prévia dos testes automatizados e regras de estilo do Moodle via `act`.

O hook de pre-commit força a execução do comando:
```bash
act -j test --matrix php:8.3 --matrix database:pgsql
```

### Como ativar o Pre-commit

Você pode ativar o pre-commit de duas formas:

#### Opção 1: Utilizando a ferramenta `pre-commit` (Recomendado)
1. Instale a ferramenta `pre-commit`:
   ```bash
   pyenv virtualenv 3.14 pre-commit
   pyenv activate pre-commit
   pip install pre-commit
   ```
2. Instale o hook no repositório:
   ```bash
   pre-commit install
   ```

#### Opção 2: Configurando o hook nativo do Git
Para utilizar o hook de pre-commit disponibilizado no diretório `.githooks`:
```bash
git config core.hooksPath .githooks
chmod +x .githooks/pre-commit
```
