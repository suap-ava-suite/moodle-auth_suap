Visão geral
===========

O que o plugin faz
-------------------

``auth_suap`` é um plugin do tipo ``auth`` (autenticação) para o Moodle, construído sobre o
plugin core ``auth_oauth2``. Ele substitui (ou complementa) o login padrão do Moodle por um
fluxo OAuth2 *Authorization Code* contra o SUAP e, a cada autenticação bem-sucedida:

* cria o usuário no Moodle na primeira vez (``username`` = ``identificacao``/``matricula`` do
  SUAP, em minúsculas) ou atualiza o usuário existente nas vezes seguintes;
* sincroniza dados pessoais e institucionais (nome, e-mail, CPF, RG, data de nascimento,
  vínculo, curso, campus, turma, polo etc.) em campos de perfil customizados;
* baixa e aplica a foto do usuário (``url_foto_150x200`` → ``url_foto_75x100`` → ``foto``);
* opcionalmente aplica preferências padrão de usuário (``local_suap``) apenas na criação da
  conta.

Veja :doc:`fluxo-autenticacao` para o passo a passo do login e :doc:`sincronizacao-usuario`
para a lista completa de campos sincronizados.

Requisitos
----------

* Moodle 4.5.0+ (``$plugin->requires`` = ``2024_10_07_00`` em ``version.php``; a esteira de CI
  testa contra ``MOODLE_405_STABLE``).
* PHP 8.3+ com a extensão cURL habilitada (única versão testada na esteira de CI).
* Plugin core ``auth_oauth2`` habilitado — ``auth_plugin_suap`` estende ``auth_oauth2\auth``.

Dependência opcional
---------------------

* **local_suap** — se instalado, suas preferências configuradas em
  ``default_user_preferences`` (admin do Moodle) são aplicadas ao usuário **apenas na
  criação** da conta, não em logins subsequentes.

Estrutura do repositório
-------------------------

.. code-block:: text

   auth_suap/
   ├── auth.php                    # Classe auth_plugin_suap: login, callback, sincronização
   ├── locallib.php                 # Helpers: cURL, config, criação de campos de perfil
   ├── login.php                    # Endpoint que inicia o fluxo OAuth2
   ├── authenticate.php              # Callback OAuth2 (troca code por token e sincroniza)
   ├── logout.php                   # Página de confirmação de logout (SUAP + Moodle)
   ├── health.php                   # Endpoint de diagnóstico (configurações ativas)
   ├── index.php                    # Redireciona para login.php
   ├── settings.php                 # Tela de configuração no admin do Moodle
   ├── version.php                  # Versão/release/maturidade do plugin
   ├── db/
   │   ├── install.php              # Cria categorias e campos de perfil na instalação
   │   ├── upgrade.php               # Reaplica os mesmos campos em cada upgrade
   │   ├── migrate.php               # auth_suap_bulk_user_custom_field() (compartilhado)
   │   └── uninstall.php
   ├── classes/privacy/provider.php  # Metadados enviados ao SUAP (API de privacidade)
   ├── lang/{en,pt_br}/auth_suap.php # Strings de idioma
   ├── templates/auth_error.mustache # Tela de erro de autenticação
   ├── docs/                         # Esta documentação (Sphinx)
   └── .github/workflows/
       ├── ci.yml                    # moodle-plugin-ci (lint, PHPCS, PHPUnit, Behat)
       ├── release.yml                # Gera ZIP instalável em cada tag
       └── docs.yml                   # Publica esta documentação no GitHub Pages

Organização
-----------

O repositório vive na organização `suap-ava-suite <https://github.com/suap-ava-suite>`_ como
``moodle-auth_suap``, ao lado de outros componentes da suíte AVA/SUAP usados pelo IFRN (por
exemplo, ``local_suap``, referenciado acima como dependência opcional).
