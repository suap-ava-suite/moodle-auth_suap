# docs/conf.py
import os
import sys

import moodle_docs_theme

sys.path.insert(0, os.path.abspath(".."))

project = "auth_suap"
language = "en"

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
    "tagline": "SUAP OAuth2 authentication plugin for Moodle",
    "github_url": "https://github.com/suap-ava-suite/moodle-auth_suap",
    "github_repo": "suap-ava-suite/moodle-auth_suap",
    "github_version": "main",
    "doc_path": "docs/en/",
    "show_edit_on_github": True,
    "enable_dark_mode": True,
    "navigation_links": (
        "Home|index, Overview|visao-geral, Installation|instalacao, "
        "Authentication flow|fluxo-autenticacao, "
        "User synchronization|sincronizacao-usuario, "
        "Privacy|privacidade, Development|desenvolvimento, "
        "🌐 Português (PT-BR)|../pt-br/index.html"
    ),
}
