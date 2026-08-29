Desenvolvimento
================

Versionamento
-------------

Sempre que houver alteração em arquivos das pastas ``db/`` ou ``lang/``, ``version.php`` deve
ser incrementado:

* ``$plugin->version`` segue o padrão ``YYYY_MM_DD_XXX``, onde ``YYYY_MM_DD`` reflete a data
  da alteração.
* ``$plugin->release`` segue o padrão ``4.5.XXX``.
* ``XXX`` é o mesmo valor nos dois campos e deve ser incrementado em 1 a cada alteração nessas
  pastas.

Este é o critério verificado por ``moodle-plugin-ci savepoints`` no CI, e também pelo passo
**Extract and validate plugin version** do workflow de release (veja abaixo).

Pre-commit (obrigatório)
--------------------------

O uso do **pre-commit** é obrigatório neste repositório: o hook força a execução da mesma
suíte de testes usada no CI, localmente, via ``act``, antes de qualquer commit.

.. code-block:: bash

   act -j test --matrix php:8.3 --matrix database:pgsql

Duas formas de ativar:

**Opção 1 — ferramenta** ``pre-commit`` **(recomendado)**

.. code-block:: bash

   pyenv virtualenv 3.14 pre-commit
   pyenv activate pre-commit
   pip install pre-commit
   pre-commit install

**Opção 2 — hook nativo do Git**

.. code-block:: bash

   git config core.hooksPath .githooks
   chmod +x .githooks/pre-commit

A configuração vive em ``.pre-commit-config.yaml`` (hook local ``act-test``) e em
``.githooks/pre-commit``.

Executando os testes localmente com ``act``
-----------------------------------------------

Para rodar a esteira do GitHub Actions localmente:

.. code-block:: bash

   curl -s https://raw.githubusercontent.com/nektos/act/master/install.sh | sudo bash
   sudo mv ./bin/act /usr/local/bin/
   act -j test --matrix php:8.3 --matrix database:pgsql

CI/CD
-----

``.github/workflows/ci.yml`` — **Moodle Plugin CI**
    Executa em todo ``push`` e ``pull_request``. Usa ``moodlehq/moodle-plugin-ci`` contra
    ``MOODLE_405_STABLE`` (Moodle 4.5.x) com PHP ``8.3`` × banco (``pgsql``, ``mariadb``) —
    a mesma versão de PHP usada localmente via ``act`` (veja acima). Etapas: PHP Lint, PHP
    Mess Detector (não bloqueante), Moodle Code Checker (PHPCS, até 100 *warnings*),
    Moodle PHPDoc Checker (até 100 *warnings*),
    ``validate``, ``savepoints`` (valida o versionamento acima), Grunt, PHPUnit
    (``--fail-on-warning``) e, se existirem, testes Behat com Chrome.

``.github/workflows/release.yml`` — **Release**
    Disparado por *push* de tag (``git tag -a 4.5.XXX -m "..."; git push origin 4.5.XXX``).
    Valida que os 3 últimos dígitos de ``$plugin->version`` e ``$plugin->release`` coincidem,
    que ``$plugin->release`` bate com o nome da tag e que ``$plugin->component`` está
    definido; em seguida empacota um ZIP instalável (``auth_suap-<version>.zip``) e publica
    uma GitHub Release com notas geradas automaticamente. O ZIP pode ser instalado
    diretamente em **Administração do site → Plugins → Instalar plugins**.

``.github/workflows/docs.yml`` — **Build & Deploy Documentation**
    Publica esta documentação (Sphinx) no GitHub Pages a cada *push* em ``main`` que altere
    ``docs/**``. Veja :ref:`documentacao` abaixo.

.. _documentacao:

Documentação
------------

Esta documentação usa `Sphinx <https://www.sphinx-doc.org/>`_ com o tema
`moodle-docs-theme <https://pypi.org/project/moodle-docs-theme/>`_ e arquivos ``.rst`` em
``docs/``. Para gerar localmente:

.. code-block:: bash

   pip install sphinx moodle-docs-theme
   sphinx-build -W -b html docs docs/_build/html

O workflow ``docs.yml`` roda o mesmo comando em CI e publica o resultado via
``actions/deploy-pages``.

Empacotamento manual
-----------------------

O workflow de release automatiza o empacotamento, mas o mesmo resultado pode ser reproduzido
localmente a partir do que ``release.yml`` faz: copiar o conteúdo do repositório para uma
pasta com o nome do componente sem o prefixo (``suap``), excluindo ``.git``, ``.github``,
``node_modules``, ``.gitignore``, ``tests`` e ``vendor``, e compactar essa pasta em
``auth_suap-<version>.zip``.
