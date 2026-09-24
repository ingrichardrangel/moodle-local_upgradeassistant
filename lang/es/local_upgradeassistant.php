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
 * Spanish language strings for Smart Upgrade Assistant.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['accessdeniedpath'] = 'La ruta seleccionada está fuera de los directorios permitidos para el escaneo.';
$string['action'] = 'Acción';
$string['administratorcriterion'] = 'Criterio documentado por el administrador';
$string['administratorcriterionhelp'] = 'Tu decisión quedará registrada con el usuario, fecha y hora. ' .
    'Documentar un hallazgo no disminuye el riesgo detectado en este informe. Si corriges la instalación destino, ' .
    'vuelve a comprobar este informe para verificarlo. La organización debe revisar tu justificación antes de actualizar.';
$string['administratorcriterionlabel'] = 'Criterio, evidencia o decisión administrativa';
$string['administratorcriterionplaceholder'] = 'Describe la evidencia y tu decisión de continuar. ' .
    'Si corregiste el destino, usa Volver a comprobar para verificarlo.';
$string['advancedpluginmatrix'] = 'Matriz avanzada de plugins';
$string['analysecompatibility'] = 'Analizar compatibilidad';
$string['analysisdowngrade'] = 'No se puede hacer downgrade. La instalación seleccionada es menor que la versión actual de Moodle.';
$string['analysisnotnext'] = 'La versión seleccionada es mayor, pero exige como origen Moodle {$a} como ' .
    'mínimo. Actualiza primero a esa versión y vuelve a evaluar la ruta.';
$string['analysissamebranch'] = 'La instalación seleccionada tiene la misma rama que la plataforma ' .
    'actual. No representa un salto de actualización.';
$string['analysisunknownbranch'] = 'No fue posible comparar completamente las versiones porque una de ' .
    'las ramas Moodle no fue detectada.';
$string['analysisunknownpatch'] = 'No se pudo verificar el parche de Moodle instalado. Confirma que sea ' .
    'al menos Moodle {$a} antes de actualizar directamente.';
$string['analysisunverifiedroute'] = 'Se detectó la rama de destino, pero todavía no se pudo verificar ' .
    'la versión mínima de Moodle desde la que se permite actualizar.';
$string['analysisvalidnext'] = 'La instalación seleccionada es mayor que la actual y coincide con el siguiente salto recomendado.';
$string['assistanttab'] = 'Asistente';
$string['attentionchecks'] = 'Requieren atención';
$string['auditablechecklist'] = 'Checklist auditable';
$string['auditedfromwizard'] = 'Importado desde el estado actual del asistente cuando se generó el reporte.';
$string['auditnoteplaceholder'] = 'Nota opcional de auditoría o referencia de evidencia';
$string['auditstatuscompleted'] = 'Completado';
$string['auditstatuspending'] = 'Pendiente';
$string['auditstepcompleted'] = 'Paso del checklist auditable completado.';
$string['back'] = 'Volver';
$string['backupdbfilename'] = 'backup_basedatos_moodle_antes_actualizacion.sql';
$string['blockedchecks'] = 'Bloqueos técnicos';
$string['blockers'] = 'Bloqueos';
$string['blockersremaining'] = 'Debes resolver {$a} bloqueo(s) antes de continuar con seguridad.';
$string['boostthemeactive'] = 'Boost activo';
$string['boostthemenotactive'] = 'El tema activo es {$a}. Cambia el tema del sitio a Boost antes de ' .
    'marcar este paso como verificado.';
$string['boostthemeswitched'] = 'El tema del sitio fue cambiado a Boost y el paso del checklist quedó completado.';
$string['boostthemeswitchednote'] = 'El tema del sitio fue cambiado a Boost desde Smart Upgrade Assistant.';
$string['boostthemeverified'] = 'Tema Boost verificado correctamente.';
$string['boostthemeverifiednote'] = 'El tema activo fue verificado como Boost desde Smart Upgrade Assistant.';
$string['branch'] = 'Rama';
$string['cachespurged'] = 'Todas las cachés de Moodle fueron purgadas.';
$string['category'] = 'Categoría';
$string['check'] = 'Comprobación';
$string['checkbackupcode'] = 'Copia de seguridad de la carpeta actual de Moodle';
$string['checkbackupcodedesc'] = 'Crea una copia completa de la carpeta actual del código Moodle antes de reemplazar archivos.';
$string['checkbackupdata'] = 'Copia de seguridad de moodledata';
$string['checkbackupdatadesc'] = 'Crea una copia completa de la carpeta moodledata. Esta carpeta ' .
    'contiene archivos privados, entregas, cachés y datos críticos de cursos.';
$string['checkbackupdb'] = 'Copia de seguridad de la base de datos';
$string['checkbackupdbdesc'] = 'Desde cPanel phpMyAdmin o SSH, exporta una copia completa de la base de ' .
    'datos Moodle en formato SQL.';
$string['checkboosttheme'] = 'Usar temporalmente el tema Boost predeterminado';
$string['checkboostthemeauditdesc'] = 'Verifica y documenta que el sitio esté usando el tema Boost ' .
    'predeterminado antes de reemplazar el código de Moodle.';
$string['checkboostthemedesc'] = 'Tema activo actual: {$a}. Antes de reemplazar el código de Moodle, usa ' .
    'Boost como tema temporal del sitio para reducir el riesgo de incompatibilidades durante la actualización.';
$string['checkconfirmtarget'] = 'Confirmar que la nueva versión ya está instalada o descomprimida';
$string['checkconfirmtargetdesc'] = 'La carpeta Moodle destino debe existir en el servidor y contener su ' .
    'propio archivo version.php. No debe ser una carpeta vacía ni un ZIP sin descomprimir.';
$string['checklistdesc'] = 'Los botones automáticos ejecutan acciones controladas dentro de Moodle. Los ' .
    'botones Listo solo registran que ya completaste una acción externa en cPanel, File Manager, phpMyAdmin, FTP o SSH.';
$string['checklisttitle'] = 'Preparación';
$string['checkmaintenance'] = 'Activar modo mantenimiento';
$string['checkmaintenancedesc'] = 'Activa el modo mantenimiento para impedir que estudiantes y docentes ' .
    'usen la plataforma mientras preparas la actualización.';
$string['checkpurgecaches'] = 'Purgar cachés antes del proceso';
$string['checkpurgecachesdesc'] = 'Limpia las cachés de Moodle antes de iniciar el reemplazo de archivos ' .
    'para reducir errores visuales y de carga después de la actualización.';
$string['checkservercompat'] = 'Verificar compatibilidad de servidor';
$string['checkservercompatdesc'] = 'Revisa manualmente que la versión destino de Moodle sea compatible ' .
    'con tu PHP, base de datos y extensiones. Detectado actualmente: {$a}.';
$string['checkservercompatprodesc'] = 'Revisa los hallazgos automáticos del reporte de pre-upgrade y ' .
    'confirma que el servidor está listo para la versión Moodle destino.';
$string['codedir'] = 'Ruta del código';
$string['compatibility'] = 'Compatibilidad';
$string['compatibilitymatrix'] = 'Matriz de compatibilidad';
$string['compatibilitymatrixdesc'] = 'Este asistente usa una matriz conservadora para la ruta de ' .
    'actualización soportada y compara la versión PHP detectada contra los hitos destino conocidos.';
$string['compatibilitywithtarget'] = 'Compatibilidad con Moodle {$a}';
$string['compatible'] = 'Compatible';
$string['compatiblechecks'] = 'Verificaciones correctas';
$string['compatiblestatuserror'] = 'No compatible';
$string['compatiblestatusinfo'] = 'Pendiente de análisis';
$string['compatiblestatussuccess'] = 'Compatible';
$string['compatiblestatuswarning'] = 'Requiere una ruta intermedia';
$string['completed'] = 'Completado';
$string['completedby'] = 'Completado por';
$string['component'] = 'Componente';
$string['continuepreparation'] = 'Continuar preparación';
$string['copyfrom'] = 'Copiar desde';
$string['copyto'] = 'Copiar hacia';
$string['criticalzone'] = 'Ejecución manual';
$string['criticalzonedesc'] = 'Cuando renombres la carpeta actual de Moodle, el sitio puede dejar de ' .
    'responder temporalmente o enviarte al actualizador nativo. Esto es normal.';
$string['criticalzonedescstrong'] = 'Desde este punto no debes depender de botones dentro de Moodle.';
$string['current'] = 'Actual';
$string['currentinstallation'] = 'Instalación actual';
$string['currenttheme'] = 'Tema actual';
$string['currentvalue'] = 'Valor actual';
$string['currentversion'] = 'Versión actual';
$string['database'] = 'Base de datos';
$string['databaseversion'] = 'Versión BD';
$string['date'] = 'Fecha';
$string['detected'] = 'Detectada';
$string['detectedfolder'] = 'Carpeta detectada';
$string['detectedprofile'] = 'Perfil detectado';
$string['detectedstatus'] = 'Estado detectado';
$string['diagnosisheading'] = 'Diagnóstico del entorno';
$string['diagnosisintro'] = 'Comprueba la instalación actual y revisa solo los datos necesarios antes de ' .
    'elegir una versión destino.';
