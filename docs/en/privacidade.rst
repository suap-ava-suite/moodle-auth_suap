Privacy
=======

``auth_suap`` implements Moodle's privacy API (``core_privacy``) in
``classes/privacy/provider.php``. The plugin does not store personal data in its own
tables — the synchronized data lives in the standard ``user`` table and in
``user_info_data`` (custom profile fields), both outside the direct scope of this
*provider*.

What the provider declares
-----------------------------

``provider::get_metadata()`` registers an **external location link** (``suap``), describing
the data sent to the external SUAP service during authentication and synchronization:

.. list-table::
   :header-rows: 1
   :widths: 30 70

   * - Field
     - Description
   * - ``username``
     - Username (institutional ID).
   * - ``email``
     - E-mail address.
   * - ``firstname``
     - User's first name.
   * - ``lastname``
     - User's last name.
   * - ``cpf``
     - User's CPF (Brazilian tax document).
   * - ``tipo``
     - User type/role (student, staff, teacher, etc.).

This allows Moodle's **Site administration → Privacy and policies → User data registry**
screen to correctly list SUAP as an external destination for this data.

.. note::
   This *provider* implements only ``metadata_provider``. Since the plugin does not persist
   user data in tables specific to the component (outside the standard ``user``/
   ``user_info_data``), it does not implement the export/deletion interfaces
   (``core_userlist_provider`` / ``plugin\privacy\provider`` with ``local_data``) — export and
   deletion of this data follow Moodle's standard flow for the ``user`` table and custom
   profile fields.
