<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * English language strings for Smart Upgrade Assistant.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['accessdeniedpath'] = 'The selected path is outside the allowed scan roots.';
$string['action'] = 'Action';
$string['administratorcriterion'] = 'Administrator documented rationale';
$string['administratorcriterionhelp'] = 'Your decision is recorded with the user, date and time. ' .
    'Reviewing a finding does not change Moodle requirements; the organisation must verify your ' .
    'rationale before performing the upgrade.';
$string['administratorcriterionlabel'] = 'Administrative rationale, evidence or decision';
$string['administratorcriterionplaceholder'] = 'Describe what you verified, the evidence consulted, and ' .
    'why you consider this finding addressed or accepted.';
$string['advancedpluginmatrix'] = 'Advanced plugin matrix';
$string['analysecompatibility'] = 'Analyse compatibility';
$string['analysisdowngrade'] = 'Downgrade is not allowed. The selected installation is older than the current Moodle version.';
$string['analysisnotnext'] = 'The selected version is higher, but its minimum source version is Moodle ' .
    '{$a}. Update to that version first, then reassess the route.';
$string['analysissamebranch'] = 'The selected installation has the same branch as the current platform. ' .
    'It does not represent an upgrade step.';
$string['analysisunknownbranch'] = 'The versions could not be fully compared because one of the Moodle branches was not detected.';
$string['analysisunknownpatch'] = 'The installed Moodle patch release could not be verified. Confirm ' .
    'that it is at least Moodle {$a} before upgrading directly.';
$string['analysisunverifiedroute'] = 'The target branch was detected, but the minimum supported source ' .
    'Moodle version could not yet be verified.';
$string['analysisvalidnext'] = 'The selected installation is higher than the current one and matches the ' .
    'next recommended upgrade step.';
$string['assistanttab'] = 'Assistant';
$string['attentionchecks'] = 'Need attention';
$string['auditablechecklist'] = 'Auditable checklist';
$string['auditedfromwizard'] = 'Imported from the current wizard state when the report was generated.';
$string['auditnoteplaceholder'] = 'Optional audit note or evidence reference';
$string['auditstatuscompleted'] = 'Completed';
$string['auditstatuspending'] = 'Pending';
$string['auditstepcompleted'] = 'Auditable checklist step completed.';
$string['back'] = 'Back';
$string['backupdbfilename'] = 'backup_moodle_database_before_upgrade.sql';
$string['blockedchecks'] = 'Technical blockers';
$string['blockers'] = 'Blockers';
$string['blockersremaining'] = '{$a} blocker(s) must be resolved before it is safe to continue.';
$string['boostthemeactive'] = 'Boost active';
$string['boostthemenotactive'] = 'The active theme is {$a}. Change the site theme to Boost before marking this step as verified.';
$string['boostthemeswitched'] = 'The site theme was changed to Boost and the checklist step was marked as completed.';
$string['boostthemeswitchednote'] = 'The site theme was changed to Boost from Smart Upgrade Assistant.';
$string['boostthemeverified'] = 'Boost theme verified successfully.';
$string['boostthemeverifiednote'] = 'The active theme was verified as Boost from Smart Upgrade Assistant.';
$string['branch'] = 'Branch';
$string['cachespurged'] = 'All Moodle caches were purged.';
$string['category'] = 'Category';
$string['check'] = 'Check';
$string['checkbackupcode'] = 'Current Moodle code folder backup';
$string['checkbackupcodedesc'] = 'Create a full copy of the current Moodle code folder before replacing files.';
$string['checkbackupdata'] = 'moodledata backup';
$string['checkbackupdatadesc'] = 'Create a full copy of the moodledata folder. This contains private ' .
    'files, submissions, caches and critical course data.';
$string['checkbackupdb'] = 'Database backup';
$string['checkbackupdbdesc'] = 'From cPanel phpMyAdmin or SSH, export a full Moodle database backup in SQL format.';
$string['checkboosttheme'] = 'Use the default Boost theme temporarily';
$string['checkboostthemeauditdesc'] = 'Verify and document that the site is using the default Boost ' .
    'theme before replacing Moodle code.';
$string['checkboostthemedesc'] = 'Current active theme: {$a}. Before replacing Moodle code, use Boost as ' .
    'the temporary site theme to reduce the risk of theme incompatibilities during the upgrade.';
$string['checkconfirmtarget'] = 'Confirm that the new version is already installed or extracted';
$string['checkconfirmtargetdesc'] = 'The target Moodle folder must exist on the server and contain its ' .
    'own version.php file. It must not be an empty folder or an unextracted ZIP.';
$string['checklistdesc'] = 'Automatic buttons run controlled Moodle actions. Done buttons only record ' .
    'that you already completed an external action in cPanel, File Manager, phpMyAdmin, FTP or SSH.';
$string['checklisttitle'] = 'Preparation';
$string['checkmaintenance'] = 'Enable maintenance mode';
$string['checkmaintenancedesc'] = 'Enable maintenance mode to prevent students and teachers from using ' .
    'the platform while you prepare the upgrade.';
$string['checkpurgecaches'] = 'Purge caches before the process';
$string['checkpurgecachesdesc'] = 'Clear Moodle caches before replacing files to reduce visual and ' .
    'loading errors after the upgrade.';
$string['checkservercompat'] = 'Verify server compatibility';
$string['checkservercompatdesc'] = 'Manually verify that the target Moodle version is compatible with ' .
    'your PHP, database and extensions. Currently detected: {$a}.';
$string['checkservercompatprodesc'] = 'Review the automated findings in the pre-upgrade report and ' .
    'confirm that the server is ready for the target Moodle version.';
$string['codedir'] = 'Code directory';
$string['compatibility'] = 'Compatibility';
$string['compatibilitymatrix'] = 'Compatibility matrix';
$string['compatibilitymatrixdesc'] = 'This assistant uses a conservative matrix for the supported ' .
    'upgrade route and checks the detected PHP version against known target milestones.';
$string['compatibilitywithtarget'] = 'Compatibility with Moodle {$a}';
$string['compatible'] = 'Compatible';
$string['compatiblechecks'] = 'Checks passed';
$string['compatiblestatuserror'] = 'Not compatible';
$string['compatiblestatusinfo'] = 'Analysis pending';
$string['compatiblestatussuccess'] = 'Compatible';
$string['compatiblestatuswarning'] = 'Intermediate route required';
$string['completed'] = 'Completed';
$string['completedby'] = 'Completed by';
$string['component'] = 'Component';
$string['continuepreparation'] = 'Continue preparation';
$string['copyfrom'] = 'Copy from';
$string['copyto'] = 'Copy to';
$string['criticalzone'] = 'Manual execution';
$string['criticalzonedesc'] = 'When you rename the current Moodle folder, the site may temporarily stop ' .
    'responding or send you to the native upgrade screen. This is expected.';
$string['criticalzonedescstrong'] = 'From this point you should not depend on buttons inside Moodle.';
$string['current'] = 'Current';
$string['currentinstallation'] = 'Current installation';
$string['currenttheme'] = 'Current theme';
$string['currentvalue'] = 'Current value';
$string['currentversion'] = 'Current version';
$string['database'] = 'Database';
$string['databaseversion'] = 'Database version';
$string['date'] = 'Date';
$string['detected'] = 'Detected';
$string['detectedfolder'] = 'Detected folder';
$string['detectedprofile'] = 'Detected profile';
$string['detectedstatus'] = 'Detected status';
$string['diagnosisheading'] = 'Environment diagnosis';
$string['diagnosisintro'] = 'Check the current installation and review only the information needed ' .
    'before choosing a target version.';