$string['disablemaintenance'] = 'Desactivar';
$string['done'] = 'Listo';
$string['downloadhtmlreport'] = 'Descargar informe HTML';
$string['downloadpdfreport'] = 'Descargar informe PDF';
$string['downloadredactedhtmlreport'] = 'Descargar reporte HTML redactado';
$string['downloadredactedpdfreport'] = 'Descargar reporte PDF redactado';
$string['enablelifecyclesync'] = 'Activar sincronización del ciclo de vida Moodle';
$string['enablelifecyclesync_desc'] = 'Permite la sincronización programada y manual del ciclo de vida ' .
    'de versiones Moodle desde Moodle Developer Resources. Desactívalo en entornos cerrados que no deban ' .
    'hacer solicitudes HTTP externas.';
$string['enablemaintenance'] = 'Activar modo mantenimiento';
$string['environmentdetected'] = 'Entorno detectado';
$string['eventchecklistcompleted'] = 'Elemento del checklist del asistente completado';
$string['eventlifecyclesynced'] = 'Datos del ciclo de vida Moodle sincronizados';
$string['eventmaintenancedisabled'] = 'Modo mantenimiento desactivado desde Smart Upgrade Assistant';
$string['eventmaintenanceenabled'] = 'Modo mantenimiento activado desde Smart Upgrade Assistant';
$string['eventreportcreated'] = 'Reporte del asistente de actualización creado';
$string['eventreportexported'] = 'Reporte del asistente de actualización exportado';
$string['eventtargetselected'] = 'Destino del asistente de actualización seleccionado';
$string['eventthemeswitchedtoboost'] = 'Tema del sitio cambiado a Boost desde Smart Upgrade Assistant';
$string['evidenceandtraceability'] = 'Evidencia y trazabilidad';
$string['executionblocked'] = 'La ejecución todavía presenta bloqueos. Regresa a Preparación y ' .
    'resuélvelos antes de modificar archivos.';
$string['executionheading'] = 'Ejecución y evidencia';
$string['executionintro'] = 'Revisa el estado final, genera el informe de preparación y consulta las ' .
    'instrucciones manuales únicamente cuando estés listo.';
$string['exportmenu'] = 'Exportar';
$string['exportprivacywarning'] = 'Las exportaciones completas pueden incluir diagnósticos sensibles del ' .
    'servidor. Usa exportaciones redactadas cuando compartas reportes fuera del equipo técnico.';
$string['field'] = 'Campo';
$string['finding'] = 'Hallazgo';
$string['findingboostthemecheckpending'] = 'Confirmación del tema Boost pendiente';
$string['findingboostthemecheckpendingdesc'] = 'El checklist auditable todavía no ha confirmado que el ' .
    'sitio use Boost como tema temporal durante la ventana de actualización.';
$string['findingboostthemecheckpendingrec'] = 'Verifica o cambia el tema del sitio a Boost antes de ' .
    'reemplazar el código de Moodle.';
$string['findingboostthemeok'] = 'El tema Boost está activo';
$string['findingboostthemeokdesc'] = 'El sitio está usando Boost como tema activo, lo que reduce el ' .
    'riesgo de incompatibilidades del tema durante la actualización.';
$string['findingboostthemeokrec'] = 'Mantén Boost activo hasta validar correctamente el sitio actualizado.';
$string['findingboostthemepending'] = 'El tema activo no es Boost';
$string['findingboostthemependingdesc'] = 'El tema activo actualmente es {$a}. Los temas personalizados ' .
    'o de terceros pueden fallar durante o justo después de una actualización Moodle.';
$string['findingboostthemependingrec'] = 'Cambia el tema del sitio a Boost antes de la ventana de ' .
    'actualización y reactiva el tema personalizado solo después de validar su compatibilidad.';
$string['findingchecklistpending'] = '{$a} sigue pendiente';
$string['findingchecklistpendingdesc'] = 'Esta tarea crítica de preparación no estaba marcada como ' .
    'completada cuando se generó el reporte.';
$string['findingchecklistpendingrec'] = 'Completa esta tarea antes de reemplazar archivos Moodle y ' .
    'regístrala en el checklist auditable.';
$string['findingdbcritical'] = 'La versión de base de datos está por debajo del mínimo recomendado';
$string['findingdbcriticaldesc'] = 'Se detectó {$a->type} {$a->current}, pero la rama destino requiere {$a->required} o superior.';
$string['findingdbcriticalrec'] = 'Actualiza el motor de base de datos o selecciona una versión Moodle ' .
    'destino compatible con el servidor actual.';
$string['findingdbok'] = 'La versión de base de datos superó la primera verificación de compatibilidad';
$string['findingdbokdesc'] = 'Base de datos detectada: {$a}.';
$string['findingdbokrec'] = 'Conserva esta evidencia y confirma también la página oficial de entorno Moodle antes de actualizar.';
$string['findingdbruleunknown'] = 'La regla de base de datos no pudo asociarse completamente';
$string['findingdbruleunknowndesc'] = 'Detalles de base de datos detectados: {$a}.';
$string['findingdbruleunknownrec'] = 'Verifica manualmente los requisitos de base de datos para la rama destino seleccionada.';
$string['findingdbunknown'] = 'No se pudo detectar la versión de base de datos';
$string['findingdbunknowndesc'] = 'La descripción de la base de datos fue: {$a}.';
$string['findingdbunknownrec'] = 'Confirma la versión de base de datos desde el panel del servidor, CLI ' .
    'o soporte del hosting antes de actualizar.';
$string['findinglifecyclecurrentsecurity'] = 'La rama actual está en soporte de seguridad';
$string['findinglifecyclecurrentsecuritydesc'] = '{$a->version} ya no recibe soporte general, pero ' .
    'mantiene soporte de seguridad hasta {$a->date}.';
$string['findinglifecyclecurrentsecurityrec'] = 'Mantén la rama con parches menores actualizados y ' .
    'planifica la siguiente ruta LTS cuando sea estable.';
$string['findinglifecyclecurrentunknown'] = 'No fue posible mapear la rama actual con el ciclo de vida oficial';
$string['findinglifecyclecurrentunknowndesc'] = 'La rama detectada ({$a}) no aparece en la matriz local de ciclo de vida.';
$string['findinglifecyclecurrentunsupported'] = 'La rama actual está fuera de soporte oficial';
$string['findinglifecyclecurrentunsupporteddesc'] = '{$a} no recibe soporte general ni parches oficiales ' .
    'de seguridad según la matriz de ciclo de vida disponible.';
$string['findinglifecyclecurrentunsupportedmitigated'] = 'La actualización seleccionada corrige la falta ' .
    'de soporte de la rama actual';
$string['findinglifecyclecurrentunsupportedmitigateddesc'] = '{$a->current} está fuera de soporte ' .
    'oficial, pero el destino seleccionado ({$a->target}) es una rama posterior y soportada. Esta ' .
    'condición queda mitigada por el propio plan de actualización y no aumenta el nivel de riesgo.';
$string['findinglifecyclecurrentunsupportedmitigatedrec'] = 'Continúa con las verificaciones técnicas y ' .
    'los respaldos. No se requiere una tarea adicional para resolver el estado de la rama de origen.';
$string['findinglifecyclecurrentunsupportedrec'] = 'Planifica una actualización hacia una rama soportada,' .
    ' preferiblemente LTS, antes de intervenir producción.';
$string['findinglifecycletargetfuture'] = 'La versión destino todavía es futura';
$string['findinglifecycletargetfuturedesc'] = '{$a->version} tiene fecha de lanzamiento prevista para ' .
    '{$a->date}; no debe usarse como destino productivo antes de su publicación estable.';
$string['findinglifecycletargetfuturerec'] = 'Usa una rama estable soportada o espera la publicación ' .
    'oficial antes de planificar producción.';
$string['findinglifecycletargetlts'] = 'La versión destino pertenece a una rama LTS';
$string['findinglifecycletargetltsdesc'] = '{$a->version} es una rama LTS con soporte de seguridad hasta {$a->date}.';
$string['findinglifecycletargetltsrec'] = 'Para entornos institucionales, priorizar una ruta LTS reduce ' .
    'cambios mayores frecuentes.';
$string['findinglifecycletargetnonlts'] = 'La versión destino no es LTS';
$string['findinglifecycletargetnonltsdesc'] = '{$a} es una rama estable no LTS. Puede ser válida, pero ' .
    'no es la opción preferente para instituciones que priorizan soporte extendido.';
$string['findinglifecycletargetnonltsrec'] = 'Evalúa si conviene permanecer en una LTS soportada o ' .
    'planificar la próxima LTS estable.';
