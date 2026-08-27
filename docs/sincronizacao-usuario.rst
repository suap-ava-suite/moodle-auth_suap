Sincronização de usuário
==========================

Campos de perfil criados automaticamente
-------------------------------------------

Na instalação/atualização, ``auth_suap_bulk_user_custom_field()`` (``db/migrate.php``, chamada
por ``db/install.php`` e ``db/upgrade.php``) cria as categorias de campo de perfil **SUAP**,
**Dados pessoais**, **Dados de contato**, **Matrícula**, **Polo**, **Campus**, **Curso** e
**Turma**, e registra os campos:

* ``tipo_usuario``, ``eh_servidor``, ``eh_aluno``, ``eh_prestador``, ``eh_usuarioexterno``,
  ``eh_docente``, ``eh_tecnico_administrativo``, ``last_login``
* ``nome_apresentacao``, ``nome_completo``, ``nome_social``, ``data_de_nascimento``, ``sexo``,
  ``cpf``, ``rg``, ``passaporte``, ``naturalidade``, ``filiacao_mae``, ``filiacao_pai``,
  ``id_doc_certificado``, ``tipo_doc_certificado``, ``eh_estrangeiro``
* ``email_google_classroom``, ``email_academico``, ``email_secundario``
* ``programa_nome``, ``ingresso_periodo``, ``outras_matriculas``, ``situacao_vinculo``,
  ``matricula_regular``, ``vinculo_ativo``, ``vinculo_cargo``, ``vinculo_categoria``, ``ira``,
  ``matriz_curricular``
* ``polo_id``, ``polo_nome``, ``polo_sigla``
* ``campus_id``, ``campus_descricao``, ``campus_sigla``
* ``curso_id``, ``curso_codigo``, ``curso_descricao``, ``curso_modalidade_id``,
  ``curso_modalidade_descricao``, ``curso_modalidade``, ``curso_nivel_ensino_id``,
  ``curso_nivel_ensino_descricao``, ``curso_nivel_ensino``
* ``turma_id``, ``turma_codigo``

Todos os campos são criados com ``locked = 1`` (bloqueados para edição pelo usuário) e
``visible = 1``, exceto ``last_login``, que fica oculto (``visible = 0``). Ao final, o bloqueio
é replicado (``field_lock_profile_field_<shortname> = locked``) para **todos** os plugins de
autenticação instalados, não apenas ``auth_suap``.

Campos alterados no primeiro login vs. logins seguintes
------------------------------------------------------------

Tabela baseada no fluxo de criação/atualização em ``auth.php::create_or_update_user()``.