$string['disablemaintenance'] = 'Disable';
$string['done'] = 'Done';
$string['downloadhtmlreport'] = 'Download HTML report';
$string['downloadpdfreport'] = 'Download PDF report';
$string['downloadredactedhtmlreport'] = 'Download redacted HTML report';
$string['downloadredactedpdfreport'] = 'Download redacted PDF report';
$string['enablelifecyclesync'] = 'Enable Moodle lifecycle synchronisation';
$string['enablelifecyclesync_desc'] = 'Allow scheduled and manual synchronisation of Moodle release ' .
    'lifecycle information from Moodle Developer Resources. Disable this in closed environments that ' .
    'must not make external HTTP requests.';
$string['enablemaintenance'] = 'Enable maintenance mode';
$string['environmentdetected'] = 'Environment detected';
$string['eventchecklistcompleted'] = 'Upgrade assistant checklist item completed';
$string['eventlifecyclesynced'] = 'Moodle lifecycle data synchronised';
$string['eventmaintenancedisabled'] = 'Maintenance mode disabled from Smart Upgrade Assistant';
$string['eventmaintenanceenabled'] = 'Maintenance mode enabled from Smart Upgrade Assistant';
$string['eventreportcreated'] = 'Upgrade assistant report created';
$string['eventreportexported'] = 'Upgrade assistant report exported';
$string['eventtargetselected'] = 'Upgrade assistant target selected';
$string['eventthemeswitchedtoboost'] = 'Site theme switched to Boost from Smart Upgrade Assistant';
$string['evidenceandtraceability'] = 'Evidence and traceability';
$string['executionblocked'] = 'Execution still has blockers. Return to Preparation and resolve them before changing files.';
$string['executionheading'] = 'Execution and evidence';
$string['executionintro'] = 'Review the final status, generate the preparation report and open manual ' .
    'instructions only when you are ready.';
$string['exportmenu'] = 'Export';
$string['exportprivacywarning'] = 'Complete exports may include sensitive server diagnostics. Use ' .
    'redacted exports when sharing reports outside the technical team.';
$string['field'] = 'Field';
$string['finding'] = 'Finding';
$string['findingboostthemecheckpending'] = 'Boost theme confirmation is pending';
$string['findingboostthemecheckpendingdesc'] = 'The auditable checklist has not confirmed that the site ' .
    'is using Boost as the temporary theme for the upgrade window.';
$string['findingboostthemecheckpendingrec'] = 'Verify or switch the site theme to Boost before replacing Moodle code.';
$string['findingboostthemeok'] = 'Boost theme is active';
$string['findingboostthemeokdesc'] = 'The site is using Boost as the active theme, which reduces the ' .
    'risk of theme incompatibilities during the upgrade.';
$string['findingboostthemeokrec'] = 'Keep Boost active until the upgraded site has been validated.';
$string['findingboostthemepending'] = 'The active theme is not Boost';
$string['findingboostthemependingdesc'] = 'The active theme is currently {$a}. Custom or third-party ' .
    'themes may break during or immediately after a Moodle upgrade.';
$string['findingboostthemependingrec'] = 'Switch the site theme to Boost before the upgrade window and ' .
    'reactivate the custom theme only after validating compatibility.';
$string['findingchecklistpending'] = '{$a} is still pending';
$string['findingchecklistpendingdesc'] = 'This critical preparation task was not marked as completed ' .
    'when the report was generated.';
$string['findingchecklistpendingrec'] = 'Complete this task before replacing Moodle files and record it ' .
    'in the auditable checklist.';
$string['findingdbcritical'] = 'Database version is below the recommended minimum';
$string['findingdbcriticaldesc'] = '{$a->type} {$a->current} was detected, but the target branch ' .
    'requires {$a->required} or higher.';
$string['findingdbcriticalrec'] = 'Upgrade the database engine or choose a target Moodle version ' .
    'compatible with the current database server.';
$string['findingdbok'] = 'Database version passed the first compatibility check';
$string['findingdbokdesc'] = 'Detected database: {$a}.';
$string['findingdbokrec'] = 'Keep this evidence and still confirm the official Moodle environment page before the upgrade.';
$string['findingdbruleunknown'] = 'Database rule could not be fully matched';
$string['findingdbruleunknowndesc'] = 'Detected database details: {$a}.';
$string['findingdbruleunknownrec'] = 'Manually verify the database requirements for the selected target branch.';
$string['findingdbunknown'] = 'Database version could not be detected';
$string['findingdbunknowndesc'] = 'The database description was: {$a}.';
$string['findingdbunknownrec'] = 'Confirm the database version from the server panel, CLI or hosting support before upgrading.';
$string['findinglifecyclecurrentsecurity'] = 'Current branch is in security support';
$string['findinglifecyclecurrentsecuritydesc'] = '{$a->version} no longer receives general support, but ' .
    'it keeps security support until {$a->date}.';
$string['findinglifecyclecurrentsecurityrec'] = 'Keep the branch patched with minor releases and plan ' .
    'the next LTS route when stable.';
$string['findinglifecyclecurrentunknown'] = 'The current branch could not be mapped to the official lifecycle';
$string['findinglifecyclecurrentunknowndesc'] = 'The detected branch ({$a}) does not appear in the local lifecycle matrix.';
$string['findinglifecyclecurrentunsupported'] = 'Current branch is officially unsupported';
$string['findinglifecyclecurrentunsupporteddesc'] = '{$a} no longer receives general support or official ' .
    'security fixes according to the available lifecycle matrix.';
$string['findinglifecyclecurrentunsupportedmitigated'] = 'The selected upgrade addresses the unsupported source branch';
$string['findinglifecyclecurrentunsupportedmitigateddesc'] = '{$a->current} is officially unsupported, ' .
    'but the selected destination ({$a->target}) is a newer supported branch. The upgrade plan itself ' .
    'mitigates this condition, so it does not increase the preparation risk score.';
$string['findinglifecyclecurrentunsupportedmitigatedrec'] = 'Continue with the technical checks and ' .
    'backups. No separate task is required to resolve the source branch lifecycle state.';
$string['findinglifecyclecurrentunsupportedrec'] = 'Plan an upgrade to a supported branch, preferably ' .
    'LTS, before touching production.';
$string['findinglifecycletargetfuture'] = 'The target version is still future';
$string['findinglifecycletargetfuturedesc'] = '{$a->version} has a planned release date of {$a->date}; ' .
    'it should not be used as a production target before stable release.';
$string['findinglifecycletargetfuturerec'] = 'Use a supported stable branch or wait for the official ' .
    'release before production planning.';
$string['findinglifecycletargetlts'] = 'The target version belongs to an LTS branch';
$string['findinglifecycletargetltsdesc'] = '{$a->version} is an LTS branch with security support until {$a->date}.';
$string['findinglifecycletargetltsrec'] = 'For institutional environments, prioritising an LTS route ' .
    'reduces frequent major changes.';
$string['findinglifecycletargetnonlts'] = 'The target version is not LTS';
$string['findinglifecycletargetnonltsdesc'] = '{$a} is a non-LTS stable branch. It can be valid, but it ' .
    'is not the preferred choice for institutions prioritising extended support.';
$string['findinglifecycletargetnonltsrec'] = 'Evaluate whether it is better to remain on a supported LTS ' .
    'or plan the next stable LTS.';