$string['findinglifecycletargetunknown'] = 'No fue posible mapear la rama destino con el ciclo de vida oficial';
$string['findinglifecycletargetunknowndesc'] = 'La rama destino ({$a}) no aparece en la matriz local de ciclo de vida.';
$string['findinglifecycletargetunsupported'] = 'La versión destino seleccionada también está fuera de soporte';
$string['findinglifecycletargetunsupporteddesc'] = '{$a} no recibe soporte general ni parches oficiales ' .
    'de seguridad. Actualizar hacia esa rama no corrige la exposición de ciclo de vida.';
$string['findinglifecycletargetunsupportedrec'] = 'Selecciona una rama posterior que mantenga soporte general o de seguridad.';
$string['findinglifecycleunknownrec'] = 'Actualiza la matriz de ciclo de vida desde Moodle HQ o valida ' .
    'manualmente la versión en la documentación oficial.';
$string['findingmaintenancepending'] = 'El modo mantenimiento no estaba marcado como completado';
$string['findingmaintenancependingdesc'] = 'El reporte se generó sin que el paso de mantenimiento ' .
    'estuviera marcado como completado.';
$string['findingmaintenancependingrec'] = 'Activa el modo mantenimiento antes de la ventana de reemplazo de archivos.';
$string['findingmaxinputvars'] = 'PHP max_input_vars puede estar demasiado bajo';
$string['findingmaxinputvarsdesc'] = 'Valor max_input_vars detectado: {$a}.';
$string['findingmaxinputvarsrec'] = 'Aumenta max_input_vars al menos a 5000 para ramas Moodle modernas.';
$string['findingnotreviewable'] = 'Este hallazgo no admite una resolución manual o ya fue revisado.';
$string['findingpathblocked'] = 'La ruta de actualización está bloqueada';
$string['findingpathblockedrec'] = 'Selecciona una rama Moodle superior válida y evita downgrades.';
$string['findingpathok'] = 'La ruta de actualización coincide con el siguiente salto recomendado';
$string['findingpathokrec'] = 'Continúa con el checklist de preparación y conserva este reporte como evidencia.';
$string['findingpathreview'] = 'La ruta de actualización requiere revisión';
$string['findingpathreviewrec'] = 'Usa primero la rama intermedia recomendada antes de saltar a la versión seleccionada.';
$string['findingpathunknown'] = 'No se pudo analizar completamente la ruta de actualización';
$string['findingpathunknowndesc'] = 'No se pudo detectar con suficiente confianza la rama Moodle actual o destino.';
$string['findingpathunknownrec'] = 'Confirma ambos archivos version.php y selecciona una instalación destino clara.';
$string['findingpathunverifiedrec'] = 'Consulta los requisitos oficiales de actualización de la versión ' .
    'de destino y documenta la ruta seleccionada.';
$string['findingphpbits'] = 'PHP no se está ejecutando en modo 64-bit';
$string['findingphpbitsdesc'] = 'Las ramas Moodle modernas requieren una ejecución PHP de 64 bits.';
$string['findingphpbitsrec'] = 'Mueve el sitio a una ejecución PHP de 64 bits antes de actualizar.';
$string['findingphpcritical'] = 'La versión PHP está por debajo del mínimo requerido';
$string['findingphpcriticaldesc'] = 'PHP detectado {$a->current}; se requiere PHP {$a->required} o superior.';
$string['findingphpcriticalrec'] = 'Cambia la versión PHP antes de intentar la actualización.';
$string['findingphpok'] = 'La versión PHP superó la primera verificación de compatibilidad';
$string['findingphpokdesc'] = 'Versión PHP detectada: {$a}.';
$string['findingphpokrec'] = 'Conserva esta evidencia y verifica también las extensiones desde la página de entorno Moodle.';
$string['findingphpruleunknown'] = 'No hay regla PHP local para esta rama destino';
$string['findingphpruleunknowndesc'] = 'El asistente no tiene una regla local de PHP mínimo para la rama destino seleccionada.';
$string['findingphpruleunknownrec'] = 'Actualiza el conjunto de reglas o verifica manualmente los requisitos de la rama destino.';
$string['findingplugincorechangereview'] = 'Verificar cambio de núcleo para {$a}';
$string['findingplugincorechangereviewdesc'] = 'Este componente pertenece a la distribución estándar de ' .
    'la versión de origen, pero no está presente en el código destino. Puede tratarse de una eliminación ' .
    'o migración oficial y, por ello, no suma riesgo automáticamente.';
$string['findingplugincorechangereviewrec'] = 'Consulta las notas oficiales de actualización y registra ' .
    'si el componente fue retirado, sustituido o requiere una acción adicional.';
$string['findingplugindependencymissing'] = 'Faltan dependencias para {$a}';
$string['findingplugindependencymissingdesc'] = '{$a}';
$string['findingplugindependencymissingrec'] = 'Incorpora las dependencias ausentes en el código destino,' .
    ' vuelve a analizar y documenta la comprobación realizada.';
$string['findingplugindependencyoutdated'] = 'Dependencias insuficientes para {$a}';
$string['findingplugindependencyoutdateddesc'] = '{$a}';
$string['findingplugindependencyoutdatedrec'] = 'Actualiza las dependencias señaladas hasta la versión ' .
    'mínima requerida y vuelve a ejecutar el análisis.';
$string['findingplugindependencyreview'] = 'Verificar dependencias declaradas de {$a}';
$string['findingplugindependencyreviewdesc'] = 'El plugin declara dependencias, pero alguna versión no ' .
    'pudo confirmarse de forma concluyente en el código destino. Esta revisión administrativa no suma riesgo por sí sola.';
$string['findingplugindependencyreviewrec'] = 'Comprueba las dependencias en la instalación destino y ' .
    'registra por escrito el criterio o la evidencia utilizada.';
$string['findingpluginofficialremoval'] = '{$a} fue retirado oficialmente del núcleo de Moodle';
$string['findingpluginofficialremovaldesc'] = '{$a->component} forma parte del núcleo de la versión de ' .
    'origen y fue retirado oficialmente desde Moodle {$a->target} ({$a->issue}). Su ausencia en el ' .
    'código destino es esperada y no representa un plugin personalizado perdido.';
$string['findingpluginofficialremovalrec'] = 'No copies ni intentes desinstalar manualmente este ' .
    'componente antes de actualizar. Conserva el hallazgo como evidencia del cambio oficial de núcleo.';
$string['findingpluginreview'] = 'Revisar plugin {$a}';
$string['findingpluginreviewdesc'] = 'Estado detectado: {$a}.';
$string['findingpluginreviewrec'] = 'Confirma la compatibilidad del plugin, actualízalo o elimínalo antes de actualizar.';
$string['findingpluginsok'] = 'No se detectaron diferencias de carpetas de plugins personalizados';
$string['findingpluginsokdesc'] = 'La comparación básica no encontró carpetas de plugins ausentes en la instalación destino.';
$string['findingpluginsokrec'] = 'Aun así, confirma manualmente la compatibilidad de plugins antes del reemplazo final.';
$string['findingpublicstructure'] = 'El sitio actual usa una estructura /public';
$string['findingpublicstructuredesc'] = 'El asistente detectó un directorio public dentro del árbol de código Moodle actual.';
$string['findingpublicstructurerec'] = 'Confirma que el DocumentRoot del servidor web apunte al ' .
    'directorio public correcto después de la actualización.';
$string['findingreviewed'] = 'Se documentó la decisión administrativa. El riesgo detectado permanece en este informe.';
$string['findingreviewerror'] = 'No se pudo confirmar la revisión. Comprueba la conexión y vuelve a intentarlo.';
$string['findingreviewsavedrefresh'] = 'La revisión se guardó, pero no se pudo actualizar esta tarjeta. ' .
    'Recarga la página para verla; no es necesario volver a enviarla.';
$string['findingsodiumcritical'] = 'Falta la extensión PHP sodium';
$string['findingsodiumcriticaldesc'] = 'La rama Moodle destino requiere la extensión sodium, pero no está cargada en PHP.';
$string['findingsodiumcriticalrec'] = 'Activa la extensión sodium antes de actualizar.';
$string['findingstatusaccepted'] = 'Riesgo aceptado por el administrador';
$string['findingstatusclosed'] = 'Resuelto';
$string['findingstatusmitigated'] = 'Mitigado por la actualización';
$string['findingstatusofficialremoval'] = 'Gestionado por la actualización';
$string['findingstatusopen'] = 'Abierto';
$string['findingstatusreviewed'] = 'Revisado por el administrador';
$string['findingtargetprerelease'] = 'La versión de destino está en desarrollo';
$string['findingtargetprereleasedesc'] = 'La instalación de destino es una versión preliminar de Moodle. ' .
    'Sus requisitos y compatibilidad pueden cambiar antes de la versión estable.';