.. list-table::
   :header-rows: 1
   :widths: 28 12 12 48

   * - Campo
     - 1º login
     - Seguintes
     - Origem / observações
   * - ``user.username``
     - Sim (criação)
     - Não
     - ``identificacao`` ou ``matricula`` (minúsculas), vindo do SUAP.
   * - ``user.password``
     - Sim (criação)
     - Não
     - Senha aleatória local; ignorada (autenticação sempre via ``suap``).
   * - ``user.timezone``
     - Sim (criação)
     - Não
     - ``99``.
   * - ``user.confirmed``
     - Sim (criação)
     - Não
     - ``1``.
   * - ``user.mnethostid``
     - Sim (criação)
     - Não
     - ``1``.
   * - ``user.policyagreed``
     - Sim (criação)
     - Não
     - ``0``.
   * - ``user.deleted``
     - Sim (criação)
     - Não
     - ``0``.
   * - ``user.firstaccess``
     - Sim (criação)
     - Não
     - Timestamp atual.
   * - ``user.currentlogin``
     - Sim (criação)
     - Não
     - Timestamp atual.
   * - ``user.lastip``
     - Sim (criação)
     - Não
     - IP remoto (``getremoteaddr()``).
   * - ``user.firstnamephonetic`` / ``lastnamephonetic`` / ``middlename`` / ``alternatename``
     - Sim (criação)
     - Não
     - ``null``.
   * - ``user.firstname``
     - Sim
     - Sim
     - Nome completo escolhido entre ``nome_social``/``nome_usual``/``nome_registro`` conforme
       a configuração ``name_source_order``, dividido conforme ``name_split_rule`` (ver seção
       abaixo).
   * - ``user.lastname``
     - Sim
     - Sim
     - Idem acima.
   * - ``user.email``
     - Sim
     - Sim
     - ``email_preferencial`` → ``email`` → ``email_secundario``.
   * - ``user.auth``
     - Sim
     - Sim
     - ``suap``.
   * - ``user.suspended``
     - Sim
     - Sim
     - ``0``.
   * - ``user.picture``
     - Sim (se houver foto)
     - Sim (se houver foto)
     - ``url_foto_150x200`` → ``url_foto_75x100`` → ``foto``, via ``process_new_icon``.
   * - ``profile_field_nome_apresentacao``
     - Sim
     - Sim
     - ``nome_usual``.
   * - ``profile_field_nome_completo``
     - Sim
     - Sim
     - ``nome_registro`` (ou ``nome``).
   * - ``profile_field_nome_social``
     - Sim
     - Sim
     - ``nome_social``.
   * - ``profile_field_email_secundario``
     - Sim
     - Sim
     - ``email_secundario``.
   * - ``profile_field_email_google_classroom``
     - Sim
     - Sim
     - ``email_google_classroom``.
   * - ``profile_field_email_academico``
     - Sim
     - Sim
     - ``email_academico``.
   * - ``profile_field_campus_sigla``
     - Sim
     - Sim
     - ``campus`` (ou ``vinculo.campus``).
   * - ``profile_field_last_login``
     - Sim
     - Sim
     - JSON completo do payload combinado do SUAP; usado para suporte.
   * - ``profile_field_tipo_usuario``
     - Sim
     - Sim
     - ``tipo_usuario``.
   * - ``profile_field_eh_servidor``
     - Sim
     - Sim
     - ``tipo_vinculo == "Servidor"``.
   * - ``profile_field_eh_aluno``
     - Sim
     - Sim
     - ``tipo_usuario == "Aluno"``.
   * - ``profile_field_eh_prestador``
     - Sim
     - Sim
     - ``tipo_vinculo == "Prestador de Serviço"``.
   * - ``profile_field_eh_usuarioexterno``
     - Sim
     - Sim
     - ``tipo_vinculo == "Prestador de Serviço"`` (mesma condição de ``eh_prestador``).
   * - ``profile_field_data_de_nascimento``
     - Sim
     - Sim
     - ``data_nascimento`` (ou ``data_de_nascimento``).
   * - ``profile_field_sexo``
     - Sim
     - Sim
     - ``sexo``.
   * - ``profile_field_cpf``
     - Sim
     - Sim
     - Apenas dígitos, preenchido com zeros à esquerda até 11 dígitos. **Descontinuado**, mas
       ainda pode ser recebido do SUAP.
   * - ``profile_field_rg``
     - Sim
     - Sim
     - ``rg``.
   * - ``profile_field_passaporte``
     - Sim
     - Sim
     - ``passaporte``. **Descontinuado**.
   * - ``profile_field_naturalidade``
     - Sim
     - Sim
     - ``naturalidade``.
   * - ``profile_field_filiacao_mae`` / ``filiacao_pai``
     - Sim
     - Sim
     - ``filiacao[0]`` / ``filiacao[1]``.
   * - ``profile_field_id_doc_certificado``
     - Sim (se CPF/passaporte)
     - Sim (se CPF/passaporte)
     - CPF mascarado (``000.000.000-00``) ou ``passaporte`` se não houver CPF.
   * - ``profile_field_tipo_doc_certificado``
     - Sim (se CPF/passaporte)
     - Sim (se CPF/passaporte)
     - ``CPF`` ou ``Passaporte``.
   * - ``profile_field_curso_modalidade`` / ``curso_nivel_ensino`` / ``vinculo_ativo`` /
       ``vinculo_cargo`` / ``vinculo_categoria``
     - Sim
     - Sim
     - Do vínculo cujo identificador bate com o ``username`` (``vinculos[].detalhamento``).
   * - ``profile_field_matricula_regular``
     - Sim
     - Sim
     - ``vinculo.matricula_regular``.

Atualização da foto do usuário
----------------------------------

A foto (``user.picture``) é tratada separadamente dos demais campos, pois exige baixar um
arquivo binário e processá-lo — potencialmente lento o suficiente para dar a impressão de que
é o próprio Moodle que está travando. Por isso, diferente dos demais campos (atualizados de
forma síncrona, no mesmo request), o download/processamento da foto roda **em segundo plano**,
via tarefa ad hoc, tanto no login quanto na ação em massa administrativa descrita mais abaixo.

Enfileiramento (não bloqueante)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

