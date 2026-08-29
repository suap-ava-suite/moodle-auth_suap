Privacidade
===========

``auth_suap`` implementa a API de privacidade do Moodle (``core_privacy``) em
``classes/privacy/provider.php``. O plugin não armazena dados pessoais em tabelas próprias —
os dados sincronizados vivem na tabela padrão ``user`` e em ``user_info_data`` (campos de
perfil customizados), ambas fora do escopo direto deste *provider*.

O que o *provider* declara
----------------------------

``provider::get_metadata()`` registra um **link de localização externa** (``suap``),
descrevendo os dados enviados ao serviço externo SUAP durante a autenticação e a
sincronização:

.. list-table::
   :header-rows: 1
   :widths: 30 70

   * - Campo
     - Descrição
   * - ``username``
     - Nome de usuário (ID institucional).
   * - ``email``
     - Endereço de e-mail.
   * - ``firstname``
     - Primeiro nome do usuário.
   * - ``lastname``
     - Sobrenome do usuário.
   * - ``cpf``
     - CPF do usuário (documento fiscal brasileiro).
   * - ``tipo``
     - Tipo/papel do usuário (aluno, servidor, professor etc.).

Isso permite que a tela **Administração do site → Privacidade e políticas → Registro de
localizações de dados de usuários** do Moodle liste corretamente o SUAP como destino externo
desses dados.

.. note::
   Este *provider* implementa apenas ``metadata_provider``. Como o plugin não persiste dados
   de usuário em tabelas específicas do componente (fora do padrão ``user``/
   ``user_info_data``), ele não implementa as interfaces de exportação/exclusão
   (``core_userlist_provider`` / ``plugin\privacy\provider`` com ``local_data``) — a
   exportação e exclusão desses dados seguem o fluxo padrão do Moodle para a tabela
   ``user`` e para campos de perfil customizados.