$string['findingtargetprereleaserec'] = 'Comprueba los requisitos vigentes de la versión preliminar, ' .
    'ensaya la actualización en un entorno de pruebas y documenta la decisión antes de continuar.';
$string['findingtargetpublicstructure'] = 'La plataforma destino usa estructura /public';
$string['findingtargetpublicstructuredesc'] = 'El asistente detectó el directorio public en la ' .
    'plataforma destino: {$a}. Este hallazgo no aumenta el riesgo; cambia el procedimiento de reemplazo.';
$string['findingtargetpublicstructurerec'] = 'Renombra la raíz antigua como respaldo, coloca la raíz ' .
    'preparada con el nombre de producción y configura el Document Root del dominio para servir el ' .
    'directorio public. Conserva config.php en la raíz superior.';
$string['generatedby'] = 'Generado por';
$string['generatedon'] = 'Generado el';
$string['generatepreparationreport'] = 'Generar informe';
$string['generateproreport'] = 'Generar reporte';
$string['headerhelp'] = 'Prepara la actualización paso a paso, resuelve los bloqueos y conserva ' .
    'evidencia antes de modificar los archivos del servidor.';
$string['herodescription'] = 'Prepara una actualización de Moodle paso a paso, con validaciones, evidencia y trazabilidad.';
$string['htmlcomplete'] = 'HTML completo';
$string['htmlredacted'] = 'HTML con datos sensibles ocultos';
$string['importantsummary'] = 'Resumen importante';
$string['importantsummarydesc'] = 'Este plugin está diseñado para guiar a administradores novatos con ' .
    'rutas reales, nombres exactos de carpetas y pasos manuales seguros. No reemplaza archivos ' .
    'automáticamente porque hacerlo desde la plataforma activa puede romper Moodle durante la ejecución.';
$string['indicator'] = 'Indicador';
$string['installationscaption'] = 'Instalaciones Moodle detectadas';
$string['instructionclassicmodeactive'] = 'Modo clásico detectado: la plataforma destino no usa /public. ' .
    'En este caso sí corresponde el reemplazo por renombrado de carpetas.';
$string['instructionclassictargetdetected'] = 'La plataforma destino no tiene estructura /public. Se ' .
    'usará el procedimiento clásico de renombrar la carpeta actual y colocar la carpeta destino con el nombre de producción.';
$string['instructioncopyconfig'] = 'Copia config.php desde la carpeta antigua hacia la nueva carpeta. Si ' .
    'el sistema pregunta si deseas reemplazar el archivo, selecciona Sí / Replace.';
$string['instructionfinish'] = 'Cuando finalice la actualización, purga cachés nuevamente y desactiva el modo mantenimiento.';
$string['instructionlocatecurrent'] = 'Ubica la carpeta actual de Moodle.';
$string['instructionlocatetarget'] = 'Ubica la carpeta de la versión Moodle destino seleccionada en este asistente.';
$string['instructionnewoldname'] = 'Renómbrala con este nombre exacto de respaldo. No la elimines.';
$string['instructionnewtargetpath'] = 'Después del renombrado, la nueva ruta de Moodle debería quedar así.';
$string['instructionopenfilemanager'] = 'Abre cPanel File Manager o ingresa por FTP/SSH.';
$string['instructionpublicconfigcheck'] = 'Revisa el config.php copiado: debe conservar la URL, las ' .
    'credenciales de base de datos, el prefijo y moodledata de producción. No agregues $CFG->dirroot ni ' .
    'cambies la línea estándar de carga.';
$string['instructionpubliccopyconfig'] = 'Copia el config.php de producción desde la carpeta antigua ' .
    'hacia la raíz de la nueva instalación. Debe quedar fuera del directorio public.';
$string['instructionpublicdevnote'] = 'La carpeta preparada {$a->targetfolder} se convierte en el código ' .
    'de producción bajo el nombre {$a->currentfolder}. El dominio de desarrollo deberá configurarse ' .
    'después con otra instalación independiente si deseas conservarlo.';
$string['instructionpublicdocumentroot'] = 'En cPanel o en la configuración del servidor, cambia el ' .
    'Document Root del dominio de producción para que apunte exactamente al directorio public final.';
$string['instructionpubliceditroots'] = 'Verifica que config.php conserve la URL, la base de datos y ' .
    'moodledata de producción. No establezcas $CFG->dirroot manualmente.';
$string['instructionpublicfinalroot'] = 'Comprueba la estructura final. La primera ruta es la raíz ' .
    'privada de Moodle y la segunda es el único directorio que debe publicar el servidor web.';
$string['instructionpublicfinish'] = 'Cuando termine la actualización, purga las cachés, actualiza la ' .
    'tarea cron para usar la nueva ruta dentro de public, verifica el sitio y solo entonces reactiva ' .
    'cron y desactiva el modo mantenimiento.';
$string['instructionpublicmodeactive'] = 'Modo /public detectado: la raíz preparada puede sustituir por ' .
    'nombre a la raíz de producción, pero el dominio debe servir exclusivamente su directorio public.';
$string['instructionpublicnorenames'] = 'Renombra la carpeta antigua como respaldo y coloca la raíz ' .
    'preparada con el nombre de la carpeta de producción. El directorio public no se renombra por separado.';
$string['instructionpublicpreflight'] = 'Antes del cambio, confirma que el modo mantenimiento está ' .
    'activo, el cron está detenido, no hay tareas en ejecución y los respaldos de código, base de datos ' .
    'y moodledata están disponibles.';
$string['instructionpublicrenamecurrent'] = 'Renombra la raíz actual de producción con el nombre de ' .
    'respaldo indicado. No la elimines: contiene el código anterior y el config.php que se copiará.';
$string['instructionpublicrenametarget'] = 'Mueve o renombra la raíz preparada de Moodle destino para ' .
    'que adopte exactamente el nombre de la carpeta de producción.';
$string['instructionpublicrouting'] = 'Si el servidor usa Apache u OpenLiteSpeed, confirma que el ' .
    'archivo .htaccess y las reglas de enrutamiento requeridas por Moodle 5.2 estén presentes dentro de public.';
$string['instructionpublicsetupinclude'] = 'Conserva la línea estándar require_once(__DIR__ . ' .
    '\'/lib/setup.php\'). Moodle carga internamente la librería situada en public.';
$string['instructionrenamecurrent'] = 'Haz clic derecho sobre la carpeta actual con este nombre exacto.';
$string['instructionrenametarget'] = 'Renombra la carpeta destino usando el nombre actual de la carpeta de producción.';
$string['instructionrunupgrade'] = 'Abre el actualizador nativo de Moodle en el navegador o ejecuta el comando CLI por SSH.';
$string['instructionsblocked'] = 'No se generan instrucciones de reemplazo porque la versión ' .
    'seleccionada representa un downgrade o una ruta de actualización inválida.';
$string['instructionsnotarget'] = 'Selecciona primero una instalación destino para que el asistente ' .
    'genere instrucciones usando los nombres reales de las carpetas.';
$string['instructionsnotdirect'] = 'La carpeta seleccionada es mayor que la versión actual, pero el ' .
    'asistente recomienda no usarla todavía como salto directo.';
$string['instructiontargetpublicdetected'] = 'La plataforma destino usa estructura /public. Confirma la ' .
    'raíz de aplicación y el directorio public detectado.';
$string['invalidaction'] = 'Acción inválida del asistente.';
$string['invalidexporttype'] = 'Tipo de exportación no válido.';
$string['invalidfinding'] = 'El hallazgo indicado no es válido.';
$string['invalidreportid'] = 'ID de reporte inválido.';
$string['invalidstep'] = 'Paso de checklist inválido.';
$string['latestreport'] = 'Último informe';
$string['lifecycleandtimeline'] = 'Ciclo de vida y cronología de soporte';
$string['lifecyclecurrentstatus'] = 'Ciclo de vida';
$string['lifecycledesc'] = 'Consulta y cachea la matriz oficial de soporte Moodle para mostrar inicio de ' .
    'ciclo, fin de soporte general, fin de soporte de seguridad y recomendación LTS.';
$string['lifecyclefallbackmode'] = 'Usando respaldo local';
$string['lifecyclefuturelts'] = 'próxima LTS';
$string['lifecyclegeneralsupport'] = 'Soporte general';
$string['lifecyclegeneraluntil'] = 'Soporte general hasta:';
$string['lifecyclelastsync'] = 'Última sincronización';
$string['lifecyclelts'] = 'LTS';
$string['lifecycleltspriority'] = 'Prioridad LTS';
$string['lifecyclepdfsectiondesc'] = 'Esta sección documenta el estado de soporte oficial conocido al ' .
    'momento de generar el reporte. Sirve como evidencia para justificar la estrategia de actualización.';
$string['lifecyclepdfsectiontitle'] = 'Ciclo de vida de la versión Moodle';
$string['lifecyclerecommendfuturetarget'] = '{$a->target} está planificada para {$a->date}, pero todavía ' .
    'no debe recomendarse como destino productivo hasta su liberación estable.';