``auth_plugin_suap::queue_update_picture_task()`` (``auth.php``) é chamado ao final de
``create_or_update_user()``, depois que ``update_user_record()`` já persistiu
``profile_field_last_login`` — ou seja, o payload do SUAP já está salvo quando a tarefa
efetivamente rodar. O método:

1. Chama ``get_last_login_photo_sources()``, que monta a lista ordenada de URLs candidatas a
   partir do payload salvo, na ordem de preferência ``url_foto_150x200`` → ``url_foto_75x100``
   → ``foto``. Se nenhuma URL estiver presente, retorna ``false`` sem enfileirar nada.
2. Caso contrário, cria uma tarefa ad hoc ``auth_suap\task\update_user_picture_adhoc`` com
   ``userid`` como dado customizado e a enfileira via
   ``\core\task\manager::queue_adhoc_task($task, true)`` — o segundo argumento
   (``checkforexisting``) evita duplicar a tarefa se o mesmo usuário já tiver uma pendente.

O login (e a ação em massa) retornam imediatamente após enfileirar; o download em si só
acontece quando o cron processar a fila de tarefas ad hoc (normalmente dentro de 1 minuto,
conforme a frequência do cron do site).

Download e processamento (em segundo plano)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Quando o cron executa a tarefa, ``update_user_picture_adhoc::execute()``
(``classes/task/update_user_picture_adhoc.php``) recarrega o usuário pelo ``userid`` salvo,
busca as URLs candidatas com ``get_last_login_photo_sources()`` e chama
``auth_plugin_suap::update_picture()``, que:

1. **Download**: percorre a lista de URLs candidatas e tenta baixar cada uma via
   ``auth_suap_curl_get()`` (timeout de 5 segundos), parando na primeira que retornar
   conteúdo não vazio. Falhas de download (erro de cURL, HTTP ≥ 400, timeout) em uma URL não
   impedem a tentativa da próxima da lista.
2. **Processamento**: o conteúdo baixado é gravado em um arquivo temporário
   (``$CFG->tempdir/suapfoto<id do usuário>``) e processado por ``process_new_icon()`` (API
   core do Moodle), que gera o ícone de usuário e devolve o identificador do arquivo de
   imagem.
3. **Persistência**: se ``process_new_icon()`` retornar um identificador válido, o campo
   ``user.picture`` é atualizado diretamente via ``$DB->set_field()``.

``update_picture()`` retorna um ``bool`` — ``true`` somente se ``user.picture`` foi
efetivamente atualizado, ``false`` em qualquer ponto de falha. A tarefa ad hoc usa esse
retorno para escrever a mensagem ``mtrace()`` correta ("concluída" ou "falha") no log de
execução do cron/tarefa — ver nota abaixo sobre por que isso importa.

Tratamento de falhas
~~~~~~~~~~~~~~~~~~~~~~

Nenhuma falha nesse fluxo interrompe o login (nem a ação em massa) — na pior hipótese, o
usuário simplesmente fica sem foto atualizada. Como ``update_picture()`` só é chamado a partir
de tarefas em segundo plano (ad hoc ou agendada — ver notas abaixo), nenhuma exceção sobe até
o cron: a execução da tarefa sempre aparece como concluída com sucesso, mesmo quando a foto de
um ou mais usuários não pôde ser atualizada. Há dois canais distintos de registro para achar
essas falhas apesar disso:

* ``mtrace()`` (prefixo ``[AUTH SUAP]`` usado em todo o plugin), para **todo** ponto de falha,
  incluindo o detalhe técnico por tentativa — erro ao baixar uma URL específica (exceção de
  ``auth_suap_curl_get()``, capturada por ``\Throwable``), nenhuma URL resultando em conteúdo
  válido, ou ``process_new_icon()`` falhando. Sai como *Task output* da execução da tarefa,
  sempre (não depende de ``$CFG->debug``).
* o evento ``auth_suap\event\picture_update_failed`` (``classes/event/picture_update_failed.php``),
  disparado por ``update_picture()`` apenas nos dois desfechos que realmente importam para um
  administrador — **nenhuma URL resultou em conteúdo válido** ou **``process_new_icon()``
  falhou** — associado ao contexto e ao ``relateduserid`` do usuário afetado. Gravado pelo log
  store padrão do Moodle, filtrável por usuário/evento sem precisar abrir uma execução de
  tarefa específica.