$string['findinglifecycletargetunknown'] = 'The target branch could not be mapped to the official lifecycle';
$string['findinglifecycletargetunknowndesc'] = 'The target branch ({$a}) does not appear in the local lifecycle matrix.';
$string['findinglifecycletargetunsupported'] = 'The selected target version is also unsupported';
$string['findinglifecycletargetunsupporteddesc'] = '{$a} no longer receives general support or official ' .
    'security fixes. Upgrading to this branch does not resolve the lifecycle exposure.';
$string['findinglifecycletargetunsupportedrec'] = 'Select a newer branch that still has general or security support.';
$string['findinglifecycleunknownrec'] = 'Refresh the lifecycle matrix from Moodle HQ or manually ' .
    'validate the version in the official documentation.';
$string['findingmaintenancepending'] = 'Maintenance mode was not marked as completed';
$string['findingmaintenancependingdesc'] = 'The report was generated without the maintenance step being marked as completed.';
$string['findingmaintenancependingrec'] = 'Enable maintenance mode before the file replacement window.';
$string['findingmaxinputvars'] = 'PHP max_input_vars may be too low';
$string['findingmaxinputvarsdesc'] = 'Detected max_input_vars value: {$a}.';
$string['findingmaxinputvarsrec'] = 'Increase max_input_vars to at least 5000 for modern Moodle branches.';
$string['findingnotreviewable'] = 'This finding cannot be manually resolved or has already been reviewed.';
$string['findingpathblocked'] = 'Upgrade path is blocked';
$string['findingpathblockedrec'] = 'Select a valid higher Moodle branch and avoid downgrades.';
$string['findingpathok'] = 'Upgrade path matched the next recommended step';
$string['findingpathokrec'] = 'Continue with the preparation checklist and keep this report as evidence.';
$string['findingpathreview'] = 'Upgrade path requires review';
$string['findingpathreviewrec'] = 'Use the next recommended intermediate branch before jumping to the selected target.';
$string['findingpathunknown'] = 'Upgrade path could not be fully analysed';
$string['findingpathunknowndesc'] = 'The current or target Moodle branch could not be detected with enough confidence.';
$string['findingpathunknownrec'] = 'Confirm both version.php files and select a clear target installation.';
$string['findingpathunverifiedrec'] = 'Check the target release upgrade requirements and document the chosen route.';
$string['findingphpbits'] = 'PHP is not running in 64-bit mode';
$string['findingphpbitsdesc'] = 'Modern Moodle branches require a 64-bit PHP runtime.';
$string['findingphpbitsrec'] = 'Move the site to a 64-bit PHP runtime before upgrading.';
$string['findingphpcritical'] = 'PHP version is below the minimum required';
$string['findingphpcriticaldesc'] = 'Detected PHP {$a->current}; required PHP {$a->required} or higher.';
$string['findingphpcriticalrec'] = 'Change the PHP version before attempting the upgrade.';
$string['findingphpok'] = 'PHP version passed the first compatibility check';
$string['findingphpokdesc'] = 'Detected PHP version: {$a}.';
$string['findingphpokrec'] = 'Keep this evidence and still verify extensions from the Moodle environment page.';
$string['findingphpruleunknown'] = 'PHP rule is not available for this target branch';
$string['findingphpruleunknowndesc'] = 'The assistant does not have a local PHP minimum rule for the selected target branch.';
$string['findingphpruleunknownrec'] = 'Update the rule set or verify the target branch requirements manually.';
$string['findingplugincorechangereview'] = 'Verify core change for {$a}';
$string['findingplugincorechangereviewdesc'] = 'This component belongs to the standard source ' .
    'distribution but is not present in the target code. It may have been officially removed or migrated,' .
    ' so it does not add risk automatically.';
$string['findingplugincorechangereviewrec'] = 'Check the official upgrade notes and record whether the ' .
    'component was removed, replaced, or requires an additional action.';
$string['findingplugindependencymissing'] = 'Missing dependencies for {$a}';
$string['findingplugindependencymissingdesc'] = '{$a}';
$string['findingplugindependencymissingrec'] = 'Add the missing dependencies to the target code, run the ' .
    'analysis again, and document the verification performed.';
$string['findingplugindependencyoutdated'] = 'Insufficient dependency versions for {$a}';
$string['findingplugindependencyoutdateddesc'] = '{$a}';
$string['findingplugindependencyoutdatedrec'] = 'Upgrade the listed dependencies to the required minimum ' .
    'version and run the analysis again.';
$string['findingplugindependencyreview'] = 'Verify declared dependencies for {$a}';
$string['findingplugindependencyreviewdesc'] = 'The plugin declares dependencies, but at least one ' .
    'version could not be conclusively confirmed in the target code. This administrative review does not add risk by itself.';
$string['findingplugindependencyreviewrec'] = 'Check the dependencies in the target installation and ' .
    'record the rationale or evidence used.';
$string['findingpluginofficialremoval'] = '{$a} was officially removed from Moodle core';
$string['findingpluginofficialremovaldesc'] = '{$a->component} belongs to the source core and was ' .
    'officially removed starting with Moodle {$a->target} ({$a->issue}). Its absence from the target ' .
    'code is expected and does not mean that a custom plugin was lost.';
$string['findingpluginofficialremovalrec'] = 'Do not copy or try to uninstall this component manually ' .
    'before upgrading. Keep the finding as evidence of the official core change.';
$string['findingpluginreview'] = 'Review plugin {$a}';
$string['findingpluginreviewdesc'] = 'Detected status: {$a}.';
$string['findingpluginreviewrec'] = 'Confirm plugin compatibility, update the plugin or remove it before upgrading.';
$string['findingpluginsok'] = 'No custom plugin folder differences detected';
$string['findingpluginsokdesc'] = 'The basic comparison did not find plugin folders missing in the target installation.';
$string['findingpluginsokrec'] = 'Still confirm plugin compatibility manually before the final replacement.';
$string['findingpublicstructure'] = 'The current site uses a /public structure';
$string['findingpublicstructuredesc'] = 'The assistant detected a public directory in the current Moodle code tree.';
$string['findingpublicstructurerec'] = 'Confirm that the web server DocumentRoot points to the correct ' .
    'public directory after the upgrade.';
$string['findingreviewed'] = 'The administrative review of the finding was completed and documented.';
$string['findingreviewerror'] = 'The review could not be confirmed. Check the connection and try again.';
$string['findingreviewsavedrefresh'] = 'The review was saved, but this finding could not be refreshed. ' .
    'Reload the page to see it; do not submit it again.';
$string['findingsodiumcritical'] = 'PHP sodium extension is missing';
$string['findingsodiumcriticaldesc'] = 'The target Moodle branch requires the sodium extension, but it is not loaded in PHP.';
$string['findingsodiumcriticalrec'] = 'Enable the sodium extension before upgrading.';
$string['findingstatusclosed'] = 'Resolved';
$string['findingstatusmitigated'] = 'Mitigated by upgrade';
$string['findingstatusofficialremoval'] = 'Handled by the upgrade';
$string['findingstatusopen'] = 'Open';
$string['findingstatusreviewed'] = 'Reviewed by administrator';
$string['findingtargetprerelease'] = 'The target version is a prerelease';
$string['findingtargetprereleasedesc'] = 'The target installation is a Moodle prerelease. Requirements ' .
    'and compatibility may change before the stable release.';
