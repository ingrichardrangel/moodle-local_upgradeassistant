# Smart Upgrade Assistant 1.6.0-beta5-contextual-recheck

Smart Upgrade Assistant is a local Moodle plugin that guides authorised administrators through safer manual Moodle upgrade preparation. It does **not** replace Moodle files automatically and it does **not** execute Moodle upgrades invisibly.

## Funciones incluidas

- Persistent pre-upgrade reports.
- Risk score from 0 to 100.
- A documented review retains the risk detected in that report. After correcting the target installation, recheck the active report to verify the change and update its risk score without creating another report. Verified resolved findings and earlier decisions remain in the audit history.
- Rechecked plugin findings explain whether the plugin disappeared from the source code, was added to the destination code, or is now considered compatible. The /public cutover recommendation follows the detected XAMPP, cPanel, Windows or Linux server profile.
- Moodle lifecycle intelligence based on official Moodle release information, with local fallback data.
- Moodle-version rule engine with local built-in rules for every branch from 4.1 through 4.5 and 5.0 through 5.3.
- Future targets can supply partial PHP and database rules through their own `admin/environment.xml`; the minimum upgrade path remains unverified until independently confirmed.
- PDF and HTML report exports.
- Complete and redacted export modes.
- Auditable checklist linked to each report.
- Moodle 5.x requirements validation.
- Advanced plugin inventory and compatibility matrix.
- Server-type recommendations for XAMPP, cPanel/WHM, VPS Linux, Windows and Linux.
- Report history.
- Advanced capabilities.
- Expanded Privacy API coverage and user data export/delete support.
- Moodle Events API records for key actions.

## Installation

1. Copy the `upgradeassistant` folder into `local/upgradeassistant`.
2. Visit `/admin/index.php` as a site administrator.
3. Let Moodle create or upgrade the database tables.
4. Open Site administration → Server → Smart Upgrade Assistant.

## GitHub repository and automated checks

The repository is named `moodle-local_upgradeassistant`; its root contains `version.php`,
`classes/`, `db/`, `tests/` and `.github/workflows/ci.yml`. Commit these files at the
repository root. To install the plugin through Moodle's interface, use the separately
packaged ZIP whose root directory is `upgradeassistant`.

The CI workflow runs on every push and pull request. It installs the plugin into Moodle
4.1–4.5 and 5.0–5.3 beta with PHP versions supported by those branches, checks PHP syntax,
Moodle plugin validation, upgrade savepoints, coding style and Mustache templates, and
runs PHPUnit. Behat runs on 4.5 and 5.2. MariaDB is tested on all branches; PostgreSQL
is tested on 5.2 and 5.3 beta. A separate job tests the documented review JavaScript.

As of September 2026, Moodle 5.3 beta is on the official `main` branch. Once Moodle
publishes `MOODLE_503_STABLE`, update the two 5.3 entries in the CI matrix to that branch.
CI failures must be reviewed before treating the beta as a marketplace release candidate.

This plugin is distributed under GNU GPL version 3 or later; see `LICENSE`.

## Sensitive technical diagnostics

This plugin intentionally displays server paths, Moodle code roots, detected target installations, `/public` directory paths, `config.php` locations, plugin directories and upgrade evidence to authorised technical administrators.

These diagnostics are required for the plugin's purpose: helping Moodle administrators prepare manual upgrades with evidence, path validation, plugin comparison and auditable reports.

Access to full diagnostics is controlled by these capabilities:

- `local/upgradeassistant:viewsensitive`
- `local/upgradeassistant:export`
- `moodle/site:config` for complete exports

Users without sensitive diagnostics permission receive redacted views and redacted exports. Do not grant `local/upgradeassistant:viewsensitive` to non-technical roles.

## Export model

The plugin supports two report export modes:

- **Redacted export:** hides paths and sensitive environment data. Intended for institutional review, non-technical stakeholders or broad sharing.
- **Complete export:** includes full technical evidence and paths. Intended for authorised administrators and server operators only.

Complete exports require stronger permissions than redacted exports.

## Lifecycle synchronisation

The scheduled task can synchronise Moodle lifecycle data from Moodle Developer Resources. In closed institutional environments, disable this from the plugin settings using **Enable Moodle lifecycle synchronisation**.

When remote synchronisation is disabled or unavailable, the plugin uses bundled fallback lifecycle data.

## Privacy and retention

The plugin stores technical evidence such as report summaries, selected target paths, detected server profile, plugin inventory, lifecycle snapshots, checklist actions, audit logs, IP address and user agent. These records support upgrade traceability and institutional evidence.

When individual user data is deleted through Moodle's Privacy API, reports are anonymised where possible so that institutional upgrade evidence is preserved without keeping the personal user link.