.. note::
   Falhas de download de uma URL individual (quando ainda há outra URL candidata na lista a
   tentar) só passam por ``mtrace()`` — o evento só é disparado quando **todas** as URLs
   se esgotam sem sucesso, para não gerar uma entrada de log por tentativa.

Como localizar uma falha
~~~~~~~~~~~~~~~~~~~~~~~~~~~

1. **Logs de tarefas** — Administração do site → Servidor → Tarefas → Logs de tarefas
   (``/admin/tasklogs.php``). Abra a execução de ``auth_suap\task\update_user_picture_adhoc``
   (ad hoc, por usuário) ou ``auth_suap\task\backfill_user_pictures`` (agendada, em lote) e
   procure, no **Task output**, por ``[AUTH SUAP] Falha ao atualizar a foto`` — a mesma
   redação nas duas tarefas, para uma busca confiável independente de qual delas rodou. Cada
   ocorrência inclui o ``username``/``id`` do usuário afetado. Como a lista de execuções não
   distingue "sucesso" de "sucesso com falhas internas", é preciso abrir e ler o texto — não
   dá para filtrar apenas pelas execuções problemáticas.
2. **Relatório de Logs** — Administração do site → Relatórios → Logs (``/report/log/index.php``).
   Filtre por **Event name** = **"Picture update failed (SUAP)"** — listado sob o componente
   **"SUAP OAuth2 Authentication"** — ou selecione o usuário específico no filtro
   **All participants**: a coluna **Affected user** aponta para quem teve a foto com falha, e
   a **Description** traz o motivo. Diferente do Task output, este canal já é filtrável por
   usuário/evento diretamente na interface.
3. **Live logs** — Administração do site → Relatórios → Live logs
   (``/report/loglive/index.php``) mostra a mesma fonte do item 2 quase em tempo real; útil
   para acompanhar uma tentativa enquanto ela acontece — por exemplo, logo depois de confirmar
   a ação em massa ou de clicar em "Run now" em uma das tarefas.

.. tip::
   Os dois primeiros canais se complementam: o relatório de Logs (item 2) é o mais rápido para
   descobrir *quais* usuários tiveram falha e *por quê*; o Task output (item 1) é útil quando
   você já sabe qual execução investigar e quer o detalhe técnico completo, incluindo as
   tentativas por URL individual que não chegam a gerar evento.

.. note::
   ``auth_plugin_suap::update_picture()`` continua síncrono por si só — quem chama diretamente
   (como as duas tarefas descritas nesta página, que já rodam em segundo plano via cron) ainda
   bloqueia até o download terminar. É ``queue_update_picture_task()`` quem desacopla isso do
   request HTTP de login/ação em massa.

.. note::
   ``update_picture_from_last_login($usuario)`` é um método de conveniência que combina
   ``get_last_login_photo_sources()`` + ``update_picture()`` e retorna ``true`` apenas em
   sucesso real (``false`` tanto para "sem dados" quanto para "tentou e falhou"). Por isso as
   duas tarefas (``backfill_user_pictures`` e ``update_user_picture_adhoc``) chamam
   ``get_last_login_photo_sources()`` e ``update_picture()`` separadamente em vez de usar esse
   atalho — precisam distinguir "usuário sem dados de foto" (ignorado silenciosamente) de
   "havia dados, mas a atualização falhou" (contado e reportado via ``mtrace()`` como falha,
   além do evento ``picture_update_failed``). Uma versão anterior confundia os dois casos: o
   retorno de ``update_picture_from_last_login()`` só indicava "havia algo para tentar", não
   se a tentativa deu certo — o que fazia o log da tarefa dizer "concluída" mesmo quando o
   download falhava.

Tarefa agendada: preenchimento retroativo de fotos
-------------------------------------------------------

Como a foto só é buscada durante o login (ver acima), usuários que autenticaram antes de uma
URL de foto estar disponível — ou cujo download falhou naquele momento — ficam sem foto até o
próximo login. A classe ``auth_suap\task\backfill_user_pictures``
(``classes/task/backfill_user_pictures.php``), registrada em ``db/tasks.php``, resolve isso
sem exigir um novo login: ela reaproveita o payload do SUAP já salvo em
``profile_field_last_login`` no último login de cada usuário.