$string['findingtargetprereleaserec'] = 'Check current prerelease requirements, test the upgrade in a ' .
    'staging environment, and document the decision before proceeding.';
$string['findingtargetpublicstructure'] = 'The target platform uses a /public structure';
$string['findingtargetpublicstructuredesc'] = 'The assistant detected the public directory in the target ' .
    'platform: {$a}. This finding does not increase risk; it changes the replacement procedure.';
$string['findingtargetpublicstructurerec'] = 'Rename the old application root as a backup, place the ' .
    'prepared root under the production folder name, and configure the domain DocumentRoot to serve its ' .
    'public directory. Keep config.php in the parent application root.';
$string['generatedby'] = 'Generated by';
$string['generatedon'] = 'Generated on';
$string['generatepreparationreport'] = 'Generate report';
$string['generateproreport'] = 'Generate report';
$string['headerhelp'] = 'Prepare the upgrade step by step, resolve blockers and keep evidence before changing server files.';
$string['herodescription'] = 'Safe assistant for preparing a Moodle upgrade. This plugin guides, ' .
    'verifies and documents the process. It does not replace Moodle files while the site is running.';
$string['htmlcomplete'] = 'Complete HTML';
$string['htmlredacted'] = 'HTML with sensitive data hidden';
$string['importantsummary'] = 'Important summary';
$string['importantsummarydesc'] = 'This plugin is designed to guide novice administrators with real ' .
    'paths, exact folder names and safe manual steps. It does not automatically replace files because ' .
    'doing so from the running platform can break Moodle during execution.';
$string['indicator'] = 'Indicator';
$string['installationscaption'] = 'Detected Moodle installations';
$string['instructionclassicmodeactive'] = 'Classic mode detected: the target platform does not use ' .
    '/public. In this case, the folder renaming replacement procedure applies.';
$string['instructionclassictargetdetected'] = 'The target platform does not have a /public structure. ' .
    'The assistant will use the classic procedure: rename the current folder and place the target folder ' .
    'using the production folder name.';
$string['instructioncopyconfig'] = 'Copy config.php from the old folder into the new folder. If asked to ' .
    'replace the file, choose Yes / Replace.';
$string['instructionfinish'] = 'When the upgrade finishes, purge caches again and disable maintenance mode.';
$string['instructionlocatecurrent'] = 'Locate the current Moodle folder.';
$string['instructionlocatetarget'] = 'Locate the target Moodle version folder selected in this assistant.';
$string['instructionnewoldname'] = 'Rename it to this exact backup folder name. Do not delete it.';
$string['instructionnewtargetpath'] = 'After renaming, the new Moodle path should be the following.';
$string['instructionopenfilemanager'] = 'Open cPanel File Manager or connect by FTP/SSH.';
$string['instructionpublicconfigcheck'] = 'Review the copied config.php: it must keep the production URL,' .
    ' database credentials, table prefix and moodledata path. Do not add $CFG->dirroot or change the standard setup loading line.';
$string['instructionpubliccopyconfig'] = 'Copy the production config.php from the old folder into the ' .
    'new application root. It must remain outside the public directory.';
$string['instructionpublicdevnote'] = 'The prepared {$a->targetfolder} folder becomes the production ' .
    'code under the name {$a->currentfolder}. Configure the development domain later with a separate ' .
    'installation if you want to keep it.';
$string['instructionpublicdocumentroot'] = 'In cPanel or the web server configuration, change the ' .
    'production domain DocumentRoot so it points exactly to the final public directory.';
$string['instructionpubliceditroots'] = 'Verify that config.php keeps the production URL, database and ' .
    'moodledata values. Do not set $CFG->dirroot manually.';
$string['instructionpublicfinalroot'] = 'Confirm the final structure. The first path is Moodle private ' .
    'application root and the second is the only directory the web server should publish.';
$string['instructionpublicfinish'] = 'After the upgrade finishes, purge caches, update the cron task to ' .
    'use the new path under public, verify the site, and only then re-enable cron and disable maintenance mode.';
$string['instructionpublicmodeactive'] = '/public mode detected: the prepared application root may ' .
    'replace the production root by name, but the domain must serve only its public directory.';
$string['instructionpublicnorenames'] = 'Rename the old folder as a backup and place the prepared ' .
    'application root under the production folder name. Do not rename the public directory separately.';
$string['instructionpublicpreflight'] = 'Before cutover, confirm maintenance mode is enabled, cron is ' .
    'stopped, no tasks are still running, and code, database and moodledata backups are available.';
$string['instructionpublicrenamecurrent'] = 'Rename the current production application root using the ' .
    'indicated backup name. Do not delete it: it contains the previous code and the config.php that will be copied.';
$string['instructionpublicrenametarget'] = 'Move or rename the prepared target Moodle application root ' .
    'so it takes the exact production folder name.';
$string['instructionpublicrouting'] = 'When using Apache or OpenLiteSpeed, confirm that the .htaccess ' .
    'file and routing rules required by Moodle 5.2 are present inside public.';
$string['instructionpublicsetupinclude'] = 'Keep the standard require_once(__DIR__ . \'/lib/setup.php\') ' .
    'line. Moodle internally loads the library located under public.';
$string['instructionrenamecurrent'] = 'Right-click the current folder with this exact name.';
$string['instructionrenametarget'] = 'Rename the target folder using the current production folder name.';
$string['instructionrunupgrade'] = 'Open Moodle native upgrader in the browser or run the CLI upgrade command by SSH.';
$string['instructionsblocked'] = 'Replacement instructions are not generated because the selected ' .
    'version represents a downgrade or an invalid upgrade route.';
$string['instructionsnotarget'] = 'First select a target installation so the assistant can generate ' .
    'instructions using the real folder names.';
$string['instructionsnotdirect'] = 'The selected folder is higher than the current version, but the ' .
    'assistant recommends not using it yet as a direct upgrade step.';
$string['instructiontargetpublicdetected'] = 'The target platform uses a /public structure. Confirm the ' .
    'detected application root and public directory.';
$string['invalidaction'] = 'Invalid assistant action.';
$string['invalidexporttype'] = 'Invalid export type.';
$string['invalidfinding'] = 'The selected finding is invalid.';
$string['invalidreportid'] = 'Invalid report ID.';
$string['invalidstep'] = 'Invalid checklist step.';
$string['latestreport'] = 'Latest report';
$string['lifecycleandtimeline'] = 'Lifecycle and support timeline';
$string['lifecyclecurrentstatus'] = 'Lifecycle';
$string['lifecycledesc'] = 'Fetches and caches the official Moodle support matrix to show lifecycle ' .
    'start, general support end, security support end, and LTS recommendation.';
$string['lifecyclefallbackmode'] = 'Using local fallback';
$string['lifecyclefuturelts'] = 'next LTS';
$string['lifecyclegeneralsupport'] = 'General support';
$string['lifecyclegeneraluntil'] = 'General support until:';
$string['lifecyclelastsync'] = 'Last sync';
$string['lifecyclelts'] = 'LTS';
$string['lifecycleltspriority'] = 'LTS priority';
$string['lifecyclepdfsectiondesc'] = 'This section documents the official known support status at the ' .
    'time the report was generated. It serves as evidence for upgrade strategy decisions.';
$string['lifecyclepdfsectiontitle'] = 'Moodle version lifecycle';
$string['lifecyclerecommendfuturetarget'] = '{$a->target} is planned for {$a->date}, but it should not ' .
    'be recommended as a production target until stable release.';