$string['lifecyclerecommendltsok'] = '{$a} es una rama LTS soportada. Para producción institucional, ' .
    'esta rama es una opción preferente cuando cumple los requisitos del sitio.';
$string['lifecyclerecommendltssecurity'] = '{$a->current} es una rama LTS con soporte de seguridad hasta ' .
    '{$a->date}. Se recomienda mantener parches menores y planificar {$a->future} cuando sea estable.';
$string['lifecyclerecommendnonlts'] = '{$a->target} es una rama estable no LTS. Para entornos ' .
    'institucionales, compara esta ruta contra {$a->lts} antes de decidir.';
$string['lifecyclerecommendstable'] = 'La rama actual está soportada, pero para entornos institucionales ' .
    'se recomienda priorizar {$a} o la próxima LTS estable.';
$string['lifecyclerecommendunknown'] = 'No fue posible generar una recomendación automática de ciclo de ' .
    'vida. Verifica la matriz oficial de Moodle HQ.';
$string['lifecyclerecommendunsupported'] = 'La rama actual está fuera de soporte. Se recomienda ' .
    'planificar una actualización hacia {$a} o hacia una rama estable soportada.';
$string['lifecyclerefresh'] = 'Sincronizar ciclo de vida';
$string['lifecyclerefreshed'] = 'La matriz de ciclo de vida Moodle fue sincronizada o actualizada desde ' .
    'el respaldo local disponible.';
$string['lifecyclerelease'] = 'Lanzamiento:';
$string['lifecyclesecuritysupport'] = 'Soporte de seguridad';
$string['lifecyclesecurityuntil'] = 'Seguridad hasta:';
$string['lifecyclesource'] = 'Fuente';
$string['lifecyclestatusfuture'] = 'Lanzamiento futuro';
$string['lifecyclestatusgeneral'] = 'Soporte general';
$string['lifecyclestatussecurity'] = 'Soporte de seguridad';
$string['lifecyclestatusunsupported'] = 'Fuera de soporte';
$string['lifecyclesyncdisabled'] = 'La sincronización del ciclo de vida Moodle está desactivada en la configuración del plugin.';
$string['lifecycletimeline'] = 'Línea de tiempo de soporte';
$string['lifecycletitle'] = 'Ciclo de vida Moodle';
$string['local/upgradeassistant:configure'] = 'Configurar Smart Upgrade Assistant';
$string['local/upgradeassistant:export'] = 'Exportar reportes de Smart Upgrade Assistant';
$string['local/upgradeassistant:generatereport'] = 'Generar reportes de Smart Upgrade Assistant';
$string['local/upgradeassistant:manage'] = 'Gestionar Smart Upgrade Assistant';
$string['local/upgradeassistant:view'] = 'Ver Smart Upgrade Assistant';
$string['local/upgradeassistant:viewreports'] = 'Ver reportes de Smart Upgrade Assistant';
$string['local/upgradeassistant:viewsensitive'] = 'Ver datos sensibles de diagnósticos de Smart Upgrade Assistant';
$string['mainnavigation'] = 'Navegación principal de Smart Upgrade Assistant';
$string['maintenanceactive'] = 'Modo mantenimiento activo';
$string['maintenancemodeoff'] = 'El modo mantenimiento fue desactivado.';
$string['maintenancemodeon'] = 'El modo mantenimiento fue activado.';
$string['maintenanceverifiednote'] = 'El modo mantenimiento ya estaba activo al generar el informe; la ' .
    'verificación se completó automáticamente.';
$string['manualexecution'] = 'Ejecución manual';
$string['manualexecutiondesc'] = 'Estas instrucciones no reemplazan archivos automáticamente. Úsalas ' .
    'después de completar los respaldos y resolver los bloqueos.';
$string['manualtarget'] = 'Introducir una ruta manualmente';
$string['manualtargetdesc'] = 'Usa esta opción si el asistente no detectó automáticamente la carpeta destino.';
$string['manualtargetdescnew'] = 'Escribe la ruta completa de la carpeta de Moodle que deseas usar como destino.';
$string['manualtargetnew'] = '¿No aparece la instalación? Introducir una ruta manualmente';
$string['markasreviewed'] = 'Completar revisión documentada';
$string['markaudited'] = 'Marcar auditado';
$string['markcomplete'] = 'Marcar como completado';
$string['markverified'] = 'Marcar como verificado';
$string['message'] = 'Mensaje';
$string['minimumfrom'] = 'Versión mínima origen';
$string['minimumphp'] = 'PHP mínimo';
$string['moodledataroot'] = 'moodledata';
$string['moodleversion'] = 'Versión Moodle';
$string['moreoptions'] = 'Más opciones';
$string['needsreview'] = 'Requiere revisión';
$string['nexttask'] = 'Siguiente tarea recomendada';
$string['nodifferences'] = 'Sin diferencias detectadas';
$string['nodifferencesdesc'] = 'No se detectaron carpetas de plugins presentes en la instalación actual ' .
    'y ausentes en la instalación destino dentro de los tipos más comunes. Aun así, revisa manualmente plugins personalizados.';
$string['nohistoryreports'] = 'Todavía no hay informes en el historial.';
$string['noinstallations'] = 'No se detectaron otras instalaciones Moodle en el directorio seleccionado. ' .
    'Coloca la carpeta de la nueva versión como hermana de la instalación actual y actualiza el escaneo.';
$string['noreportyet'] = 'Aún no hay reporte generado.';
$string['noreportyetdesc'] = 'Selecciona una carpeta Moodle destino y genera el reporte para guardar ' .
    'riesgo, hallazgos y checklist auditable.';
$string['notavailable'] = 'No disponible';
$string['notconfigured'] = 'No configurado';
$string['notdetected'] = 'No detectada';
$string['note'] = 'Nota';
$string['notnextwarning'] = 'No reemplaces archivos con esta carpeta todavía si no corresponde al ' .
    'siguiente salto recomendado. Primero prepara una carpeta Moodle {$a} y selecciónala como destino.';
$string['notselected'] = 'Destino no seleccionado';
$string['openreports'] = 'Abrir informes';
$string['overview'] = 'Resumen';
$string['pasteinto'] = 'Pegar en';
$string['path'] = 'Ruta';
$string['pdfauditintro'] = 'Esta sección registra las acciones capturadas por la bitácora auditable de este reporte.';
$string['pdfaudittrail'] = 'Bitácora de auditoría';
$string['pdfchecklistintro'] = 'Este checklist registra evidencia de preparación vinculada al reporte. ' .
    'Los elementos pendientes deben completarse antes de la ventana de actualización en producción.';
$string['pdfcomplete'] = 'PDF completo';
$string['pdfcurrentplatform'] = 'Plataforma actual';
$string['pdfdisclaimer'] = 'Este informe apoya un proceso manual de actualización Moodle. No reemplaza ' .
    'la documentación oficial de Moodle, el control de cambios institucional ni una prueba completa de respaldo/restauración.';
$string['pdfexecutivesummary'] = 'Resumen ejecutivo';
$string['pdffinalnote'] = 'UUID del reporte: {$a}. Conserva este documento como evidencia del control de ' .
    'cambios institucional de la actualización Moodle.';
$string['pdffindingsintro'] = 'Los siguientes hallazgos fueron detectados en el momento en que se generó ' .
    'el reporte de pre-actualización. Los elementos críticos y altos deben resolverse antes de reemplazar archivos de Moodle.';
$string['pdfnextstepbackup'] = 'Confirmar los respaldos de base de datos, código Moodle y moodledata antes de reemplazar archivos.';
$string['pdfnextstepcritical'] = 'Resolver cada hallazgo crítico y documentar la acción correctiva.';
$string['pdfnextstepnative'] = 'Ejecutar el actualizador nativo de Moodle solo cuando los archivos, ' .
    'config.php y requisitos del servidor estén listos.';
$string['pdfnextstepplugins'] = 'Revisar plugins y temas marcados como ausentes, incompatibles o desconocidos.';
$string['pdfnextsteps'] = 'Próximos pasos recomendados';
$string['pdfnextstepstaging'] = 'Repetir el proceso en una copia de pruebas antes de tocar producción.';
$string['pdfredacted'] = 'PDF con datos sensibles ocultos';
$string['pdfreportintro'] = 'Informe profesional de pre-actualización de plataforma con score de riesgo, ' .
    'hallazgos técnicos, checklist auditable y acciones recomendadas.';
$string['pdfreporttitle'] = 'Informe de pre-actualización de plataforma';
$string['pdfseveritysummary'] = 'Resumen de severidad';
$string['pdfsubject'] = 'Informe de pre-actualización de plataforma Moodle';
$string['pdfsummarycritical'] = 'La plataforma tiene al menos un bloqueo crítico. No continúes con el ' .
    'reemplazo de archivos en producción hasta resolver y documentar el bloqueo.';