Critério de elegibilidade
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A tarefa seleciona usuários com ``auth = 'suap'``, não excluídos (``deleted = 0``) e sem foto
(``picture = 0``). Para cada um, chama ``auth_plugin_suap::get_last_login_photo_sources()``
(``auth.php``), que decodifica o JSON salvo em ``profile_field_last_login`` e só considera o
usuário elegível para download **se pelo menos um** dos atributos de foto previstos —
``url_foto_150x200``, ``url_foto_75x100`` ou ``foto``, na mesma ordem de prioridade usada em
``create_or_update_user()`` — estiver presente e não vazio. Usuários sem esses atributos são
simplesmente ignorados (não é uma condição de erro). Esse método é compartilhado com a ação em
massa descrita a seguir e com a tarefa ad hoc descrita acima.

O download e o processamento em si reutilizam ``auth_plugin_suap::update_picture()`` — o
mesmo método usado durante o login, com o mesmo tratamento de falhas descrito acima. Um JSON
inválido em ``profile_field_last_login`` também é registrado via ``debugging(...,
DEBUG_DEVELOPER)``, com o prefixo ``[AUTH SUAP]``.

Como executar
~~~~~~~~~~~~~~~

A tarefa é registrada **desabilitada** por padrão (roda apenas sob demanda). Para executá-la
pela interface web, sempre que desejar:

1. Acesse **Administração do site → Servidor → Tarefas → Tarefas agendadas**
   (*Site administration → Server → Tasks → Scheduled tasks*).
2. Localize **"SUAP: preencher fotos de usuários sem foto"**.
3. Clique em **Executar agora** (*Run now*) — disponível mesmo com a tarefa desabilitada; a
   saída (``mtrace``) é exibida na própria tela.

Se preferir execução automática recorrente, habilite a tarefa nessa mesma tela; o agendamento
padrão definido em ``db/tasks.php`` é diário, às 03h.

Ação em massa: atualizar foto pela listagem de usuários
-------------------------------------------------------------

Além da tarefa agendada (que varre todos os usuários sem foto de uma vez), é possível disparar
a mesma tentativa de atualização para um ou mais usuários específicos diretamente pela
listagem administrativa, sem precisar esperar o próximo ciclo da tarefa.

Capability ``auth/suap:updatepicture``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Definida em ``db/access.php`` (``captype`` ``write``, contexto de sistema), com ``CAP_ALLOW``
padrão apenas para o arquétipo ``manager``. Só quem tem essa capability no contexto de sistema
vê a opção no dropdown de ações em massa.

Onde aparece
~~~~~~~~~~~~~~

``classes/hook_callbacks.php`` escuta o hook core ``core_user\hook\extend_bulk_user_actions``
(registrado em ``db/hooks.php``) e adiciona a ação **"Atualizar foto (SUAP)"** ao grupo do
plugin no menu **"Com os usuários selecionados..."**, disponível tanto em
**Administração do site → Usuários → Listar usuários** (``admin/user.php``, marcando as
caixas de seleção por linha) quanto em **Ações em massa de usuários**
(``admin/user/user_bulk.php``).

Fluxo
~~~~~~

1. Selecione um ou mais usuários (checkbox por linha) e escolha **"Atualizar foto (SUAP)"** no
   dropdown de ações em massa.
2. ``updatepicture_bulk.php`` (raiz do plugin) exige a capability ``auth/suap:updatepicture``
   e pede confirmação, listando os nomes dos usuários selecionados.
3. Ao confirmar, para cada usuário selecionado chama
   ``auth_plugin_suap::queue_update_picture_task()`` — o mesmo método usado no login (ver
   "Enfileiramento (não bloqueante)" acima) — que enfileira uma tarefa ad hoc
   (``auth_suap\task\update_user_picture_adhoc``) em vez de baixar a foto na hora. Isso evita
   que a tela de administração de usuários trave enquanto os downloads acontecem; o
   processamento real ocorre no próximo ciclo do cron.
4. Ao final, exibe um resumo: quantos usuários tinham dados de foto do SUAP e tiveram a
   atualização **agendada para segundo plano**, e quantos foram ignorados por não ter esses
   dados salvos (nesse caso, nada é enfileirado).

Diferente da tarefa agendada, esta ação **não filtra** por ``picture = 0`` — pode ser usada
também para forçar uma nova tentativa em usuários que já têm foto.

Configuração do nome de exibição (nome social)
------------------------------------------------

``user.firstname``/``user.lastname`` são montados a partir de duas configurações do plugin
(*Site administration → Plugins → Authentication → SUAP OAuth2 Authentication*), em vez de uma
regra fixa em código — a regra de negócio já mudou várias vezes, então trocar de regra agora é
só uma mudança de configuração.

