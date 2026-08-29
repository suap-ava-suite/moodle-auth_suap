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

``auth_suap`` is a Moodle authentication plugin that implements Single Sign-On (SSO) via
OAuth2 against `SUAP <https://suap.ifrn.edu.br/>`_ (Sistema Unificado de Administração
Pública — Unified Public Administration System), used at the Instituto Federal do Rio Grande
do Norte (IFRN) and other Brazilian federal institutions. Besides authenticating, the plugin
synchronizes the user's institutional data (name, e-mail, CPF, enrollment/employment
relationship, course, campus, photo, among others) into custom Moodle profile fields on every
login.

Contents
--------

.. toctree::
   :maxdepth: 2

   visao-geral
   instalacao
   fluxo-autenticacao
   sincronizacao-usuario
   privacidade
   desenvolvimento
