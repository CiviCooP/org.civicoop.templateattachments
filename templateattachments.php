<?php

require_once 'templateattachments.civix.php';

$templateattachments_message_form_build = false;
$templateattachments_message_form = false;
$templateattachments_template_id = false;

function templateattachments_civicrm_alterMailParams(&$params, $context) {
  global $templateattachments_template_id;
  $template_id = false;
  if (!empty($params['messageTemplateID'])) {
    $template_id = $params['messageTemplateID'];
  }
  elseif (!empty($templateattachments_template_id)) {
    $template_id = $templateattachments_template_id;
  }
  elseif (isset($params['job_id'])) {
    $job_id = $params['job_id'];
    $sql = "SELECT civicrm_mailing.msg_template_id
            FROM civicrm_mailing_job
            INNER JOIN civicrm_mailing ON civicrm_mailing_job.mailing_id = civicrm_mailing.id
            WHERE civicrm_mailing_job.id = %1";
    $sql_params[1] = array($job_id, 'Integer');
    $template_id = CRM_Core_DAO::singleValueQuery($sql, $sql_params);
  }

  if ($template_id) {
    // Lookup the attachments of this template.
    $attachments = CRM_Core_BAO_File::getEntityFile('civicrm_msg_template', $template_id);
    if (!empty($attachments)) {
      foreach($attachments as $attachment) {
        $params['attachments'][$attachment['fileID']] = $attachment;
      }
    }
  }
}

function templateattachments_civicrm_buildForm($formName, &$form) {
  global $templateattachments_message_form_build;
  if ($formName == 'CRM_Admin_Form_MessageTemplates') {
    $template_id = $form->getVar('_id') ? $form->getVar('_id') : NULL;
    $numAttachments = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'max_attachments');
    CRM_Core_BAO_File::buildAttachment($form, 'civicrm_msg_template', $template_id, $numAttachments);
    $form->updateAttributes(array('enctype' => 'multipart/form-data'));
    $form->setMaxFileSize();
    // This allows us to switch back to and edit the message template without attachment
    if ($form->elementExists('file_type')) {
      $elem = $form->getElement('file_type');
      $elem->unfreeze();
    }
    $templateattachments_message_form_build = true;
  }
}

function templateattachments_civicrm_alterContent(&$content, $context, $tplName, &$object ) {
  if ($object instanceof CRM_Admin_Form_MessageTemplates) {
    $template = CRM_Core_Smarty::singleton();
    $content .= $template->fetch('CRM/Templateattachments/Form/Admin.tpl');
  }
}

function templateattachments_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  global $templateattachments_message_form;
  global $templateattachments_template_id;
  if ($formName == 'CRM_Admin_Form_MessageTemplates') {
    $templateattachments_message_form = $form;
  }
  if ($formName == 'CRM_Contact_Form_Task_Email') {
    if (!empty($fields['template'])) {
      $templateattachments_template_id = $fields['template'];
    }
  }
}

function templateattachments_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  global $templateattachments_message_form_build;
  global $templateattachments_message_form;
  if ($objectName == 'MessageTemplate' && $templateattachments_message_form_build) {
    $params = array(); //used for attachments
    $templateattachments_message_form_values = $templateattachments_message_form->controller->exportValues();
    CRM_Core_BAO_File::formatAttachment($templateattachments_message_form_values, $params, 'civicrm_msg_template', $objectId);

    $numAttachments = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'max_attachments');
    for ($i = 1; $i <= $numAttachments; $i++) {
      if (isset($params["attachFile_$i"]) &&  is_array($params["attachFile_$i"]) && !empty($params["attachFile_$i"]['location'])) {
        CRM_Core_BAO_File::filePostProcess(
          $params["attachFile_".$i]["location"],
          NULL,
          'civicrm_msg_template',
          $objectId,
          NULL,
          TRUE,
          $params["attachFile_$i"],
          "attachFile_$i",
          $params["attachFile_$i"]['type']
        );
      }
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function templateattachments_civicrm_config(&$config) {
  _templateattachments_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function templateattachments_civicrm_xmlMenu(&$files) {
  _templateattachments_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function templateattachments_civicrm_install() {
  _templateattachments_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function templateattachments_civicrm_uninstall() {
  _templateattachments_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function templateattachments_civicrm_enable() {
  _templateattachments_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function templateattachments_civicrm_disable() {
  _templateattachments_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function templateattachments_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _templateattachments_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function templateattachments_civicrm_managed(&$entities) {
  _templateattachments_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function templateattachments_civicrm_caseTypes(&$caseTypes) {
  _templateattachments_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function templateattachments_civicrm_angularModules(&$angularModules) {
_templateattachments_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function templateattachments_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _templateattachments_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function templateattachments_civicrm_preProcess($formName, &$form) {

}

*/
