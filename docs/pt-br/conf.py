# docs/conf.py
import os
import sys

import moodle_docs_theme

sys.path.insert(0, os.path.abspath(".."))

project = "auth_suap"

extensions = [
    "sphinx.ext.githubpages",
    "moodle_docs_theme",
]

templates_path = ["_templates"]
exclude_patterns = ["_build", "Thumbs.db", ".DS_Store"]

root_doc = "index"

html_theme = "moodle_docs_theme"
html_theme_path = [moodle_docs_theme.get_html_theme_path()]
html_static_path = ["_static"]

html_theme_options = {
    "project_name": "auth_suap",
    "tagline": "Plugin de autenticação OAuth2 do SUAP para o Moodle",
    "github_url": "https://github.com/suap-ava-suite/moodle-auth_suap",
    "github_repo": "suap-ava-suite/moodle-auth_suap",
    "github_version": "main",
    "doc_path": "docs/pt-br/",
    "show_edit_on_github": True,
    "enable_dark_mode": True,
    "navigation_links": (
        "Início|index, Visão geral|visao-geral, Instalação|instalacao, "
        "Fluxo de autenticação|fluxo-autenticacao, "
        "Sincronização de usuário|sincronizacao-usuario, "
        "Privacidade|privacidade, Desenvolvimento|desenvolvimento, "
        "🌐 English (EN)|../en/index.html"
    ),
}
