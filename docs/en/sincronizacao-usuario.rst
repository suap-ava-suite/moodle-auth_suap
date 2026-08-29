User synchronization
======================

Profile fields created automatically
---------------------------------------

On install/upgrade, ``auth_suap_bulk_user_custom_field()`` (``db/migrate.php``, called by
``db/install.php`` and ``db/upgrade.php``) creates the profile field categories **SUAP**,
**Dados pessoais** (Personal data), **Dados de contato** (Contact data), **Matrícula**
(Enrollment), **Polo** (Hub/Pole), **Campus**, **Curso** (Course) and **Turma** (Class), and
registers the fields:

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

All fields are created with ``locked = 1`` (locked for editing by the user) and
``visible = 1``, except ``last_login``, which is hidden (``visible = 0``). At the end, the
lock is replicated (``field_lock_profile_field_<shortname> = locked``) to **all** installed
authentication plugins, not just ``auth_suap``.

Fields changed on first login vs. subsequent logins
--------------------------------------------------------

Table based on the creation/update flow in ``auth.php::create_or_update_user()``.

.. list-table::
   :header-rows: 1
   :widths: 28 12 12 48

   * - Field
     - 1st login
     - Subsequent
     - Source / notes
   * - ``user.username``
     - Yes (creation)
     - No
     - ``identificacao`` or ``matricula`` (lowercased), from SUAP.
   * - ``user.password``
     - Yes (creation)
     - No
     - Random local password; ignored (authentication is always via ``suap``).
   * - ``user.timezone``
     - Yes (creation)
     - No
     - ``99``.
   * - ``user.confirmed``
     - Yes (creation)
     - No
     - ``1``.
   * - ``user.mnethostid``
     - Yes (creation)
     - No
     - ``1``.
   * - ``user.policyagreed``
     - Yes (creation)
     - No
     - ``0``.
   * - ``user.deleted``
     - Yes (creation)
     - No
     - ``0``.
   * - ``user.firstaccess``
     - Yes (creation)
     - No
     - Current timestamp.
   * - ``user.currentlogin``
     - Yes (creation)
     - No
     - Current timestamp.
   * - ``user.lastip``
     - Yes (creation)
     - No
     - Remote IP (``getremoteaddr()``).
   * - ``user.firstnamephonetic`` / ``lastnamephonetic`` / ``middlename`` / ``alternatename``
     - Yes (creation)
     - No
     - ``null``.
   * - ``user.firstname``
     - Yes
     - Yes
     - Full name chosen among ``nome_social``/``nome_usual``/``nome_registro`` according to
       the ``name_source_order`` setting, split according to ``name_split_rule`` (see section
       below).
   * - ``user.lastname``
     - Yes
     - Yes
     - Same as above.
   * - ``user.email``
     - Yes
     - Yes
     - ``email_preferencial`` → ``email`` → ``email_secundario``.
   * - ``user.auth``
     - Yes
     - Yes
     - ``suap``.
   * - ``user.suspended``
     - Yes
     - Yes
     - ``0``.
   * - ``user.picture``
     - Yes (if a photo is available)
     - Yes (if a photo is available)
     - ``url_foto_150x200`` → ``url_foto_75x100`` → ``foto``, via ``process_new_icon``.
   * - ``profile_field_nome_apresentacao``
     - Yes
     - Yes
     - ``nome_usual``.
   * - ``profile_field_nome_completo``
     - Yes
     - Yes
     - ``nome_registro`` (or ``nome``).
   * - ``profile_field_nome_social``
     - Yes
     - Yes
     - ``nome_social``.
   * - ``profile_field_email_secundario``
     - Yes
     - Yes
     - ``email_secundario``.
   * - ``profile_field_email_google_classroom``
     - Yes
     - Yes
     - ``email_google_classroom``.
   * - ``profile_field_email_academico``
     - Yes
     - Yes
     - ``email_academico``.
   * - ``profile_field_campus_sigla``
     - Yes
     - Yes
     - ``campus`` (or ``vinculo.campus``).
   * - ``profile_field_last_login``
     - Yes
     - Yes
     - Full JSON of the combined SUAP payload; used for support.
   * - ``profile_field_tipo_usuario``
     - Yes
     - Yes
     - ``tipo_usuario``.
   * - ``profile_field_eh_servidor``
     - Yes
     - Yes
     - ``tipo_vinculo == "Servidor"``.
   * - ``profile_field_eh_aluno``
     - Yes
     - Yes
     - ``tipo_usuario == "Aluno"``.
   * - ``profile_field_eh_prestador``
     - Yes
     - Yes
     - ``tipo_vinculo == "Prestador de Serviço"``.
   * - ``profile_field_eh_usuarioexterno``
     - Yes
     - Yes
     - ``tipo_vinculo == "Prestador de Serviço"`` (same condition as ``eh_prestador``).
   * - ``profile_field_data_de_nascimento``
     - Yes
     - Yes
     - ``data_nascimento`` (or ``data_de_nascimento``).
   * - ``profile_field_sexo``
     - Yes
     - Yes
     - ``sexo``.
   * - ``profile_field_cpf``
     - Yes
     - Yes
     - Digits only, zero-padded on the left up to 11 digits. **Deprecated**, but may still be
       received from SUAP.
   * - ``profile_field_rg``
     - Yes
     - Yes
     - ``rg``.
   * - ``profile_field_passaporte``
     - Yes
     - Yes
     - ``passaporte``. **Deprecated**.
   * - ``profile_field_naturalidade``
     - Yes
     - Yes
     - ``naturalidade``.
   * - ``profile_field_filiacao_mae`` / ``filiacao_pai``
     - Yes
     - Yes
     - ``filiacao[0]`` / ``filiacao[1]``.
   * - ``profile_field_id_doc_certificado``
     - Yes (if CPF/passport)
     - Yes (if CPF/passport)
     - Masked CPF (``000.000.000-00``) or ``passaporte`` if there is no CPF.
   * - ``profile_field_tipo_doc_certificado``
     - Yes (if CPF/passport)
     - Yes (if CPF/passport)
     - ``CPF`` or ``Passaporte``.
   * - ``profile_field_curso_modalidade`` / ``curso_nivel_ensino`` / ``vinculo_ativo`` /
       ``vinculo_cargo`` / ``vinculo_categoria``
     - Yes
     - Yes
     - From the relationship whose identifier matches the ``username``
       (``vinculos[].detalhamento``).
   * - ``profile_field_matricula_regular``
     - Yes
     - Yes
     - ``vinculo.matricula_regular``.