## Important

Always test Moodle upgrades in a staging copy before touching production. This plugin generates guidance, evidence and risk diagnostics; it does not replace official Moodle documentation, backups, or institutional change-control procedures.

## Status

This build is marked as `MATURITY_BETA`. It keeps the existing diagnostic and reporting features while introducing the new four-step interface.


## Redacted notes and audit trail

Redacted exports also process checklist notes and audit notes to remove server paths,
`config.php` locations, dataroot values and other sensitive technical details. Full
notes remain available only in complete exports for authorised technical administrators.

## Lifecycle synchronisation setting

The `enablelifecyclesync` setting disables both the scheduled lifecycle synchronisation
and the manual refresh button. This is intended for institutions that do not allow
outbound HTTP requests from Moodle.

## Action permission hardening

This build validates every POST action against its exact capability set before any controller side effect runs. Wizard reset, checklist completion, scan path changes, target selection, lifecycle synchronisation, report generation and global site actions are all separated by capability.

When a `/public` directory is selected manually as a target, the plugin resolves and stores the Moodle application root while preserving `/public`, `config.php` and related path diagnostics in the target metadata for authorised technical users.


## Static parsing limitation

Moodle and plugin `version.php` files are parsed statically without executing code from
other installations. This is intentional for safety. The detector covers standard Moodle
`version.php` assignments and treats unusual constants, concatenations or complex dynamic
expressions as best-effort diagnostics that may require manual review.

## Privacy API checklist notes

When Moodle privacy deletion/anonymisation is requested for a user, checklist completion
ownership is anonymised and the related checklist note is cleared. This avoids leaving
personal free-text data attached to an anonymised checklist row while preserving the
institutional report structure.

## Conservative default capabilities

The `configure` capability is not granted to the manager archetype by default in this
build. Site administrators can assign it explicitly to technical roles that should manage
plugin settings, lifecycle synchronisation and global assistant configuration.



## 1.4.14 PDF lifecycle timeline fix

This iteration replaces Unicode block characters in the PDF/HTML lifecycle timeline with TCPDF-safe HTML table bars. Some PDF fonts render block characters as question marks, especially in local Windows/XAMPP environments. The lifecycle evidence now uses ASCII-safe labels and coloured HTML cells so both complete and redacted reports render cleanly.

## 1.4.13-alpha-public-detection-fix notes

Manual target selection now handles Moodle targets where the administrator selects the `public` folder directly. The assistant resolves the parent application root, preserves the selected folder as the public web root, and reads `version.php` from either the parent root or the selected public directory. This prevents Moodle 5.1/5.2+ installations from being shown as "public structure not detected" after manual target selection.


## 1.4.13 public directory detection fix

This iteration fixes manual target selection for Moodle 5.1+ / 5.2+ installations where administrators paste the web public directory directly, for example `C:\xampp\htdocs\moodle-5.2.1\moodle\public`. The detector now resolves the parent application root, keeps the selected public directory as `publicpath`, and can read `version.php` either from the application root or from the `public` directory.

## Novedades de la versión 1.5.0

- Interfaz reorganizada como un asistente real de cuatro pasos: Diagnóstico, Versión destino, Preparación y Ejecución.
- Cabecera compacta con versión actual, destino, progreso, bloqueos y nivel de riesgo.
- Informes e historial separados del recorrido operativo.
- Selección automática y manual de destino unificada en una sola fase.
- Compatibilidad técnica, reglas, plugins y recomendaciones agrupadas en detalles desplegables.
- Checklist operativo y checklist auditable sincronizados.
- Exportaciones agrupadas en un único menú.
- Licenciamiento movido a la configuración del plugin.
- Uso de iconos Font Awesome, variables CSS, diseño adaptable y compatibilidad visual con modo oscuro.
- Eliminación de archivos de registro del paquete distribuible.

## 1.5.1-beta evaluation fixes

- Restarting the assistant now removes the current administrator's generated reports and their dependent checklist, audit, plugin and export records. Reports created by other administrators are preserved.
- Maintenance mode is automatically recognized as completed in both the preparation checklist and newly generated auditable checklists, even when it was enabled before opening the assistant.
- The duplicated plugin copy-path list was removed from phase 4; plugin compatibility remains available in phase 3 and in reports.


## 1.5.2-beta lifecycle context fix

- Una rama de origen fuera de soporte deja de tratarse como una tarea abierta cuando el destino seleccionado es una rama posterior y soportada.
- Esa condición se conserva como evidencia informativa con el estado “Mitigado por la actualización”, pero no suma puntos al nivel de riesgo.
- Los informes existentes se reconcilian automáticamente al abrirse y recalculan su riesgo sin necesidad de generarlos otra vez.
- Una versión destino que también esté fuera de soporte sí se mantiene como hallazgo abierto de severidad alta.