$string['pdfsummaryhigh'] = 'La plataforma muestra condiciones de alto riesgo. Continúa solo después de ' .
    'revisar los hallazgos, validar respaldos y confirmar compatibilidad.';
$string['pdfsummarylow'] = 'La plataforma muestra un nivel de riesgo bajo según las verificaciones ' .
    'guardadas. Continúa siguiendo el checklist auditable y el proceso oficial de actualización Moodle.';
$string['pdfsummarymedium'] = 'La plataforma muestra un nivel de riesgo medio. Revisa las advertencias ' .
    'antes de proceder y conserva evidencia de las decisiones tomadas.';
$string['pdftargetplatform'] = 'Plataforma destino';
$string['pdftechnicalprofile'] = 'Perfil técnico';
$string['pending'] = 'Pendiente';
$string['pendingtasks'] = 'Tareas pendientes';
$string['phpversion'] = 'Versión PHP';
$string['plugin'] = 'Plugin';
$string['plugincompatibility'] = 'Compatibilidad de plugins';
$string['plugincompatible'] = 'Compatible';
$string['plugincopyinstructions'] = 'Rutas de plugins que debes revisar o copiar';
$string['plugincorechangeinreview'] = 'Componente estándar ausente en destino; verificar cambio oficial';
$string['plugindependenciesmissing'] = 'Dependencias ausentes en destino: {$a}';
$string['plugindependenciesoutdated'] = 'Dependencias con versión insuficiente: {$a}';
$string['plugindependenciesreview'] = 'Revisar dependencias declaradas';
$string['plugindependenciesverified'] = 'Dependencias verificadas en la instalación destino';
$string['plugindifferencesdesc'] = 'Las siguientes carpetas de plugins existen en la instalación actual ' .
    'pero no existen en la instalación destino, o requieren revisión adicional de compatibilidad.';
$string['pluginmissingintarget'] = 'Ausente en destino';
$string['pluginname'] = 'Smart Upgrade Assistant';
$string['pluginofficiallyremoved'] = '{$a->component} fue retirado oficialmente desde Moodle {$a->version}';
$string['pluginpresentintarget'] = 'Presente en la versión destino';
$string['pluginrequiresfuturemoodle'] = 'Requiere una versión Moodle superior a la seleccionada';
$string['pluginrequiresreview'] = 'Requiere revisión';
$string['pluginreviewnotarget'] = 'Selecciona una instalación destino para comparar plugins.';
$string['pluginreviewtitle'] = 'Compatibilidad de plugins';
$string['pluginsreviewcount'] = 'Plugins por revisar';
$string['pluginunknowncompatibility'] = 'Compatibilidad desconocida';
$string['preparationchecks'] = 'Verificaciones de preparación';
$string['preparationheading'] = 'Prepara una actualización segura';
$string['preparationintro'] = 'Completa las verificaciones, resuelve los bloqueos y revisa los plugins ' .
    'antes de pasar a la ejecución manual.';
$string['preparationprogress'] = 'Preparación';
$string['preparationreport'] = 'Informe de preparación';
$string['preparationreportdesc'] = 'Genera una evidencia persistente con el nivel de riesgo, hallazgos, ' .
    'plugins y verificaciones completadas.';
$string['priority'] = 'Prioridad';
$string['prioritycritical'] = 'Crítica';
$string['priorityhigh'] = 'Alta';
$string['prioritynormal'] = 'Normal';
$string['privacy:metadata:local_upgradeassistant_audit'] = 'Almacena entradas de auditoría generadas por ' .
    'acciones de reportes y checklist.';
$string['privacy:metadata:local_upgradeassistant_audit:action'] = 'La acción auditada.';
$string['privacy:metadata:local_upgradeassistant_audit:ip'] = 'La dirección IP registrada para la acción auditada.';
$string['privacy:metadata:local_upgradeassistant_audit:newvalue'] = 'El nuevo valor del cambio auditado.';
$string['privacy:metadata:local_upgradeassistant_audit:note'] = 'La nota almacenada con la entrada de auditoría.';
$string['privacy:metadata:local_upgradeassistant_audit:oldvalue'] = 'El valor anterior del cambio auditado.';
$string['privacy:metadata:local_upgradeassistant_audit:targetid'] = 'El identificador del objetivo auditado.';
$string['privacy:metadata:local_upgradeassistant_audit:targettype'] = 'El tipo de objetivo auditado.';
$string['privacy:metadata:local_upgradeassistant_audit:timecreated'] = 'La fecha de creación de la entrada de auditoría.';
$string['privacy:metadata:local_upgradeassistant_audit:useragent'] = 'El agente de usuario del navegador ' .
    'registrado para la acción auditada.';
$string['privacy:metadata:local_upgradeassistant_audit:userid'] = 'El usuario que ejecutó la acción auditada.';
$string['privacy:metadata:local_upgradeassistant_check'] = 'Almacena elementos de checklist auditable ' .
    'vinculados a un reporte de pre-upgrade.';
$string['privacy:metadata:local_upgradeassistant_check:completedat'] = 'La fecha en que se completó el elemento del checklist.';
$string['privacy:metadata:local_upgradeassistant_check:completedby'] = 'El usuario que completó el elemento del checklist.';
$string['privacy:metadata:local_upgradeassistant_check:note'] = 'La nota administrativa agregada al elemento del checklist.';
$string['privacy:metadata:local_upgradeassistant_expt'] = 'Almacena historial de exportaciones PDF y HTML.';
$string['privacy:metadata:local_upgradeassistant_expt:contenthash'] = 'Almacena un hash utilizado para ' .
    'identificar el contenido exportado o la referencia del archivo generado.';
$string['privacy:metadata:local_upgradeassistant_expt:exporttype'] = 'El formato de exportación descargado por el usuario.';
$string['privacy:metadata:local_upgradeassistant_expt:reportid'] = 'El identificador del reporte exportado.';
$string['privacy:metadata:local_upgradeassistant_expt:timecreated'] = 'La fecha en que se descargó la exportación.';
$string['privacy:metadata:local_upgradeassistant_expt:userid'] = 'Usuario que descargó una exportación.';
$string['privacy:metadata:local_upgradeassistant_item'] = 'Hallazgos del reporte generados por Smart ' .
    'Upgrade Assistant. Pueden incluir evidencia técnica y recomendaciones para el proceso de actualización.';
$string['privacy:metadata:local_upgradeassistant_item:category'] = 'Categoría del hallazgo.';
$string['privacy:metadata:local_upgradeassistant_item:code'] = 'Código interno del hallazgo.';
$string['privacy:metadata:local_upgradeassistant_item:description'] = 'Descripción del hallazgo, que ' .
    'puede incluir diagnósticos técnicos generados durante la creación del reporte.';
$string['privacy:metadata:local_upgradeassistant_item:evidence'] = 'Evidencia técnica del hallazgo, ' .
    'incluyendo diagnósticos de servidor o plugins cuando estén disponibles.';
$string['privacy:metadata:local_upgradeassistant_item:recommendation'] = 'Acción recomendada.';
$string['privacy:metadata:local_upgradeassistant_item:reportid'] = 'Identificador del reporte asociado.';
$string['privacy:metadata:local_upgradeassistant_item:severity'] = 'Severidad del hallazgo.';
$string['privacy:metadata:local_upgradeassistant_item:status'] = 'Estado del hallazgo.';
$string['privacy:metadata:local_upgradeassistant_item:timecreated'] = 'Fecha de creación del hallazgo.';
$string['privacy:metadata:local_upgradeassistant_item:title'] = 'Título del hallazgo.';
$string['privacy:metadata:local_upgradeassistant_plug'] = 'Almacena inventario y compatibilidad de plugins vinculados al reporte.';
$string['privacy:metadata:local_upgradeassistant_plug:compatibility'] = 'El estado de compatibilidad detectado.';
$string['privacy:metadata:local_upgradeassistant_plug:component'] = 'El nombre del componente del plugin Moodle.';
$string['privacy:metadata:local_upgradeassistant_plug:dependencyjson'] = 'Datos serializados de dependencias del plugin.';
$string['privacy:metadata:local_upgradeassistant_plug:evidence'] = 'Evidencia técnica del estado de compatibilidad.';
$string['privacy:metadata:local_upgradeassistant_plug:pluginname'] = 'Nombre del plugin.';
$string['privacy:metadata:local_upgradeassistant_plug:plugintype'] = 'Tipo de plugin.';
$string['privacy:metadata:local_upgradeassistant_plug:releaseinfo'] = 'Información de release del plugin.';
$string['privacy:metadata:local_upgradeassistant_plug:reportid'] = 'El reporte vinculado al registro de inventario de plugins.';
$string['privacy:metadata:local_upgradeassistant_plug:requires'] = 'Versión mínima de Moodle requerida por el plugin.';
$string['privacy:metadata:local_upgradeassistant_plug:statuslabel'] = 'Estado de compatibilidad legible.';
$string['privacy:metadata:local_upgradeassistant_plug:targetversion'] = 'Versión detectada del plugin en destino.';
$string['privacy:metadata:local_upgradeassistant_plug:timecreated'] = 'Fecha de creación de la fila de inventario del plugin.';
$string['privacy:metadata:local_upgradeassistant_plug:version'] = 'Versión detectada del plugin origen.';
$string['privacy:metadata:local_upgradeassistant_rep'] = 'Almacena reportes de pre-upgrade generados.';
$string['privacy:metadata:local_upgradeassistant_rep:currentbranch'] = 'La rama actual detectada de Moodle.';
$string['privacy:metadata:local_upgradeassistant_rep:currentrelease'] = 'La versión actual detectada de Moodle.';
$string['privacy:metadata:local_upgradeassistant_rep:dbtype'] = 'El tipo de base de datos detectado.';
$string['privacy:metadata:local_upgradeassistant_rep:dbversion'] = 'La versión de base de datos detectada.';
$string['privacy:metadata:local_upgradeassistant_rep:phpversion'] = 'La versión de PHP detectada.';
$string['privacy:metadata:local_upgradeassistant_rep:serverprofile'] = 'El perfil de servidor detectado.';
$string['privacy:metadata:local_upgradeassistant_rep:summary'] = 'Resumen JSON del reporte, incluyendo ' .
    'ciclo de vida, evidencia técnica y rutas sensibles del servidor cuando se requieren como evidencia de auditoría.';