Updating the user's photo
-----------------------------

The photo (``user.picture``) is handled separately from the other fields, since it requires
downloading a binary file and processing it — potentially slow enough to give the impression
that Moodle itself is hanging. So, unlike the other fields (updated synchronously, in the same
request), the photo download/processing runs **in the background**, via an ad hoc task, both
on login and in the administrative bulk action described further below.

Queueing (non-blocking)
~~~~~~~~~~~~~~~~~~~~~~~~~~

``auth_plugin_suap::queue_update_picture_task()`` (``auth.php``) is called at the end of
``create_or_update_user()``, after ``update_user_record()`` has already persisted
``profile_field_last_login`` — that is, the SUAP payload is already saved by the time the
task actually runs. The method:

1. Calls ``get_last_login_photo_sources()``, which builds the ordered list of candidate URLs
   from the saved payload, in the priority order ``url_foto_150x200`` → ``url_foto_75x100``
   → ``foto``. If no URL is present, it returns ``false`` without queueing anything.
2. Otherwise, it creates an ad hoc task ``auth_suap\task\update_user_picture_adhoc`` with
   ``userid`` as custom data and queues it via
   ``\core\task\manager::queue_adhoc_task($task, true)`` — the second argument
   (``checkforexisting``) prevents duplicating the task if the same user already has one
   pending.

