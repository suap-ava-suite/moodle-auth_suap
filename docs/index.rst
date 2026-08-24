auth_suap
=========

.. image:: https://img.shields.io/badge/License-GPLv3-blue.svg
   :target: https://github.com/suap-ava-suite/moodle-auth_suap/blob/main/LICENSE
   :alt: License

.. image:: https://github.com/suap-ava-suite/moodle-auth_suap/actions/workflows/ci.yml/badge.svg
   :target: https://github.com/suap-ava-suite/moodle-auth_suap/actions/workflows/ci.yml
   :alt: Moodle Plugin CI


.. image:: https://img.shields.io/github/v/release/suap-ava-suite/moodle-auth_suap
   :target: https://github.com/suap-ava-suite/moodle-auth_suap/releases
   :alt: Latest release

.. image:: https://img.shields.io/badge/Moodle-4.5.0%2B-orange.svg
   :target: https://github.com/suap-ava-suite/moodle-auth_suap/blob/main/version.php
   :alt: Moodle compatibility

.. image:: https://img.shields.io/badge/PHP-8.3-777bb4.svg
   :target: https://github.com/suap-ava-suite/moodle-auth_suap/blob/main/.github/workflows/ci.yml
   :alt: PHP compatibility

.. image:: https://github.com/suap-ava-suite/moodle-auth_suap/actions/workflows/docs.yml/badge.svg
   :target: https://github.com/suap-ava-suite/moodle-auth_suap/actions/workflows/docs.yml
   :alt: Build & Deploy Documentation

``auth_suap`` é um plugin de autenticação para o Moodle que implementa Single Sign-On (SSO)
via OAuth2 contra o `SUAP <https://suap.ifrn.edu.br/>`_ (Sistema Unificado de Administração
Pública), usado no Instituto Federal do Rio Grande do Norte (IFRN) e em outras instituições
federais brasileiras. Além de autenticar, o plugin sincroniza dados institucionais do usuário
(nome, e-mail, CPF, vínculo, curso, campus, foto, entre outros) em campos de perfil
customizados do Moodle a cada login.

Conteúdo
--------

.. toctree::
   :maxdepth: 2

   visao-geral
   instalacao
   fluxo-autenticacao
   sincronizacao-usuario
   privacidade
   desenvolvimento