$string['privacy:metadata:local_upgradeassistant_rep:targetbranch'] = 'La rama destino seleccionada de Moodle.';
$string['privacy:metadata:local_upgradeassistant_rep:targetpath'] = 'La ruta de instalación destino seleccionada.';
$string['privacy:metadata:local_upgradeassistant_rep:targetrelease'] = 'La versión destino seleccionada de Moodle.';
$string['privacy:metadata:local_upgradeassistant_rep:timecreated'] = 'La fecha de creación del reporte.';
$string['privacy:metadata:local_upgradeassistant_rep:userid'] = 'El usuario que generó el reporte.';
$string['privacy:metadata:local_upgradeassistant_rules'] = 'Almacena reglas locales por versión Moodle usadas para validación.';
$string['privacy:metadata:local_upgradeassistant_rules:enabled'] = 'Indica si la regla está habilitada.';
$string['privacy:metadata:local_upgradeassistant_rules:moodlebranch'] = 'La rama de Moodle usada por una ' .
    'regla local de validación.';
$string['privacy:metadata:local_upgradeassistant_rules:recommendation'] = 'Recomendación asociada a la regla.';
$string['privacy:metadata:local_upgradeassistant_rules:rulekey'] = 'Clave de la regla.';
$string['privacy:metadata:local_upgradeassistant_rules:rulesetversion'] = 'Versión del conjunto de reglas.';
$string['privacy:metadata:local_upgradeassistant_rules:rulesjson'] = 'Configuración serializada de la regla.';
$string['privacy:metadata:local_upgradeassistant_rules:ruletype'] = 'Tipo de regla.';
$string['privacy:metadata:local_upgradeassistant_rules:severity'] = 'Severidad de la regla.';
$string['privacy:metadata:local_upgradeassistant_rules:source'] = 'Fuente de la regla.';
$string['privacy:metadata:local_upgradeassistant_rules:targetbranch'] = 'Rama Moodle destino de la regla.';
$string['privacy:metadata:preference:scanpath'] = 'Almacena el último directorio usado para detectar instalaciones Moodle.';
$string['privacy:metadata:preference:state'] = 'Almacena el avance del asistente, la ruta objetivo ' .
    'seleccionada, rutas public/config y elementos completados del checklist.';
$string['privacy:metadata:preference:targetinfo'] = 'Almacena información de versión leída desde el archivo version.php objetivo.';
$string['privacy:metadata:preference:targetpath'] = 'Almacena la ruta de la instalación Moodle objetivo seleccionada.';
$string['procontrolpanel'] = 'Panel de control';
$string['profilecpanel'] = 'cPanel / WHM';
$string['profilelinux'] = 'Linux';
$string['profilevpslinux'] = 'VPS Linux';
$string['profilewindows'] = 'Windows';
$string['profilexampp'] = 'XAMPP / Windows local';
$string['proreportdesc'] = 'Genera evidencia persistente con el nivel de riesgo, hallazgos y verificaciones auditables.';
$string['proreportnotarget'] = 'Selecciona una instalación Moodle destino antes de generar el reporte.';
$string['proreporttitle'] = 'Informe de preparación';
$string['publicstructure'] = 'Estructura /public';
$string['purgecaches'] = 'Purgar cachés';
$string['reccpanel1'] = 'Verifica la versión PHP desde MultiPHP Manager antes de mover archivos.';
$string['reccpanel2'] = 'Confirma que el DocumentRoot apunta a la carpeta correcta, especialmente si Moodle usa /public.';
$string['reccpanel3'] = 'Programa cron desde cPanel Cron Jobs apuntando al PHP CLI correcto.';
$string['reccpanel4'] = 'Realiza la sustitución de carpetas en una ventana de mantenimiento y conserva ' .
    'la carpeta anterior como respaldo.';
$string['recheckreport'] = 'Volver a comprobar';
$string['reclinux1'] = 'Confirma propietario y permisos de código fuente y moodledata antes del cambio.';
$string['reclinux2'] = 'Valida que PHP CLI y PHP web usen la misma versión y extensiones necesarias.';
$string['reclinux3'] = 'Ejecuta la actualización primero en una copia de pruebas antes de tocar producción.';
$string['recommendation'] = 'Recomendación';
$string['recommendedroute'] = 'Ruta conservadora recomendada';
$string['recvps1'] = 'Valida backups por CLI y conserva una copia restaurable de base de datos, código y moodledata.';
$string['recvps2'] = 'Comprueba PHP-FPM/Apache/Nginx y PHP CLI antes de ejecutar el upgrade.';
$string['recvps3'] = 'Revisa permisos de moodledata y ownership del código luego de copiar plugins personalizados.';
$string['recvps4'] = 'Usa staging y un procedimiento de rollback documentado antes de producción.';
$string['recwindows1'] = 'Verifica que las extensiones PHP estén activas en el php.ini correcto.';
$string['recwindows2'] = 'Evita rutas con permisos restringidos y valida que Apache/IIS pueda leer el nuevo código.';
$string['recwindows3'] = 'Programa cron con el ejecutable PHP correcto y rutas absolutas.';
$string['recxampp1'] = 'Activa las extensiones PHP requeridas en el php.ini de XAMPP.';
$string['recxampp2'] = 'Reinicia Apache después de cambiar PHP o extensiones.';
$string['recxampp3'] = 'Usa rutas locales simples y evita espacios o caracteres especiales en carpetas críticas.';
$string['recxampp4'] = 'Programa cron en Windows con el php.exe correcto solo para pruebas locales.';
$string['redacted'] = '[Redactado]';
$string['redactedexportnotice'] = 'Esta es una exportación redactada. Se ocultaron rutas sensibles, ' .
    'datos de base de datos y ubicaciones del servidor.';
$string['redactedhtmlshort'] = 'HTML redactado';
$string['redactedpdfshort'] = 'PDF redactado';
$string['referencerules'] = 'Ver reglas de referencia';
$string['refreshscan'] = 'Actualizar escaneo';
$string['reportcategorychecklist'] = 'Checklist';
$string['reportcategorydatabase'] = 'Base de datos';
$string['reportcategorylifecycle'] = 'Ciclo de vida';
$string['reportcategorypath'] = 'Ruta de actualización';
$string['reportcategoryplugins'] = 'Plugins';
$string['reportcategoryrequirements'] = 'Requisitos';
$string['reportcategoryserver'] = 'Servidor';
$string['reportcategorytheme'] = 'Tema';
$string['reportfindings'] = 'Hallazgos del reporte';
$string['reportgenerated'] = 'Reporte de pre-upgrade #{$a} generado.';
$string['reporthistorydesc'] = 'Historial de reportes generados para comparar diagnósticos y conservar evidencia.';
$string['reporthistorydescnew'] = 'Abre un informe anterior para comparar el nivel de riesgo y conservar ' .
    'la trazabilidad del proceso.';
$string['reporthistorytitle'] = 'Historial de reportes';
$string['reportid'] = 'ID del reporte';
$string['reportlastchecked'] = 'Última comprobación';
$string['reportnotactive'] = 'Solo puedes actualizar el informe activo de este asistente.';
$string['reportrechecked'] = 'Informe #{$a} actualizado con una nueva comprobación.';
$string['reportspageheading'] = 'Informes de preparación';
$string['reportspageintro'] = 'Consulta el diagnóstico actual, el checklist auditable y los informes ' .
    'anteriores sin interrumpir el flujo del asistente.';