$string['lifecyclerecommendltsok'] = '{$a} is a supported LTS branch. For institutional production, this ' .
    'branch is a preferred option when site requirements are met.';
$string['lifecyclerecommendltssecurity'] = '{$a->current} is an LTS branch with security support until ' .
    '{$a->date}. Keep minor patches current and plan {$a->future} when stable.';
$string['lifecyclerecommendnonlts'] = '{$a->target} is a non-LTS stable branch. For institutional ' .
    'environments, compare this route against {$a->lts} before deciding.';
$string['lifecyclerecommendstable'] = 'The current branch is supported, but institutional environments ' .
    'should prioritise {$a} or the next stable LTS.';
$string['lifecyclerecommendunknown'] = 'An automatic lifecycle recommendation could not be generated. ' .
    'Check the official Moodle HQ matrix.';
$string['lifecyclerecommendunsupported'] = 'The current branch is unsupported. Plan an upgrade to {$a} ' .
    'or to another supported stable branch.';
$string['lifecyclerefresh'] = 'Synchronise lifecycle';
$string['lifecyclerefreshed'] = 'The Moodle lifecycle matrix was synchronised or updated from the available local fallback.';
$string['lifecyclerelease'] = 'Release:';
$string['lifecyclesecuritysupport'] = 'Security support';
$string['lifecyclesecurityuntil'] = 'Security until:';
$string['lifecyclesource'] = 'Source';
$string['lifecyclestatusfuture'] = 'Future release';
$string['lifecyclestatusgeneral'] = 'General support';
$string['lifecyclestatussecurity'] = 'Security support';
$string['lifecyclestatusunsupported'] = 'Unsupported';
$string['lifecyclesyncdisabled'] = 'Moodle lifecycle synchronisation is disabled in plugin settings.';
$string['lifecycletimeline'] = 'Support timeline';
$string['lifecycletitle'] = 'Moodle lifecycle';
$string['local/upgradeassistant:configure'] = 'Configure Smart Upgrade Assistant';
$string['local/upgradeassistant:export'] = 'Export Smart Upgrade Assistant reports';
$string['local/upgradeassistant:generatereport'] = 'Generate Smart Upgrade Assistant reports';
$string['local/upgradeassistant:manage'] = 'Manage Smart Upgrade Assistant';
$string['local/upgradeassistant:view'] = 'View Smart Upgrade Assistant';
$string['local/upgradeassistant:viewreports'] = 'View Smart Upgrade Assistant reports';
$string['local/upgradeassistant:viewsensitive'] = 'View sensitive Smart Upgrade Assistant diagnostic data';
$string['mainnavigation'] = 'Smart Upgrade Assistant main navigation';
$string['maintenanceactive'] = 'Maintenance mode active';
$string['maintenancemodeoff'] = 'Maintenance mode was disabled.';
$string['maintenancemodeon'] = 'Maintenance mode was enabled.';
$string['maintenanceverifiednote'] = 'Maintenance mode was already active when the report was generated; ' .
    'the check was completed automatically.';
$string['manualexecution'] = 'Manual execution';
$string['manualexecutiondesc'] = 'These instructions do not replace files automatically. Use them only ' .
    'after backups are complete and blockers are resolved.';
$string['manualtarget'] = 'Enter a path manually';
$string['manualtargetdesc'] = 'Use this option if the assistant did not automatically detect the target folder.';
$string['manualtargetdescnew'] = 'Enter the full path to the Moodle folder you want to use as the target.';
$string['manualtargetnew'] = 'Installation not listed? Enter a path manually';
$string['markasreviewed'] = 'Complete documented review';
$string['markaudited'] = 'Mark audited';
$string['markcomplete'] = 'Mark as complete';
$string['markverified'] = 'Mark as verified';
$string['message'] = 'Message';
$string['minimumfrom'] = 'Minimum source version';
$string['minimumphp'] = 'Minimum PHP';
$string['moodledataroot'] = 'moodledata';
$string['moodleversion'] = 'Moodle version';
$string['moreoptions'] = 'More options';
$string['needsreview'] = 'Needs review';
$string['nexttask'] = 'Recommended next task';
$string['nodifferences'] = 'No differences detected';
$string['nodifferencesdesc'] = 'No plugin folders were detected in the current installation that are ' .
    'absent in the target installation among the most common plugin types. Still, manually review custom plugins.';
$string['nohistoryreports'] = 'There are no reports in the history yet.';
$string['noinstallations'] = 'No other Moodle installations were detected in the selected directory. ' .
    'Place the new Moodle version folder next to the current installation and refresh the scan.';
$string['noreportyet'] = 'No report generated yet.';
$string['noreportyetdesc'] = 'Select a target Moodle folder and generate the report to store risk, ' .
    'findings and an auditable checklist.';
$string['notavailable'] = 'Not available';
$string['notconfigured'] = 'Not configured';
$string['notdetected'] = 'Not detected';
$string['note'] = 'Note';
$string['notnextwarning'] = 'Do not replace files with this folder yet if it is not the next recommended ' .
    'step. First prepare a Moodle {$a} folder and select it as the target.';
$string['notselected'] = 'No target selected';
$string['openreports'] = 'Open reports';
$string['overview'] = 'Overview';
$string['pasteinto'] = 'Paste into';
$string['path'] = 'Path';
$string['pdfauditintro'] = 'This section records the actions captured by the audit trail for this report.';
$string['pdfaudittrail'] = 'Audit trail';
$string['pdfchecklistintro'] = 'This checklist records preparation evidence linked to the report. ' .
    'Pending items should be completed before the production upgrade window.';
$string['pdfcomplete'] = 'Complete PDF';
$string['pdfcurrentplatform'] = 'Current platform';
$string['pdfdisclaimer'] = 'This report supports a manual Moodle upgrade process. It does not replace ' .
    'official Moodle documentation, institutional change control or a full backup/restore test.';
$string['pdfexecutivesummary'] = 'Executive summary';
$string['pdffinalnote'] = 'Report UUID: {$a}. Keep this document with the institutional change-control ' .
    'evidence for the Moodle upgrade.';
$string['pdffindingsintro'] = 'The following findings were detected at the time the pre-upgrade report ' .
    'was generated. Critical and high items should be resolved before replacing Moodle files.';
$string['pdfnextstepbackup'] = 'Confirm database, Moodle code and moodledata backups before replacing files.';
$string['pdfnextstepcritical'] = 'Resolve every critical finding and document the corrective action.';
$string['pdfnextstepnative'] = 'Run Moodle native upgrader only after the files, config.php and server requirements are ready.';
$string['pdfnextstepplugins'] = 'Review plugins and themes listed as missing, incompatible or unknown.';
$string['pdfnextsteps'] = 'Recommended next steps';
$string['pdfnextstepstaging'] = 'Repeat the process in a staging copy before touching production.';
$string['pdfredacted'] = 'PDF with sensitive data hidden';
$string['pdfreportintro'] = 'Professional pre-upgrade platform report with risk score, technical ' .
    'findings, auditable checklist and recommended actions.';
$string['pdfreporttitle'] = 'Pre-upgrade platform report';
$string['pdfseveritysummary'] = 'Severity summary';
$string['pdfsubject'] = 'Moodle pre-upgrade platform report';
$string['pdfsummarycritical'] = 'The platform has at least one critical blocker. Do not continue with ' .
    'production file replacement until the blocker is resolved and documented.';
