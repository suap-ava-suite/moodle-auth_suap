Installation
============

Installation has two ends: registering an OAuth2 application in SUAP and configuring the
plugin in Moodle with the generated credentials.

1. Configuration in SUAP
--------------------------

1. In SUAP, search for **auth** and select **Aplicações OAUTH2** (OAuth2 Applications).
2. In the upper-right corner, click **Adicionar Aplicação OAUTH2** (Add OAuth2 Application).
3. Fill in the fields:

   * **Nome (Name):** a descriptive name for the Moodle instance.
   * **Authorization grant type:** ``Authorization code``.
   * **Redirect URIs:**
     ``http://moodle/auth/suap/authenticate.php http://moodle/admin/oauth2callback.php http://moodle/authenticate.php``
   * **Client type:** ``Public``.
   * **Algorithm:** ``No OIDC support``.
   * **Ativo (Active):** checked.

4. Click **Salvar mudanças** (Save changes).

.. warning::
   The **Client Secret** is shown only once. Save it immediately — if it is lost, you must
   register a new application.

2. Configuration in Moodle
-----------------------------

1. Enable the authentication plugin: **Site administration → Plugins → Authentication →
   Manage authentication** and enable **SUAP**.
2. Set the **Alternate login URL** (``alternateloginurl``) to:
   ``http://moodle/auth/suap/login.php``.

.. danger::
   Once the alternate login URL is set, **all** login attempts are redirected to that page.
   Make sure **at least one user with OAuth2 authentication and administrator permissions
   already exists** before saving, so you don't get locked out of Moodle.

3. Scroll to the bottom and click **Salvar mudanças** (Save changes).

3. SUAP configuration in Moodle
-----------------------------------

Under **Site administration → Plugins → Authentication → SUAP**, fill in:

.. list-table::
   :header-rows: 1
   :widths: 30 70

   * - Field
     - Value
   * - Client ID
     - Generated in SUAP in step 1.
   * - Client Secret
     - Generated in SUAP in step 1.
   * - Authorize URL
     - E.g.: ``https://suap.ifrn.edu.br/o/authorize/``
   * - Token URL
     - E.g.: ``https://suap.ifrn.edu.br/o/token/``
   * - RH/EU URL
     - E.g.: ``https://suap.ifrn.edu.br/api/rh/eu/``
   * - RH/Meus Dados URL
     - E.g.: ``https://suap.ifrn.edu.br/api/rh/meus-dados/``
   * - RH/Meus Vínculos URL
     - E.g.: ``https://suap.ifrn.edu.br/api/rh/meus-vinculos/``
   * - Ensino/Meus Dados Aluno URL
     - E.g.: ``https://suap.ifrn.edu.br/api/ensino/meus-dados-aluno/``
   * - Logout URL
     - E.g.: ``https://suap.ifrn.edu.br/comum/logout/``

All fields have a default value pre-filled from the ``SUAP_BASE_URL`` environment variable
(``https://suap.ifrn.edu.br`` if not set) — see ``settings.php``.

Click **Salvar mudanças** (Save changes).

4. Testing access
-------------------

When the user clicks the login button, they are redirected to SUAP's authentication screen.

* If the user already exists in Moodle, their data is updated.
* Otherwise, a new account is created automatically.

.. note::
   The ``/auth/suap/health.php`` endpoint requires an authenticated user (``require_login``)
   and exposes the plugin's active settings (URLs, version) as JSON — useful for
   troubleshooting an installation without revealing the *client secret*.
