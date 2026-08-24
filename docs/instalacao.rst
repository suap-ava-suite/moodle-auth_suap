Instalação
==========

A instalação tem duas pontas: registrar uma aplicação OAuth2 no SUAP e configurar o plugin
no Moodle com as credenciais geradas.

1. Configuração no SUAP
------------------------

1. No SUAP, pesquise por **auth** e selecione **Aplicações OAUTH2**.
2. No canto superior direito, clique em **Adicionar Aplicação OAUTH2**.
3. Preencha os campos:

   * **Nome:** um nome descritivo para a instância do Moodle.
   * **Authorization grant type:** ``Authorization code``.
   * **Redirect URIs:**
     ``http://moodle/auth/suap/authenticate.php http://moodle/admin/oauth2callback.php http://moodle/authenticate.php``
   * **Client type:** ``Public``.
   * **Algorithm:** ``No OIDC support``.
   * **Ativo:** marcado.

4. Clique em **Salvar mudanças**.

.. warning::
   O **Client Secret** é exibido apenas uma vez. Guarde-o imediatamente — se for perdido, é
   necessário registrar uma nova aplicação.

2. Configuração no Moodle
---------------------------

1. Ative o plugin de autenticação: **Administração do site → Plugins → Autenticação →
   Gerenciar autenticação** e habilite **SUAP**.
2. Defina a **URL alternativa para login** (``alternateloginurl``) como:
   ``http://moodle/auth/suap/login.php``.

.. danger::
   Ao definir a URL alternativa, **todas** as tentativas de login são redirecionadas para
   essa página. Garanta que **já existe pelo menos um usuário com autenticação OAuth2 e
   permissões de administrador** antes de salvar, para não ficar bloqueado do lado de fora
   do Moodle.

3. Role até o final e clique em **Salvar mudanças**.

3. Configuração do SUAP no Moodle
------------------------------------

Em **Administração do site → Plugins → Autenticação → SUAP**, preencha:

.. list-table::
   :header-rows: 1
   :widths: 30 70

   * - Campo
     - Valor
   * - Client ID
     - Gerado no SUAP no passo 1.
   * - Client Secret
     - Gerado no SUAP no passo 1.
   * - Authorize URL
     - Ex.: ``https://suap.ifrn.edu.br/o/authorize/``
   * - Token URL
     - Ex.: ``https://suap.ifrn.edu.br/o/token/``
   * - RH/EU URL
     - Ex.: ``https://suap.ifrn.edu.br/api/rh/eu/``
   * - RH/Meus Dados URL
     - Ex.: ``https://suap.ifrn.edu.br/api/rh/meus-dados/``
   * - RH/Meus Vínculos URL
     - Ex.: ``https://suap.ifrn.edu.br/api/rh/meus-vinculos/``
   * - Ensino/Meus Dados Aluno URL
     - Ex.: ``https://suap.ifrn.edu.br/api/ensino/meus-dados-aluno/``
   * - Logout URL
     - Ex.: ``https://suap.ifrn.edu.br/comum/logout/``

Todos os campos têm um valor padrão pré-preenchido a partir da variável de ambiente
``SUAP_BASE_URL`` (``https://suap.ifrn.edu.br`` se não definida) — ver ``settings.php``.

Clique em **Salvar mudanças**.

4. Testando o acesso
----------------------

Ao clicar no botão de login, o usuário é redirecionado à tela de autenticação do SUAP.

* Se o usuário já existir no Moodle, seus dados são atualizados.
* Caso contrário, uma nova conta é criada automaticamente.

.. note::
   O endpoint ``/auth/suap/health.php`` exige um usuário autenticado (``require_login``) e
   expõe as configurações ativas do plugin (URLs, versão) em JSON — útil para diagnosticar
   uma instalação sem revelar o *client secret*.