$string['pdfsummaryhigh'] = 'The platform shows high-risk conditions. Continue only after reviewing the ' .
    'findings, validating backups and confirming compatibility.';
$string['pdfsummarylow'] = 'The platform shows a low risk level based on the stored checks. Continue ' .
    'following the auditable checklist and the official Moodle upgrade process.';
$string['pdfsummarymedium'] = 'The platform shows a medium risk level. Review the warnings before ' .
    'proceeding and keep evidence of the decisions taken.';
$string['pdftargetplatform'] = 'Target platform';
$string['pdftechnicalprofile'] = 'Technical profile';
$string['pending'] = 'Pending';
$string['pendingtasks'] = 'Pending tasks';
$string['phpversion'] = 'PHP version';
$string['plugin'] = 'Plugin';
$string['plugincompatibility'] = 'Plugin compatibility';
$string['plugincompatible'] = 'Compatible';
$string['plugincopyinstructions'] = 'Plugin paths to review or copy';
$string['plugincorechangeinreview'] = 'Standard component absent from target; verify official core change';
$string['plugindependenciesmissing'] = 'Dependencies missing from target: {$a}';
$string['plugindependenciesoutdated'] = 'Dependencies below the required version: {$a}';
$string['plugindependenciesreview'] = 'Review declared dependencies';
$string['plugindependenciesverified'] = 'Dependencies verified in the target installation';
$string['plugindifferencesdesc'] = 'The following plugin folders exist in the current installation but ' .
    'are missing in the target installation, or require additional compatibility review.';
$string['pluginmissingintarget'] = 'Missing in target';
$string['pluginname'] = 'Smart Upgrade Assistant';
$string['pluginofficiallyremoved'] = '{$a->component} was officially removed starting with Moodle {$a->version}';
$string['pluginpresentintarget'] = 'Present in target version';
$string['pluginrequiresfuturemoodle'] = 'Requires a Moodle version higher than the selected target';
$string['pluginrequiresreview'] = 'Requires review';
$string['pluginreviewnotarget'] = 'Select a target installation to compare plugins.';
$string['pluginreviewtitle'] = '5. Plugins to review before replacing files';
$string['pluginsreviewcount'] = 'Plugins to review';
$string['pluginunknowncompatibility'] = 'Unknown compatibility';
$string['preparationchecks'] = 'Preparation checks';
$string['preparationheading'] = 'Prepare a safe upgrade';
$string['preparationintro'] = 'Complete the checks, resolve blockers and review plugins before moving to manual execution.';
$string['preparationprogress'] = 'Preparation';
$string['preparationreport'] = 'Preparation report';
$string['preparationreportdesc'] = 'Create persistent evidence with the risk level, findings, plugins and completed checks.';
$string['priority'] = 'Priority';
$string['prioritycritical'] = 'Critical';
$string['priorityhigh'] = 'High';
$string['prioritynormal'] = 'Normal';
$string['privacy:metadata:local_upgradeassistant_audit'] = 'Stores audit log entries generated by report and checklist actions.';
$string['privacy:metadata:local_upgradeassistant_audit:action'] = 'The audited action.';
$string['privacy:metadata:local_upgradeassistant_audit:ip'] = 'The IP address recorded for the audited action.';
$string['privacy:metadata:local_upgradeassistant_audit:newvalue'] = 'The new value for the audited change.';
$string['privacy:metadata:local_upgradeassistant_audit:note'] = 'The note stored with the audit entry.';
$string['privacy:metadata:local_upgradeassistant_audit:oldvalue'] = 'The previous value for the audited change.';
$string['privacy:metadata:local_upgradeassistant_audit:targetid'] = 'The audited target identifier.';
$string['privacy:metadata:local_upgradeassistant_audit:targettype'] = 'The audited target type.';
$string['privacy:metadata:local_upgradeassistant_audit:timecreated'] = 'The time the audit entry was created.';
$string['privacy:metadata:local_upgradeassistant_audit:useragent'] = 'The browser user agent recorded for the audited action.';
$string['privacy:metadata:local_upgradeassistant_audit:userid'] = 'The user who performed the audited action.';
$string['privacy:metadata:local_upgradeassistant_check'] = 'Stores auditable checklist items linked to a pre-upgrade report.';
$string['privacy:metadata:local_upgradeassistant_check:completedat'] = 'The time the checklist item was completed.';
$string['privacy:metadata:local_upgradeassistant_check:completedby'] = 'The user who completed the checklist item.';
$string['privacy:metadata:local_upgradeassistant_check:note'] = 'The administrative note added to the checklist item.';
$string['privacy:metadata:local_upgradeassistant_expt'] = 'Stores PDF and HTML export history.';
$string['privacy:metadata:local_upgradeassistant_expt:contenthash'] = 'Stores a hash used to identify ' .
    'the exported report content or generated file reference.';
$string['privacy:metadata:local_upgradeassistant_expt:exporttype'] = 'The export format downloaded by the user.';
$string['privacy:metadata:local_upgradeassistant_expt:reportid'] = 'The exported report identifier.';
$string['privacy:metadata:local_upgradeassistant_expt:timecreated'] = 'The time the export was downloaded.';
$string['privacy:metadata:local_upgradeassistant_expt:userid'] = 'The user who downloaded an export.';
$string['privacy:metadata:local_upgradeassistant_item'] = 'Report findings generated by Smart Upgrade ' .
    'Assistant. They may include technical evidence and recommendations for the upgrade process.';
$string['privacy:metadata:local_upgradeassistant_item:category'] = 'Finding category.';
$string['privacy:metadata:local_upgradeassistant_item:code'] = 'Internal finding code.';
$string['privacy:metadata:local_upgradeassistant_item:description'] = 'Finding description, which may ' .
    'include technical diagnostics generated during report creation.';
$string['privacy:metadata:local_upgradeassistant_item:evidence'] = 'Technical evidence for the finding, ' .
    'including server or plugin diagnostics when available.';
$string['privacy:metadata:local_upgradeassistant_item:recommendation'] = 'Recommended action.';
$string['privacy:metadata:local_upgradeassistant_item:reportid'] = 'Associated report identifier.';
$string['privacy:metadata:local_upgradeassistant_item:severity'] = 'Finding severity.';
$string['privacy:metadata:local_upgradeassistant_item:status'] = 'Finding status.';
$string['privacy:metadata:local_upgradeassistant_item:timecreated'] = 'Time when the finding was created.';
$string['privacy:metadata:local_upgradeassistant_item:title'] = 'Finding title.';
$string['privacy:metadata:local_upgradeassistant_plug'] = 'Stores plugin inventory and compatibility linked to the report.';
$string['privacy:metadata:local_upgradeassistant_plug:compatibility'] = 'The detected compatibility status.';
$string['privacy:metadata:local_upgradeassistant_plug:component'] = 'The Moodle plugin component name.';
$string['privacy:metadata:local_upgradeassistant_plug:dependencyjson'] = 'Serialized plugin dependency data.';
$string['privacy:metadata:local_upgradeassistant_plug:evidence'] = 'Technical evidence for the compatibility status.';
$string['privacy:metadata:local_upgradeassistant_plug:pluginname'] = 'Plugin name.';
$string['privacy:metadata:local_upgradeassistant_plug:plugintype'] = 'Plugin type.';
$string['privacy:metadata:local_upgradeassistant_plug:releaseinfo'] = 'Plugin release information.';
$string['privacy:metadata:local_upgradeassistant_plug:reportid'] = 'The report linked to the plugin inventory row.';
$string['privacy:metadata:local_upgradeassistant_plug:requires'] = 'Minimum Moodle version required by the plugin.';
$string['privacy:metadata:local_upgradeassistant_plug:statuslabel'] = 'Human readable compatibility status.';
$string['privacy:metadata:local_upgradeassistant_plug:targetversion'] = 'Detected target plugin version.';
$string['privacy:metadata:local_upgradeassistant_plug:timecreated'] = 'Time when the plugin inventory row was created.';
$string['privacy:metadata:local_upgradeassistant_plug:version'] = 'Detected source plugin version.';
$string['privacy:metadata:local_upgradeassistant_rep'] = 'Stores generated pre-upgrade reports.';
$string['privacy:metadata:local_upgradeassistant_rep:currentbranch'] = 'The detected current Moodle branch.';
$string['privacy:metadata:local_upgradeassistant_rep:currentrelease'] = 'The detected current Moodle release.';
$string['privacy:metadata:local_upgradeassistant_rep:dbtype'] = 'The detected database type.';
$string['privacy:metadata:local_upgradeassistant_rep:dbversion'] = 'The detected database version.';
$string['privacy:metadata:local_upgradeassistant_rep:phpversion'] = 'The detected PHP version.';
$string['privacy:metadata:local_upgradeassistant_rep:serverprofile'] = 'The detected server profile.';
$string['privacy:metadata:local_upgradeassistant_rep:summary'] = 'JSON summary of the report, including ' .
    'lifecycle data, technical evidence and sensitive server paths when required for audit evidence.';
