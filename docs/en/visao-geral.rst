Overview
========

What the plugin does
---------------------

``auth_suap`` is an ``auth``-type (authentication) plugin for Moodle, built on top of the
core ``auth_oauth2`` plugin. It replaces (or complements) Moodle's default login with an
OAuth2 *Authorization Code* flow against SUAP and, on every successful authentication:

* creates the user in Moodle the first time (``username`` = SUAP's ``identificacao``/
  ``matricula``, lowercased) or updates the existing user on subsequent logins;
* synchronizes personal and institutional data (name, e-mail, CPF, RG, date of birth,
  enrollment/employment relationship, course, campus, class, hub/pole, etc.) into custom
  profile fields;
* downloads and applies the user's photo (``url_foto_150x200`` → ``url_foto_75x100`` →
  ``foto``);
* optionally applies default user preferences (``local_suap``) only when the account is
  created.

See :doc:`fluxo-autenticacao` for the step-by-step login flow and :doc:`sincronizacao-usuario`
for the complete list of synchronized fields.

Requirements
------------

* Moodle 4.5.0+ (``$plugin->requires`` = ``2024_10_07_00`` in ``version.php``; the CI pipeline
  tests against ``MOODLE_405_STABLE``).
* PHP 8.3+ with the cURL extension enabled (the only version tested in the CI pipeline).
* Core ``auth_oauth2`` plugin enabled — ``auth_plugin_suap`` extends ``auth_oauth2\auth``.

Optional dependency
--------------------

* **local_suap** — if installed, its preferences configured in
  ``default_user_preferences`` (Moodle admin) are applied to the user **only when the
  account is created**, not on subsequent logins.

Languages
---------

The plugin's interface strings (``lang/``) are available in ``pt_br`` (main language, used
during development), ``en`` (required by Moodle, used as fallback), ``es``, ``fr``, ``nl``
and ``zh_cn``. Simply change the site's or user's language in Moodle so that the plugin's
screens — settings, scheduled tasks, error messages — are displayed in the chosen language.

Repository structure
----------------------

.. code-block:: text

   auth_suap/
   ├── auth.php                    # auth_plugin_suap class: login, callback, synchronization
   ├── locallib.php                 # Helpers: cURL, config, profile field creation
   ├── login.php                    # Endpoint that starts the OAuth2 flow
   ├── authenticate.php              # OAuth2 callback (exchanges code for token and syncs)
   ├── logout.php                   # Logout confirmation page (SUAP + Moodle)
   ├── health.php                   # Diagnostics endpoint (active settings)
   ├── index.php                    # Redirects to login.php
   ├── settings.php                 # Settings screen in Moodle admin
   ├── version.php                  # Plugin version/release/maturity
   ├── db/
   │   ├── install.php              # Creates profile field categories and fields on install
   │   ├── upgrade.php               # Reapplies the same fields on every upgrade
   │   ├── migrate.php               # auth_suap_bulk_user_custom_field() (shared)
   │   └── uninstall.php
   ├── classes/privacy/provider.php  # Metadata sent to SUAP (privacy API)
   ├── lang/{en,pt_br,es,fr,nl,zh_cn}/auth_suap.php # Language strings
   ├── templates/auth_error.mustache # Authentication error screen
   ├── docs/                         # This documentation (Sphinx)
   └── .github/workflows/
       ├── ci.yml                    # moodle-plugin-ci (lint, PHPCS, PHPUnit, Behat)
       ├── release.yml                # Generates an installable ZIP on every tag
       └── docs.yml                   # Publishes this documentation to GitHub Pages

Organization
------------

The repository lives in the `suap-ava-suite <https://github.com/suap-ava-suite>`_
organization as ``moodle-auth_suap``, alongside other components of the AVA/SUAP suite used
by IFRN (for example, ``local_suap``, referenced above as an optional dependency).