``name_source_order``: ordem de prioridade das fontes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Escolhe o nome completo entre ``nome_social``, ``nome_usual`` e ``nome_registro`` — o primeiro
não vazio, na ordem selecionada. ``nome_registro`` é sempre o fallback final, por ser o único
campo garantidamente presente no payload do SUAP; ``nome_social`` e ``nome_usual`` podem vir
ausentes ou como string vazia. Opções:

1. Nome social, nome usual, nome de registro (**padrão**)
2. Nome usual, nome social, nome de registro
3. Nome usual, nome de registro
4. Nome social, nome de registro
5. Apenas nome de registro

``name_split_rule``: regra de divisão em firstname/lastname
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Divide o nome completo escolhido acima em ``firstname``/``lastname``. Opções:

1. **Primeirão + Derradeiro**: ``firstname`` = primeira palavra, ``lastname`` = última palavra
   (nomes do meio são descartados).
2. **Primeiros + Derradeiro** (**padrão**): ``firstname`` = todas as palavras exceto a última,
   ``lastname`` = última palavra.
3. **Primeiro + Restante**: ``firstname`` = primeira palavra, ``lastname`` = todas as demais.

Um nome de uma única palavra sempre resulta em ``firstname == lastname`` (evita ``lastname``
vazio, que o Moodle não aceita bem), independente da regra escolhida.

Ambas as configurações são lidas por ``auth_plugin_suap::resolve_firstname_lastname()``
(``auth.php``), chamado tanto durante o login (``create_or_update_user()``) quanto pela tarefa
agendada abaixo.

Tarefa agendada: sincronização retroativa de nomes
------------------------------------------------------

Alterar ``name_source_order``/``name_split_rule`` só afeta usuários no próximo login deles. A
classe ``auth_suap\task\sync_user_names`` (``classes/task/sync_user_names.php``), registrada em
``db/tasks.php``, aplica a regra atual retroativamente a todos os usuários já existentes, sem
exigir novo login — mesmo princípio de reaproveitar ``profile_field_last_login`` já usado por
``backfill_user_pictures``.

Critério de elegibilidade
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A tarefa seleciona usuários com ``auth = 'suap'``, não excluídos (``deleted = 0``). Para cada
um, chama ``auth_plugin_suap::get_last_login_payload()`` para obter o JSON salvo; sem payload
salvo, o usuário é ignorado. Com payload, calcula
``resolve_firstname_lastname()`` e só grava (via ``user_update_user()``) se o resultado for
diferente do ``firstname``/``lastname`` atuais.

Como executar
~~~~~~~~~~~~~~~

A tarefa é registrada **desabilitada** por padrão (roda apenas sob demanda), tipicamente logo
após alterar uma das duas configurações acima:

1. Acesse **Administração do site → Servidor → Tarefas → Tarefas agendadas**
   (*Site administration → Server → Tasks → Scheduled tasks*).
2. Localize **"SUAP: sincronizar nomes de exibição dos usuários (nome social/usual/registro)"**.
3. Clique em **Executar agora** (*Run now*) — disponível mesmo com a tarefa desabilitada; a
   saída (``mtrace``) é exibida na própria tela.

Se preferir execução automática recorrente, habilite a tarefa nessa mesma tela; o agendamento
padrão definido em ``db/tasks.php`` é diário, às 03h.

Notas
-----

* ``cpf`` e ``passaporte`` estão marcados como descontinuados no código, mas continuam sendo
  recebidos e sincronizados quando o SUAP os retorna.
* ``profile_field_last_login`` guarda o JSON bruto recebido do SUAP a cada login — útil para
  diagnosticar problemas de sincronização, mas não deve ser tratado como fonte de dados
  estruturados por outros plugins.
* Se ``vinculo`` (objeto) estiver presente no payload combinado, campos adicionais como
  ``profile_field_situacao_vinculo``, ``profile_field_situacao_sistemica``,
  ``profile_field_ira``, ``profile_field_matriz_curricular``,
  ``profile_field_ingresso_periodo``, ``profile_field_curso_descricao``,
  ``profile_field_turno`` e ``profile_field_campus_curso`` também são preenchidos —
  ver ``auth.php::create_or_update_user()`` para a lista completa e a ordem de precedência
  entre ``vinculos[]`` (detalhamento por vínculo equivalente ao ``username``) e ``vinculo``
  (vínculo corrente).