$string['privacy:metadata:local_upgradeassistant_rep:targetbranch'] = 'The selected target Moodle branch.';
$string['privacy:metadata:local_upgradeassistant_rep:targetpath'] = 'The selected target installation path.';
$string['privacy:metadata:local_upgradeassistant_rep:targetrelease'] = 'The selected target Moodle release.';
$string['privacy:metadata:local_upgradeassistant_rep:timecreated'] = 'The time the report was created.';
$string['privacy:metadata:local_upgradeassistant_rep:userid'] = 'The user who generated the report.';
$string['privacy:metadata:local_upgradeassistant_rules'] = 'Stores local Moodle-version rules used for validation.';
$string['privacy:metadata:local_upgradeassistant_rules:enabled'] = 'Whether the rule is enabled.';
$string['privacy:metadata:local_upgradeassistant_rules:moodlebranch'] = 'The Moodle branch used by a local validation rule.';
$string['privacy:metadata:local_upgradeassistant_rules:recommendation'] = 'Recommendation associated with the rule.';
$string['privacy:metadata:local_upgradeassistant_rules:rulekey'] = 'Rule key.';
$string['privacy:metadata:local_upgradeassistant_rules:rulesetversion'] = 'Ruleset version.';
$string['privacy:metadata:local_upgradeassistant_rules:rulesjson'] = 'Serialized rule configuration.';
$string['privacy:metadata:local_upgradeassistant_rules:ruletype'] = 'Rule type.';
$string['privacy:metadata:local_upgradeassistant_rules:severity'] = 'Rule severity.';
$string['privacy:metadata:local_upgradeassistant_rules:source'] = 'Rule source.';
$string['privacy:metadata:local_upgradeassistant_rules:targetbranch'] = 'Target Moodle branch for the rule.';
$string['privacy:metadata:preference:scanpath'] = 'Stores the last directory used to scan for Moodle installations.';
$string['privacy:metadata:preference:state'] = 'Stores the administrator wizard progress, selected ' .
    'target path, public/config paths and completed checklist items.';
$string['privacy:metadata:preference:targetinfo'] = 'Stores target Moodle version information read from ' .
    'the selected version.php file.';
$string['privacy:metadata:preference:targetpath'] = 'Stores the selected Moodle target installation path.';
$string['procontrolpanel'] = 'Control panel';
$string['profilecpanel'] = 'cPanel / WHM';
$string['profilelinux'] = 'Linux';
$string['profilevpslinux'] = 'VPS Linux';
$string['profilewindows'] = 'Windows';
$string['profilexampp'] = 'XAMPP / Windows local';
$string['proreportdesc'] = 'Generate a persistent pre-upgrade diagnosis with risk score, findings and an auditable checklist.';
$string['proreportnotarget'] = 'Select a target Moodle installation before generating the report.';
$string['proreporttitle'] = 'Preparation report';
$string['publicstructure'] = '/public structure';
$string['purgecaches'] = 'Purge caches';
$string['reccpanel1'] = 'Verify the PHP version in MultiPHP Manager before moving files.';
$string['reccpanel2'] = 'Confirm that DocumentRoot points to the correct folder, especially if Moodle uses /public.';
$string['reccpanel3'] = 'Schedule cron from cPanel Cron Jobs pointing to the correct PHP CLI.';
$string['reccpanel4'] = 'Replace folders during a maintenance window and keep the previous folder as a backup.';
$string['reclinux1'] = 'Confirm ownership and permissions for source code and moodledata before the change.';
$string['reclinux2'] = 'Validate that PHP CLI and web PHP use the same version and required extensions.';
$string['reclinux3'] = 'Run the upgrade in a staging copy before touching production.';
$string['recommendation'] = 'Recommendation';
$string['recommendedroute'] = 'Recommended conservative route';
$string['recvps1'] = 'Validate CLI backups and keep a restorable copy of database, code and moodledata.';
$string['recvps2'] = 'Check PHP-FPM/Apache/Nginx and PHP CLI before running the upgrade.';
$string['recvps3'] = 'Review moodledata permissions and source ownership after copying custom plugins.';
$string['recvps4'] = 'Use staging and a documented rollback procedure before production.';
$string['recwindows1'] = 'Verify that required PHP extensions are enabled in the correct php.ini.';
$string['recwindows2'] = 'Avoid restricted-permission paths and validate that Apache/IIS can read the new code.';
$string['recwindows3'] = 'Schedule cron with the correct php.exe and absolute paths.';
$string['recxampp1'] = 'Enable required PHP extensions in the XAMPP php.ini.';
$string['recxampp2'] = 'Restart Apache after changing PHP or extensions.';
$string['recxampp3'] = 'Use simple local paths and avoid spaces or special characters in critical folders.';
$string['recxampp4'] = 'Schedule cron in Windows with the correct php.exe only for local testing.';
$string['redacted'] = '[Redacted]';
$string['redactedexportnotice'] = 'This is a redacted export. Sensitive paths, database details and server locations were hidden.';
$string['redactedhtmlshort'] = 'Redacted HTML';
$string['redactedpdfshort'] = 'Redacted PDF';
$string['referencerules'] = 'View reference rules';
$string['refreshscan'] = 'Refresh scan';
$string['reportcategorychecklist'] = 'Checklist';
$string['reportcategorydatabase'] = 'Database';
$string['reportcategorylifecycle'] = 'Lifecycle';
$string['reportcategorypath'] = 'Upgrade path';
$string['reportcategoryplugins'] = 'Plugins';
$string['reportcategoryrequirements'] = 'Requirements';
$string['reportcategoryserver'] = 'Server';
$string['reportcategorytheme'] = 'Theme';
$string['reportfindings'] = 'Report findings';
$string['reportgenerated'] = 'Pre-upgrade report #{$a} generated.';
$string['reporthistorydesc'] = 'History of generated reports to compare diagnostics and preserve evidence.';
$string['reporthistorydescnew'] = 'Open an earlier report to compare risk levels and preserve process traceability.';
$string['reporthistorytitle'] = 'Report history';
$string['reportid'] = 'Report ID';
$string['reportspageheading'] = 'Preparation reports';
$string['reportspageintro'] = 'Review the current diagnosis, auditable checklist and previous reports ' .
    'without interrupting the assistant flow.';