The login (and the bulk action) return immediately after queueing; the actual download only
happens when cron processes the ad hoc task queue (typically within 1 minute, depending on
the site's cron frequency).

Download and processing (in the background)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When cron runs the task, ``update_user_picture_adhoc::execute()``
(``classes/task/update_user_picture_adhoc.php``) reloads the user by the saved ``userid``,
fetches the candidate URLs with ``get_last_login_photo_sources()`` and calls
``auth_plugin_suap::update_picture()``, which:

1. **Download**: goes through the list of candidate URLs and tries to download each one via
   ``auth_suap_curl_get()`` (5-second timeout), stopping at the first one that returns
   non-empty content. Download failures (cURL error, HTTP ≥ 400, timeout) on one URL do not
   prevent trying the next one in the list.
2. **Processing**: the downloaded content is written to a temporary file
   (``$CFG->tempdir/suapfoto<user id>``) and processed by ``process_new_icon()`` (Moodle core
   API), which generates the user icon and returns the image file identifier.
3. **Persistence**: if ``process_new_icon()`` returns a valid identifier, the ``user.picture``
   field is updated directly via ``$DB->set_field()``.

``update_picture()`` returns a ``bool`` — ``true`` only if ``user.picture`` was actually
updated, ``false`` at any point of failure. The ad hoc task uses this return value to write
the correct ``mtrace()`` message ("completed" or "failed") to the cron/task execution log —
see the note below on why this matters.

Failure handling
~~~~~~~~~~~~~~~~~~

No failure in this flow interrupts the login (nor the bulk action) — at worst, the user
simply ends up without an updated photo. Since ``update_picture()`` is only called from
background tasks (ad hoc or scheduled — see notes below), no exception bubbles up to cron:
the task execution always shows as completed successfully, even when the photo of one or more
users could not be updated. There are two distinct logging channels to find these failures
despite that:

* ``mtrace()`` (prefix ``[AUTH SUAP]`` used throughout the plugin), for **every** point of
  failure, including the technical detail per attempt — error downloading a specific URL
  (exception from ``auth_suap_curl_get()``, caught via ``\Throwable``), no URL resulting in
  valid content, or ``process_new_icon()`` failing. Shown as *Task output* of the task
  execution, always (does not depend on ``$CFG->debug``).
* the ``auth_suap\event\picture_update_failed`` event
  (``classes/event/picture_update_failed.php``), fired by ``update_picture()`` only in the
  two outcomes that actually matter to an administrator — **no URL resulted in valid
  content** or **``process_new_icon()`` failed** — associated with the context and the
  ``relateduserid`` of the affected user. Recorded by Moodle's standard log store, filterable
  by user/event without needing to open a specific task execution.

.. note::
   Failures downloading an individual URL (when there is still another candidate URL in the
   list to try) only go through ``mtrace()`` — the event is only fired when **all** URLs are
   exhausted without success, so as not to generate a log entry per attempt.

How to find a failure
~~~~~~~~~~~~~~~~~~~~~~~~

1. **Task logs** — Site administration → Server → Tasks → Task logs
   (``/admin/tasklogs.php``). Open the execution of
   ``auth_suap\task\update_user_picture_adhoc`` (ad hoc, per user) or
   ``auth_suap\task\backfill_user_pictures`` (scheduled, in bulk) and search, in the
   **Task output**, for ``[AUTH SUAP] Falha ao atualizar a foto`` (Failed to update photo) —
   the same wording in both tasks, for a reliable search regardless of which one ran. Each
   occurrence includes the affected user's ``username``/``id``. Since the list of executions
   does not distinguish "success" from "success with internal failures", you need to open and
   read the text — you cannot filter only for problematic executions.
2. **Logs report** — Site administration → Reports → Logs
   (``/report/log/index.php``). Filter by **Event name** = **"Picture update failed (SUAP)"**
   — listed under the **"SUAP OAuth2 Authentication"** component — or select the specific
   user in the **All participants** filter: the **Affected user** column points to who had a
   failed photo, and the **Description** shows the reason. Unlike the Task output, this
   channel is already filterable by user/event directly in the interface.
3. **Live logs** — Site administration → Reports → Live logs
   (``/report/loglive/index.php``) shows the same source as item 2 nearly in real time;
   useful for following an attempt as it happens — for example, right after confirming the
   bulk action or clicking "Run now" on one of the tasks.

.. tip::
   The first two channels complement each other: the Logs report (item 2) is the fastest way
   to discover *which* users had a failure and *why*; the Task output (item 1) is useful when
   you already know which execution to investigate and want the full technical detail,
   including the individual per-URL attempts that don't generate an event.

.. note::
   ``auth_plugin_suap::update_picture()`` remains synchronous by itself — whoever calls it
   directly (like the two tasks described on this page, which already run in the background
   via cron) still blocks until the download finishes. It is ``queue_update_picture_task()``
   that decouples this from the login/bulk-action HTTP request.

.. note::
   ``update_picture_from_last_login($usuario)`` is a convenience method that combines
   ``get_last_login_photo_sources()`` + ``update_picture()`` and returns ``true`` only on
   actual success (``false`` for both "no data" and "tried and failed"). That's why the two
   tasks (``backfill_user_pictures`` and ``update_user_picture_adhoc``) call
   ``get_last_login_photo_sources()`` and ``update_picture()`` separately instead of using
   this shortcut — they need to distinguish "user without photo data" (silently ignored) from
   "there was data, but the update failed" (counted and reported via ``mtrace()`` as a
   failure, plus the ``picture_update_failed`` event). A previous version conflated the two
   cases: the return value of ``update_picture_from_last_login()`` only indicated "there was
   something to try", not whether the attempt succeeded — which made the task log say
   "completed" even when the download failed.

Scheduled task: retroactive photo backfill
-----------------------------------------------

Since the photo is only fetched during login (see above), users who authenticated before a
photo URL was available — or whose download failed at that time — remain without a photo
until their next login. The ``auth_suap\task\backfill_user_pictures`` class
(``classes/task/backfill_user_pictures.php``), registered in ``db/tasks.php``, solves this
without requiring a new login: it reuses the SUAP payload already saved in
``profile_field_last_login`` from each user's last login.

Eligibility criteria
~~~~~~~~~~~~~~~~~~~~~~~

The task selects users with ``auth = 'suap'``, not deleted (``deleted = 0``) and without a
photo (``picture = 0``). For each one, it calls
``auth_plugin_suap::get_last_login_photo_sources()`` (``auth.php``), which decodes the JSON
saved in ``profile_field_last_login`` and only considers the user eligible for download **if
at least one** of the expected photo attributes — ``url_foto_150x200``, ``url_foto_75x100``
or ``foto``, in the same priority order used in ``create_or_update_user()`` — is present and
non-empty. Users without these attributes are simply skipped (this is not an error
condition). This method is shared with the bulk action described next and with the ad hoc
task described above.

The download and processing itself reuse ``auth_plugin_suap::update_picture()`` — the same
method used during login, with the same failure handling described above. Invalid JSON in
``profile_field_last_login`` is also logged via ``debugging(...,
DEBUG_DEVELOPER)``, with the ``[AUTH SUAP]`` prefix.

How to run it
~~~~~~~~~~~~~~~~

The task is registered **disabled** by default (runs only on demand). To run it through the
web interface, whenever desired:

1. Go to **Site administration → Server → Tasks → Scheduled tasks**.
2. Find **"SUAP: preencher fotos de usuários sem foto"** (SUAP: fill photos for users
   without one).
3. Click **Run now** — available even with the task disabled; the output (``mtrace``) is
   shown on the screen itself.

If you prefer recurring automatic execution, enable the task on that same screen; the default
schedule defined in ``db/tasks.php`` is daily, at 3 AM.

Bulk action: update photo from the user listing
----------------------------------------------------

Besides the scheduled task (which scans all users without a photo at once), it is possible to
trigger the same update attempt for one or more specific users directly from the
administrative listing, without waiting for the next task cycle.

``auth/suap:updatepicture`` capability
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Defined in ``db/access.php`` (``captype`` ``write``, system context), with default
``CAP_ALLOW`` only for the ``manager`` archetype. Only those who have this capability in the
system context see the option in the bulk actions dropdown.

Where it appears
~~~~~~~~~~~~~~~~~~~

``classes/hook_callbacks.php`` listens to the core hook
``core_user\hook\extend_bulk_user_actions`` (registered in ``db/hooks.php``) and adds the
**"Atualizar foto (SUAP)"** (Update photo (SUAP)) action to the plugin's group in the
**"With selected users..."** menu, available both in **Site administration → Users → Browse
list of users** (``admin/user.php``, checking the per-row checkboxes) and in **Bulk user
actions** (``admin/user/user_bulk.php``).

Flow
~~~~~~

1. Select one or more users (per-row checkbox) and choose **"Atualizar foto (SUAP)"** in the
   bulk actions dropdown.
2. ``updatepicture_bulk.php`` (plugin root) requires the ``auth/suap:updatepicture``
   capability and asks for confirmation, listing the names of the selected users.
3. Upon confirmation, for each selected user it calls
   ``auth_plugin_suap::queue_update_picture_task()`` — the same method used during login (see
   "Queueing (non-blocking)" above) — which queues an ad hoc task
   (``auth_suap\task\update_user_picture_adhoc``) instead of downloading the photo right
   away. This avoids the user administration screen freezing while the downloads happen; the
   actual processing occurs on the next cron cycle.
4. At the end, it shows a summary: how many users had SUAP photo data and had the update
   **scheduled in the background**, and how many were skipped for not having that data saved
   (in that case, nothing is queued).

Unlike the scheduled task, this action **does not filter** by ``picture = 0`` — it can also
be used to force a new attempt on users who already have a photo.

Display name configuration (social name)
--------------------------------------------

``user.firstname``/``user.lastname`` are built from two plugin settings (*Site
administration → Plugins → Authentication → SUAP OAuth2 Authentication*), rather than a fixed
rule in code — the business rule has already changed several times, so switching rules now is
just a configuration change.

``name_source_order``: source priority order
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Chooses the full name among ``nome_social``, ``nome_usual`` and ``nome_registro`` — the first
non-empty one, in the selected order. ``nome_registro`` is always the final fallback, being
the only field guaranteed to be present in the SUAP payload; ``nome_social`` and
``nome_usual`` may be absent or an empty string. Options:

1. Social name, usual name, registration name (**default**)
2. Usual name, social name, registration name
3. Usual name, registration name
4. Social name, registration name
5. Registration name only

``name_split_rule``: firstname/lastname split rule
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Splits the full name chosen above into ``firstname``/``lastname``. Options:

1. **First + Last**: ``firstname`` = first word, ``lastname`` = last word (middle names are
   discarded).
2. **All but last + Last** (**default**): ``firstname`` = all words except the last,
   ``lastname`` = last word.
3. **First + Remainder**: ``firstname`` = first word, ``lastname`` = all the remaining ones.

A single-word name always results in ``firstname == lastname`` (avoids an empty ``lastname``,
which Moodle does not handle well), regardless of the chosen rule.

Both settings are read by ``auth_plugin_suap::resolve_firstname_lastname()`` (``auth.php``),
called both during login (``create_or_update_user()``) and by the scheduled task below.

Scheduled task: retroactive name synchronization
------------------------------------------------------

Changing ``name_source_order``/``name_split_rule`` only affects users on their next login.
The ``auth_suap\task\sync_user_names`` class (``classes/task/sync_user_names.php``),
registered in ``db/tasks.php``, retroactively applies the current rule to all existing users,
without requiring a new login — the same principle of reusing
``profile_field_last_login`` already used by ``backfill_user_pictures``.

Eligibility criteria
~~~~~~~~~~~~~~~~~~~~~~~

The task selects users with ``auth = 'suap'``, not deleted (``deleted = 0``). For each one, it
calls ``auth_plugin_suap::get_last_login_payload()`` to get the saved JSON; without a saved
payload, the user is skipped. With a payload, it computes
``resolve_firstname_lastname()`` and only writes (via ``user_update_user()``) if the result
differs from the current ``firstname``/``lastname``.

How to run it
~~~~~~~~~~~~~~~~

The task is registered **disabled** by default (runs only on demand), typically right after
changing one of the two settings above:

1. Go to **Site administration → Server → Tasks → Scheduled tasks**.
2. Find **"SUAP: sincronizar nomes de exibição dos usuários (nome social/usual/registro)"**
   (SUAP: synchronize user display names (social/usual/registration name)).
3. Click **Run now** — available even with the task disabled; the output (``mtrace``) is
   shown on the screen itself.

If you prefer recurring automatic execution, enable the task on that same screen; the default
schedule defined in ``db/tasks.php`` is daily, at 3 AM.

Notes
-----

* ``cpf`` and ``passaporte`` are marked as deprecated in the code, but continue to be
  received and synchronized when SUAP returns them.
* ``profile_field_last_login`` stores the raw JSON received from SUAP on every login — useful
  for troubleshooting synchronization issues, but should not be treated as a structured data
  source by other plugins.
* If ``vinculo`` (object) is present in the combined payload, additional fields such as
  ``profile_field_situacao_vinculo``, ``profile_field_situacao_sistemica``,
  ``profile_field_ira``, ``profile_field_matriz_curricular``,
  ``profile_field_ingresso_periodo``, ``profile_field_curso_descricao``,
  ``profile_field_turno`` and ``profile_field_campus_curso`` are also filled in — see
  ``auth.php::create_or_update_user()`` for the complete list and the precedence order
  between ``vinculos[]`` (per-relationship detail equivalent to ``username``) and ``vinculo``
  (current relationship).