$string['reportstab'] = 'Informes';
$string['reporttarget'] = 'Destino del reporte';
$string['reporttargetchanged'] = 'El origen, el destino o la rama cambió. Selecciona el destino correcto antes de comprobar este informe.';
$string['required'] = 'Requerido';
$string['requiredvalue'] = 'Valor requerido';
$string['requires'] = 'Requires';
$string['resetwizard'] = 'Reiniciar asistente';
$string['result'] = 'Resultado';
$string['returntoassistant'] = 'Volver al asistente';
$string['reviewexecution'] = 'Revisar ejecución';
$string['reviewnoterequired'] = 'Escribe el criterio o la evidencia de la revisión antes de marcar el hallazgo como revisado.';
$string['risklevel'] = 'Nivel de riesgo';
$string['risklevelcritical'] = 'Riesgo crítico';
$string['risklevelhigh'] = 'Riesgo alto';
$string['risklevellow'] = 'Riesgo bajo';
$string['risklevelmedium'] = 'Riesgo medio';
$string['risknotcalculated'] = 'Sin calcular';
$string['riskscore'] = 'Nivel de riesgo';
$string['riskscoreexplanation'] = 'La puntuación describe los hallazgos de la última comprobación. ' .
    'La revisión escrita registra una decisión administrativa, pero no elimina el hallazgo ni reduce su puntuación. ' .
    'Tras corregir la instalación destino, pulsa Volver a comprobar: ' .
    'si el hallazgo desaparece, quedará resuelto y disminuirá el riesgo de este mismo informe.';
$string['riskscoresummary'] = 'Basado en este informe; revisarlo no disminuye la puntuación.';
$string['route'] = 'Ruta';
$string['ruleoptional'] = 'Opcional';
$string['rulerecommended'] = 'Recomendado';
$string['rulerequired'] = 'Requerido';
$string['rulesbuiltinrecommendation'] = 'Regla local incluida con el plugin. Puede ser reemplazada por ' .
    'una API de compatibilidad en versiones comerciales futuras.';
$string['rulesbyversiondesc'] = 'Reglas locales usadas para evaluar compatibilidad por versión Moodle. ' .
    'Esta estructura prepara la sincronización futura desde una API externa.';
$string['rulesbyversiontitle'] = 'Reglas de referencia';
$string['rulesourcebuiltin'] = 'Incluida en el plugin';
$string['scan'] = 'Escanear';
$string['scaninstallations'] = 'Instalaciones Moodle disponibles';
$string['scaninstallationsdesc'] = 'El asistente busca carpetas que contengan un archivo version.php de ' .
    'Moodle. Por seguridad, solo escanea directorios cercanos a la instalación actual.';
$string['scanpathlabel'] = 'Directorio base para escanear';
$string['scanrefreshed'] = 'El escaneo de instalaciones fue actualizado.';
$string['selectedtarget'] = 'Instalación destino seleccionada';
$string['selectionresult'] = 'Resultado de la selección';
$string['selecttargetaction'] = 'Seleccionar versión destino';
$string['selecttargetfirst'] = 'Primero selecciona la carpeta Moodle con la versión a la que deseas actualizar.';
$string['selectthisversion'] = 'Seleccionar';
$string['sensitivediagnosticswarning'] = 'Esta página muestra diagnósticos técnicos sensibles, ' .
    'incluyendo rutas del servidor y ubicaciones de instalaciones Moodle. Esta vista está destinada ' .
    'únicamente a administradores Moodle y operadores de servidor autorizados.';
$string['sensitivehidden'] = 'Oculto. Requiere permiso para ver diagnósticos sensibles.';
$string['server'] = 'Servidor';
$string['serverprofile'] = 'Perfil de servidor';
$string['serverrecommendationsdesc'] = 'Recomendaciones específicas según el tipo de entorno detectado.';
$string['serverrecommendationstitle'] = 'Recomendaciones por tipo de servidor';
$string['settingspage'] = 'Smart Upgrade Assistant';
$string['severity'] = 'Severidad';
$string['severitycritical'] = 'Crítica';
$string['severityhigh'] = 'Alta';
$string['severityinfo'] = 'Info';
$string['severitylow'] = 'Baja';
$string['severitymedium'] = 'Media';
$string['sodium'] = 'Sodium';
$string['source'] = 'Fuente';
$string['statecleared'] = 'El asistente se reinició por completo. Informes asociados eliminados: {$a}.';
$string['status'] = 'Estado';
$string['stepcompleted'] = 'Paso del checklist marcado como completado.';
$string['stepdiagnosis'] = 'Diagnóstico';
$string['stepexecution'] = 'Ejecución y evidencia';
$string['stepfourof'] = 'Paso 4 de 4';
$string['steponeof'] = 'Paso 1 de 4';
$string['steppreparation'] = 'Preparación';
$string['steptarget'] = 'Versión destino';
$string['stepthreeof'] = 'Paso 3 de 4';
$string['steptwoof'] = 'Paso 2 de 4';
$string['subject'] = 'Asunto';
$string['switchtoboosttheme'] = 'Cambiar a Boost y marcar listo';
$string['synclifecycletask'] = 'Sincronizar ciclo de vida oficial de Moodle';
$string['targetconfigpath'] = 'Ruta config.php destino';
$string['targetheading'] = 'Selecciona la versión destino';
$string['targetintro'] = 'Elige una instalación detectada o introduce una ruta manual. El asistente ' .
    'validará la ruta antes de continuar.';
$string['targetnotfound'] = 'La carpeta seleccionada no parece ser una instalación Moodle válida porque ' .
    'no se encontró version.php.';
$string['targetpathlabel'] = 'Ruta completa de la carpeta Moodle destino';
$string['targetpathplaceholder'] = 'Ruta completa de la carpeta Moodle destino';
$string['targetpublicdetectedshort'] = '/public destino';
$string['targetpublicpath'] = 'Ruta public destino';
$string['targetpublicstructure'] = 'Estructura /public en destino';
$string['targetselected'] = 'Instalación Moodle objetivo seleccionada.';
$string['targetvalidation'] = 'Resultado de la selección';
$string['targetversion'] = 'Versión destino';
$string['technicalcompatibility'] = 'Compatibilidad técnica';
$string['technicaldatavisible'] = 'Datos técnicos visibles';
$string['technicaldatavisibledesc'] = 'Tienes permiso para consultar rutas y diagnósticos sensibles del servidor.';
$string['type'] = 'Tipo';
$string['usetarget'] = 'Usar como versión destino';
$string['validateandselect'] = 'Validar y seleccionar';
$string['validationdatabase'] = 'Base de datos mínima';
$string['validationdesc'] = 'Comprobación técnica del entorno contra las reglas de la versión destino seleccionada.';
$string['validationfindingdesc'] = 'Valor actual: {$a->current}. Valor requerido: {$a->required}.';
$string['validationfindingrec'] = 'Corrige este requisito antes de proceder con la actualización de producción.';
$string['validationmaxinputvars'] = 'PHP max_input_vars';
$string['validationmoodlesource'] = 'Versión Moodle origen mínima';
$string['validationpartialrule'] = 'Requisitos parciales de la versión destino';
$string['validationpartialruledesc'] = 'Los requisitos mostrados provienen de admin/environment.xml del ' .
    'paquete de destino. La ruta mínima de actualización y otros requisitos no identificados deben ' .
    'verificarse en las notas oficiales de esa versión.';
$string['validationphp64bit'] = 'PHP de 64 bits';
$string['validationphpversion'] = 'Versión mínima de PHP';
$string['validationruleunknown'] = 'Regla de versión no disponible';
$string['validationruleunknowndesc'] = 'No existe una regla local para la versión destino seleccionada.';
$string['validationsodium'] = 'Extensión PHP sodium';
$string['validationstatusfail'] = 'No cumple';
$string['validationstatuspass'] = 'Cumple';
$string['validationstatuswarning'] = 'Revisar';
$string['validationtitle'] = 'Compatibilidad con la versión destino';
$string['value'] = 'Valor';
$string['verified'] = 'Verificado';
$string['verifyboosttheme'] = 'Verificar tema Boost';
$string['version'] = 'Versión';
$string['viewdetails'] = 'Ver detalles';
$string['viewfullreport'] = 'Ver informe completo';
$string['viewmanualinstructions'] = 'Ver instrucciones manuales';
$string['viewtargettechnicaldetails'] = 'Ver detalles técnicos del destino';
$string['viewtechnicalserverinfo'] = 'Ver información técnica del servidor';
$string['warningsremaining'] = 'Hay {$a} elemento(s) que requieren revisión antes de ejecutar la actualización.';
$string['wizardcontrol'] = 'Opciones del asistente';
$string['wizardcontroldesc'] = 'El progreso se guarda únicamente en tus preferencias de usuario de Moodle.';
$string['wizardsteps'] = 'Fases del asistente';
$string['wwwroot'] = 'wwwroot';
