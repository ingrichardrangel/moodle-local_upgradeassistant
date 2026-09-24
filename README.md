# Upgrade Assistant 1.6

Upgrade Assistant is a free local plugin for Moodle administrators preparing a manual upgrade. It examines the current site and a Moodle code tree prepared as the upgrade target, highlights potential problems, and records the checks and decisions made before the change. The plugin provides guidance and evidence; administrators remain in control of the upgrade.

## What it does

- Detects Moodle installations in permitted server locations or accepts a manually selected target directory.
- Compares the current and target Moodle branches and checks the known upgrade path, PHP version, database requirements, and other environment information available to the plugin.
- Compares plugin code in the current and target installations, including missing components, declared dependencies, and compatibility indicators. Results that cannot be confirmed automatically are identified for review.
- Displays Moodle release lifecycle information, with bundled fallback data when remote synchronisation is unavailable or disabled.
- Recognises a target that uses a `/public` web root and tailors cutover guidance to the detected XAMPP, cPanel, Windows, or Linux server profile.
- Provides preparation tasks, a technical report, a risk score, an audit trail, and PDF or HTML exports.

## Preparation workflow

The assistant has four stages:

1. **Diagnosis:** Inspect the current site, server environment, and available Moodle installations.
2. **Target version:** Select the prepared Moodle code tree and review the upgrade route and target requirements.
3. **Preparation:** Work through backups, maintenance, theme, server, and plugin checks. Record the steps that require an administrator's action or confirmation.
4. **Execution and evidence:** Generate a preparation report, review its findings, and follow the manual cutover instructions applicable to the target installation.

The target must be available on the server as a separate code tree. Selecting it does not replace the running Moodle installation. Some actions in the assistant, such as enabling maintenance mode or purging caches, run only when an authorised administrator explicitly requests them.

## Reports and risk

The report groups detected findings by subject and assigns a risk score from 0 to 100. The score represents the findings present at the latest check; it is a preparation indicator, not a guarantee that an upgrade will succeed.

After changing the current or target installation, select **Recheck report**. The assistant examines both code trees again and updates the active report without creating another one. A finding that is no longer detected is marked resolved and stops contributing to the score. For plugin findings, the report can indicate whether the component disappeared from the source code, appeared in the target code, or is now considered compatible. These conclusions describe the code that was inspected; the assistant cannot prove from a file scan that a plugin was uninstalled through Moodle's interface.

Some risks cannot be removed before the upgrade, such as choosing a prerelease target. An authorised administrator can provide a written rationale and record a decision to proceed. The finding remains visible and continues to contribute to the risk score. The report records the administrator, time, and rationale. Rechecks and changes to findings are retained in the audit trail.

The report offers **complete** and **redacted** PDF and HTML exports. Complete exports contain technical evidence such as server paths and are restricted to administrators with the required permissions. Redacted exports hide sensitive paths and environment details for wider institutional review.

## Installation

1. Place the `upgradeassistant` directory at `local/upgradeassistant` in your Moodle installation, or install the distributable plugin ZIP through Moodle's plugin installer.
2. Sign in as a site administrator and complete Moodle's plugin upgrade at `/admin/index.php`.
3. Open **Site administration → Server → Upgrade Assistant**.

This release requires Moodle 4.1 or later. The bundled upgrade rules cover Moodle 4.1–4.5 and 5.0–5.3, including a preliminary target in the 5.3 branch. A newly released branch may be detected from its files, but its complete requirements and upgrade route still need verified rules; review unknown results against the official Moodle documentation.

## Access and data

Moodle capabilities control who can view the assistant, manage preparation, generate or export reports, and see sensitive diagnostics. Grant sensitive access only to trusted technical administrators. Plugin settings include an option to disable scheduled and manual lifecycle synchronisation for environments that do not permit outbound requests.

Reports store technical diagnostics, selected paths, plugin comparisons, checklist records, and audit information. Moodle's Privacy API supports export and deletion or anonymisation of relevant user data. **Reset assistant** clears the current administrator's assistant state and the reports that administrator created, including their associated records; reports created by other administrators are preserved.

## Scope

Upgrade Assistant does not install or remove plugins, copy Moodle code, perform database upgrades, create backups, or modify the web server's document root. It does not execute code from the selected target installation. Follow Moodle's official upgrade documentation, test in a staging environment, and keep restorable backups before changing a production site.

The plugin is distributed under the GNU General Public License, version 3 or later. See [LICENSE](LICENSE).