## 1.5.3-beta auditable plugin review

- Las dependencias declaradas se contrastan automáticamente con los componentes y versiones presentes en la instalación destino.
- Una declaración de dependencias ya no suma riesgo por sí sola; únicamente lo hacen dependencias confirmadas como ausentes o insuficientes.
- Los hallazgos de plugins que requieren criterio humano incorporan una nota obligatoria y auditable para marcarlos como revisados.
- La nota registra administrador, fecha y hora, se conserva en el informe y aparece en las exportaciones.
- Los hallazgos revisados dejan de sumar al nivel de riesgo sin desaparecer de la evidencia.
- `auth_mnet` y otros componentes MNet retirados oficialmente del núcleo desde Moodle 5.0 se reconocen como cambios esperados del destino.
- Los componentes estándar ausentes del destino se diferencian de plugins personalizados perdidos y quedan como revisión informativa, no como riesgo automático.
- Los informes generados con versiones anteriores se reconcilian al abrirse para corregir los hallazgos genéricos de dependencias y `auth_mnet`.


## 1.5.4-beta production cutover flow

- La ejecución desde Moodle 4.4/5.0 hacia Moodle 5.1+ ahora representa correctamente un cambio real de producción.
- La carpeta antigua se renombra como respaldo y la raíz preparada adopta el nombre de la carpeta de producción.
- El dominio de producción debe cambiar su Document Root hacia el directorio `public` de la nueva raíz.
- `config.php` se copia a la raíz superior y conserva la URL, la base de datos y moodledata de producción.
- Se eliminan las indicaciones incorrectas de definir `$CFG->dirroot` y cargar directamente `public/lib/setup.php`.
- Se muestran las rutas finales de actualización, purga de cachés y cron después del renombrado.
- La interfaz advierte que la carpeta de desarrollo utilizada como destino deja de ser una instalación de desarrollo independiente.

## 1.5.6-beta future branches and documented review

- Moodle 5.3 tiene una regla local con sus requisitos publicados y un aviso para destinos preliminares.
- Las ramas futuras se identifican mediante `version.php`; los requisitos PHP y de base de datos declarados en el `admin/environment.xml` del paquete destino se comprueban sin ejecutar su código.
- Si el paquete no permite verificar la ruta mínima de actualización u otros requisitos, el informe mantiene una advertencia abierta y permite registrar la justificación del administrador.
- La revisión documentada de hallazgos se guarda sin cerrar el panel de resultados ni perder la ubicación en la página; sin JavaScript, continúa funcionando el formulario tradicional.

## 1.5.7-beta documented review fixes

- El formulario de revisión recibe una confirmación JSON del servidor y actualiza el hallazgo y la puntuación sin cerrar la lista; los errores aparecen junto al botón.
- El hallazgo «La versión destino todavía es futura» admite revisión documentada y conserva el criterio del administrador en el informe y en el PDF.
- La reconciliación del ciclo de vida respeta los hallazgos ya revisados y no los vuelve a abrir.

## 1.5.8-beta documented review URL fix

- El envío dinámico utiliza el atributo URL del formulario. El campo oculto llamado `action` ya no puede sustituir la dirección y provocar una solicitud HTTP 404.
- Una prueba JavaScript reproduce la colisión de nombres del navegador para evitar que este fallo reaparezca.

## 1.5.9-beta HTML export fix

- Las exportaciones HTML completas y redactadas se descargan con cabeceras HTTP estándar. Se elimina la llamada a `send_content()`, que no existe en Moodle.
- Las comprobaciones de capacidades y sesión del endpoint y la exportación PDF permanecen en el mismo flujo.

## 1.6.0-beta Moodle 4.1–5.3 routes

- Se reconocen las ramas intermedias Moodle 4.2, 4.3, 4.4 y 5.1, con sus requisitos oficiales de PHP y bases de datos.
- El asistente compara el parche instalado con el mínimo de origen, por ejemplo 4.1.2 para pasar a 4.4/4.5 y 4.2.3 para pasar a 5.0/5.1.
- Las rutas encadenadas indican cuándo actualizar primero la propia rama 4.1 antes de saltar a 4.4 o 4.5. Si no se puede leer el parche, la ruta queda pendiente de verificación.
- Se corrigieron los requisitos de MariaDB, PostgreSQL y SQL Server de reglas anteriores. Las reglas locales incluidas en versiones anteriores se sustituyen en tiempo de lectura; las reglas personalizadas conservan su prioridad.