$string['reportstab'] = 'Reports';
$string['reporttarget'] = 'Report target';
$string['required'] = 'Required';
$string['requiredvalue'] = 'Required value';
$string['requires'] = 'Requires';
$string['resetwizard'] = 'Reset assistant';
$string['result'] = 'Result';
$string['returntoassistant'] = 'Return to assistant';
$string['reviewexecution'] = 'Review execution';
$string['reviewnoterequired'] = 'Enter the review rationale or evidence before marking the finding as reviewed.';
$string['risklevel'] = 'Risk level';
$string['risklevelcritical'] = 'Critical risk';
$string['risklevelhigh'] = 'High risk';
$string['risklevellow'] = 'Low risk';
$string['risklevelmedium'] = 'Medium risk';
$string['risknotcalculated'] = 'Not calculated';
$string['riskscore'] = 'Risk level';
$string['route'] = 'Route';
$string['ruleoptional'] = 'Optional';
$string['rulerecommended'] = 'Recommended';
$string['rulerequired'] = 'Required';
$string['rulesbuiltinrecommendation'] = 'Local rule included with the plugin. It can be replaced by a ' .
    'compatibility API in future commercial versions.';
$string['rulesbyversiondesc'] = 'Local rules used to evaluate compatibility by Moodle version. This ' .
    'structure prepares future synchronisation from an external API.';
$string['rulesbyversiontitle'] = 'Reference rules';
$string['rulesourcebuiltin'] = 'Bundled with plugin';
$string['scan'] = 'Scan';
$string['scaninstallations'] = 'Available Moodle installations';
$string['scaninstallationsdesc'] = 'The assistant looks for folders containing a Moodle version.php ' .
    'file. For security, it only scans directories close to the current installation.';
$string['scanpathlabel'] = 'Base directory to scan';
$string['scanrefreshed'] = 'Installation scan was refreshed.';
$string['selectedtarget'] = 'Selected target installation';
$string['selectionresult'] = 'Selection result';
$string['selecttargetaction'] = 'Select target version';
$string['selecttargetfirst'] = 'First select the Moodle folder containing the version you want to upgrade to.';
$string['selectthisversion'] = 'Select';
$string['sensitivediagnosticswarning'] = 'This page is showing sensitive technical diagnostics, ' .
    'including server paths and Moodle installation locations. This view is intended only for authorised ' .
    'Moodle administrators and server operators.';
$string['sensitivehidden'] = 'Hidden. Requires sensitive diagnostics permission.';
$string['server'] = 'Server';
$string['serverprofile'] = 'Server profile';
$string['serverrecommendationsdesc'] = 'Specific recommendations based on the detected environment type.';
$string['serverrecommendationstitle'] = 'Recommendations by server type';
$string['settingspage'] = 'Smart Upgrade Assistant';
$string['severity'] = 'Severity';
$string['severitycritical'] = 'Critical';
$string['severityhigh'] = 'High';
$string['severityinfo'] = 'Info';
$string['severitylow'] = 'Low';
$string['severitymedium'] = 'Medium';
$string['sodium'] = 'Sodium';
$string['source'] = 'Source';
$string['statecleared'] = 'The assistant was fully reset. Associated reports deleted: {$a}.';
$string['status'] = 'Status';
$string['stepcompleted'] = 'Checklist step marked as completed.';
$string['stepdiagnosis'] = 'Diagnosis';
$string['stepexecution'] = 'Execution and evidence';
$string['stepfourof'] = 'Step 4 of 4';
$string['steponeof'] = 'Step 1 of 4';
$string['steppreparation'] = 'Preparation';
$string['steptarget'] = 'Target version';
$string['stepthreeof'] = 'Step 3 of 4';
$string['steptwoof'] = 'Step 2 of 4';
$string['subject'] = 'Subject';
$string['switchtoboosttheme'] = 'Switch to Boost and mark ready';
$string['synclifecycletask'] = 'Synchronise official Moodle lifecycle';
$string['targetconfigpath'] = 'Target config.php path';
$string['targetheading'] = 'Select the target version';
$string['targetintro'] = 'Choose a detected installation or enter a manual path. The assistant validates ' .
    'the route before continuing.';
$string['targetnotfound'] = 'The selected folder does not appear to be a valid Moodle installation ' .
    'because version.php was not found.';
$string['targetpathlabel'] = 'Full path of the target Moodle folder';
$string['targetpathplaceholder'] = 'Full path of the target Moodle folder';
$string['targetpublicdetectedshort'] = 'Target /public';
$string['targetpublicpath'] = 'Target public path';
$string['targetpublicstructure'] = 'Target /public structure';
$string['targetselected'] = 'Target Moodle installation selected.';
$string['targetvalidation'] = 'Selection result';
$string['targetversion'] = 'Target version';
$string['technicalcompatibility'] = 'Technical compatibility';
$string['technicaldatavisible'] = 'Technical data visible';
$string['technicaldatavisibledesc'] = 'You have permission to view sensitive server paths and diagnostics.';
$string['type'] = 'Type';
$string['usetarget'] = 'Use as target version';
$string['validateandselect'] = 'Validate and select';
$string['validationdatabase'] = 'Minimum database';
$string['validationdesc'] = 'Technical environment check against the rules for the selected target version.';
$string['validationfindingdesc'] = 'Current value: {$a->current}. Required value: {$a->required}.';
$string['validationfindingrec'] = 'Fix this requirement before proceeding with the production upgrade.';
$string['validationmaxinputvars'] = 'PHP max_input_vars';
$string['validationmoodlesource'] = 'Minimum source Moodle version';
$string['validationpartialrule'] = 'Partial target version requirements';
$string['validationpartialruledesc'] = 'The requirements shown come from the target package ' .
    'admin/environment.xml. The minimum upgrade source and any unrecognised requirements must be ' .
    'verified against the official release notes.';
$string['validationphp64bit'] = '64-bit PHP';
$string['validationphpversion'] = 'Minimum PHP version';
$string['validationruleunknown'] = 'Version rule unavailable';
$string['validationruleunknowndesc'] = 'There is no local rule for the selected target version.';
$string['validationsodium'] = 'PHP sodium extension';
$string['validationstatusfail'] = 'Failed';
$string['validationstatuspass'] = 'Passed';
$string['validationstatuswarning'] = 'Review';
$string['validationtitle'] = 'Target version compatibility';
$string['value'] = 'Value';
$string['verified'] = 'Verified';
$string['verifyboosttheme'] = 'Verify Boost theme';
$string['version'] = 'Version';
$string['viewdetails'] = 'View details';
$string['viewfullreport'] = 'View full report';
$string['viewmanualinstructions'] = 'View manual instructions';
$string['viewtargettechnicaldetails'] = 'View target technical details';
$string['viewtechnicalserverinfo'] = 'View technical server information';
$string['warningsremaining'] = '{$a} item(s) require review before running the upgrade.';
$string['wizardcontrol'] = 'Assistant control';
$string['wizardcontroldesc'] = 'Progress is stored only in your Moodle user preferences.';
$string['wizardsteps'] = 'Assistant steps';
$string['wwwroot'] = 'wwwroot';
