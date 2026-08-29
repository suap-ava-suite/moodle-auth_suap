Development
============

Versioning
----------

Whenever files in the ``db/`` or ``lang/`` folders change, ``version.php`` must be
incremented:

* ``$plugin->version`` follows the ``YYYY_MM_DD_XXX`` pattern, where ``YYYY_MM_DD`` reflects
  the date of the change.
* ``$plugin->release`` follows the ``4.5.XXX`` pattern.
* ``XXX`` is the same value in both fields and must be incremented by 1 on every change to
  those folders.

This is the criterion checked by ``moodle-plugin-ci savepoints`` in CI, and also by the
**Extract and validate plugin version** step of the release workflow (see below).

Pre-commit (mandatory)
-------------------------

Using **pre-commit** is mandatory in this repository: the hook forces the same test suite
used in CI to run locally, via ``act``, before every commit.

.. code-block:: bash

   act -j test --matrix php:8.3 --matrix database:pgsql

Two ways to enable it:

**Option 1 — the** ``pre-commit`` **tool (recommended)**

.. code-block:: bash

   pyenv virtualenv 3.14 pre-commit
   pyenv activate pre-commit
   pip install pre-commit
   pre-commit install

**Option 2 — native Git hook**

.. code-block:: bash

   git config core.hooksPath .githooks
   chmod +x .githooks/pre-commit

The configuration lives in ``.pre-commit-config.yaml`` (local hook ``act-test``) and in
``.githooks/pre-commit``.

Running the tests locally with ``act``
------------------------------------------

To run the GitHub Actions pipeline locally:

.. code-block:: bash

   curl -s https://raw.githubusercontent.com/nektos/act/master/install.sh | sudo bash
   sudo mv ./bin/act /usr/local/bin/
   act -j test --matrix php:8.3 --matrix database:pgsql

CI/CD
-----

``.github/workflows/ci.yml`` — **Moodle Plugin CI**
    Runs on every ``push`` and ``pull_request``. Uses ``moodlehq/moodle-plugin-ci`` against
    ``MOODLE_405_STABLE`` (Moodle 4.5.x) with PHP ``8.3`` × database (``pgsql``, ``mariadb``)
    — the same PHP version used locally via ``act`` (see above). Steps: PHP Lint, PHP Mess
    Detector (non-blocking), Moodle Code Checker (PHPCS, up to 100 *warnings*), Moodle
    PHPDoc Checker (up to 100 *warnings*), ``validate``, ``savepoints`` (validates the
    versioning above), Grunt, PHPUnit (``--fail-on-warning``) and, if present, Behat tests
    with Chrome.

``.github/workflows/release.yml`` — **Release**
    Triggered by a tag push (``git tag -a 4.5.XXX -m "..."; git push origin 4.5.XXX``).
    Validates that the last 3 digits of ``$plugin->version`` and ``$plugin->release`` match,
    that ``$plugin->release`` matches the tag name and that ``$plugin->component`` is
    defined; then packages an installable ZIP (``auth_suap-<version>.zip``) and publishes a
    GitHub Release with automatically generated notes. The ZIP can be installed directly in
    **Site administration → Plugins → Install plugins**.

``.github/workflows/docs.yml`` — **Build & Deploy Documentation**
    Publishes this documentation (Sphinx) to GitHub Pages on every *push* to ``main`` that
    changes ``docs/**``. See :ref:`documentation` below.

.. _documentation:

Documentation
--------------

This documentation uses `Sphinx <https://www.sphinx-doc.org/>`_ with the
`moodle-docs-theme <https://pypi.org/project/moodle-docs-theme/>`_ theme and ``.rst`` files
in ``docs/``. To build it locally:

.. code-block:: bash

   pip install sphinx moodle-docs-theme
   sphinx-build -W -b html docs/en docs/_build/html/en

The ``docs.yml`` workflow runs the same command in CI and publishes the result via
``actions/deploy-pages``.

Manual packaging
-------------------

The release workflow automates packaging, but the same result can be reproduced locally
based on what ``release.yml`` does: copy the repository contents into a folder named after
the component without the prefix (``suap``), excluding ``.git``, ``.github``,
``node_modules``, ``.gitignore``, ``tests`` and ``vendor``, and compress that folder into
``auth_suap-<version>.zip``.
