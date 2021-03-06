<?php

require_once 'sumfields.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function sumfields_civicrm_config(&$config) {
  _sumfields_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function sumfields_civicrm_xmlMenu(&$files) {
  _sumfields_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_navigationMenu
 *
 * @param $params array
 */
function sumfields_civicrm_navigationMenu(&$params) {
  $path = "Administer/Customize Data and Screens";
  $item = array(
    'label' => ts('Summary Fields'),
    'name' => 'Summary Fields',
    'url' => 'civicrm/admin/setting/sumfields',
    'permission' => 'administer CiviCRM',
    'operator' => '',
    'separator' => '',
    'active' => 1,
  );

  $ret = _sumfields_civix_insert_navigation_menu($params, $path, $item);
}

/**
 * Implementation of hook_civicrm_install
 */
function sumfields_civicrm_install() {
  return _sumfields_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function sumfields_civicrm_uninstall() {
  return _sumfields_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function sumfields_civicrm_enable() {
  sumfields_initialize_user_settings();
  $session = CRM_Core_Session::singleton();
  if(!sumfields_create_custom_fields_and_table()) {
    $msg = ts("Failed to create custom fields and table. Maybe they already exist?");
    $session->setStatus($msg);
  }
  $msg = ts("The extension is enabled. Please go to Adminster -> Customize Data and Screens -> Summary Fields to configure it.");
  $session->setStatus($msg);

  return _sumfields_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function sumfields_civicrm_disable() {
  sumfields_deinitialize_custom_data();
  sumfields_delete_user_settings();
  return _sumfields_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function sumfields_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sumfields_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function sumfields_civicrm_managed(&$entities) {
  return _sumfields_civix_civicrm_managed($entities);
}

/**
 * Replace %variable with the actual
 * values that the user has configured to limit to.
 **/
function sumfields_sql_rewrite($sql) {
  // Note: most of these token replacements fill in a sql IN statement,
  // e.g. field_name IN (%token). That means if the token is empty, we
  // get a SQL error. So... for each of these, if the token is empty,
  // we fill it with all possible values at the moment. If a new option
  // is added, summary fields will have to be re-configured.
  $ids = sumfields_get_setting('financial_type_ids', array());
  if(count($ids) == 0) {
    $ids = array_keys(sumfields_get_all_financial_types());
  }
  $str_ids = implode(',', $ids);
  $sql = str_replace('%financial_type_ids', $str_ids, $sql);

  $ids = sumfields_get_setting('membership_financial_type_ids', array());
  if(count($ids) == 0) {
    // Surely this is wrong... but better to avoid a sql error
    $ids = array_keys(sumfields_get_all_financial_types());
  }
  $str_ids = implode(',', $ids);
  $sql = str_replace('%membership_financial_type_ids', $str_ids, $sql);

  $ids = sumfields_get_setting('participant_status_ids', array());
  if(count($ids) == 0) {
    $ids = array_keys(sumfields_get_all_participant_status_types());
  }
  $str_ids = implode(',', $ids);
  $sql = str_replace('%participant_status_ids', $str_ids, $sql);

  $ids = sumfields_get_setting('participant_noshow_status_ids', array());
  if(count($ids) == 0) {
    $ids = array_keys(sumfields_get_all_participant_status_types());
  }
  $str_ids = implode(',', $ids);
  $sql = str_replace('%participant_noshow_status_ids', $str_ids, $sql);

  $ids = sumfields_get_setting('event_type_ids', array());
  if(count($ids) == 0) {
    $ids = array_keys(sumfields_get_all_event_types());
  }
  $str_ids = implode(',', $ids);
  $sql = str_replace('%event_type_ids', $str_ids, $sql);

  $fiscal_dates = sumfields_get_fiscal_dates();
  $keys = array_keys($fiscal_dates);
  $values = array_values($fiscal_dates);
  $sql = str_replace($keys, $values, $sql);

  $participant_info_table_name = sumfields_get_participant_info_table();
  if($participant_info_table_name) {
    $sql = str_replace('%civicrm_value_participant_info', $participant_info_table_name, $sql);
  } 
  elseif(preg_match('/%civicrm_value_participant_info/', $sql)) {
    // This is an error - we have a variable we can't replace.
    return FALSE;
  }
  $reminder_response_field = sumfields_get_column_name('reminder_response');
  if($reminder_response_field) {
    $sql = str_replace('%reminder_response', $reminder_response_field, $sql);
  }
  elseif(preg_match('/%reminder_response/', $sql)) {
    // This is an error - we have a variable we can't replace.
    return FALSE;
  }
  $invitation_response_field = sumfields_get_column_name('invitation_response');
  if($invitation_response_field) {
    $sql = str_replace('%invitation_response', $invitation_response_field, $sql);
  }
  elseif(preg_match('/%invitation_response/', $sql)) {
    // This is an error - we have a variable we can't replace.
    return FALSE;
  }
  $event_attended_total_lifetime_field = sumfields_get_column_name('event_attended_total_lifetime');
  if($event_attended_total_lifetime_field) {
    $sql = str_replace('%event_attended_total_lifetime', $event_attended_total_lifetime_field, $sql);
  }
  elseif(preg_match('/%event_attended_total_lifetime/', $sql)) {
    // This is an error - we have a variable we can't replace.
    return FALSE;
  }
  return $sql;
}

/**
 * Based on the civicrm fiscal date setting, determine the dates for the
 * various begin and end fiscal year dates needed by the rewrite function.
 **/
function sumfields_get_fiscal_dates() {
  $ret = array(
    '%current_fiscal_year_begin' => NULL,
    '%current_fiscal_year_end' => NULL,
    '%last_fiscal_year_begin' => NULL,
    '%last_fiscal_year_end' => NULL,
    '%year_before_last_fiscal_year_begin' => NULL,
    '%year_before_last_fiscal_year_end' => NULL,
  );
  $config = CRM_Core_Config::singleton();

  // These are returned as not zero-padded numbers,
  // e.g. 1 and 1 or 9 and 1
  $fiscal_month = sumfields_zero_pad($config->fiscalYearStart['M']);
  $fiscal_day = sumfields_zero_pad($config->fiscalYearStart['d']);

  $this_calendar_year_fiscal_year_begin_ts = strtotime(date('Y') . '-' . $fiscal_month . '-' . $fiscal_day);
  $now = time();
  if($now < $this_calendar_year_fiscal_year_begin_ts) {
    // We need to adjust the current fiscal year back one year. For example, it's Feb 3
    // and the fiscal year begins Sep 1, the current fiscal year started Sep 1 of the
    // last calendar year.
    $current_fiscal_year_begin_ts = strtotime('-1 year', $this_calendar_year_fiscal_year_begin_ts);
    $current_fiscal_year_end_ts = strtotime('-1 day', $this_calendar_year_fiscal_year_begin_ts);
    $last_fiscal_year_begin_ts = strtotime('-2 year', $this_calendar_year_fiscal_year_begin_ts);
    $last_fiscal_year_end_ts = strtotime('-1 year -1 day', $this_calendar_year_fiscal_year_begin_ts);
    $year_before_last_fiscal_year_begin_ts = strtotime('-3 year', $this_calendar_year_fiscal_year_begin_ts);
    $year_before_last_fiscal_year_end_ts = strtotime('-2 year -1 day', $this_calendar_year_fiscal_year_begin_ts);
  }
  else {
    $current_fiscal_year_begin_ts = $this_calendar_year_fiscal_year_begin_ts;
    $current_fiscal_year_end_ts = strtotime('+1 year -1 day', $this_calendar_year_fiscal_year_begin_ts);
    $last_fiscal_year_begin_ts = strtotime('-1 year', $this_calendar_year_fiscal_year_begin_ts);
    $last_fiscal_year_end_ts = strtotime('-1 day', $this_calendar_year_fiscal_year_begin_ts);
    $year_before_last_fiscal_year_begin_ts = strtotime('-2 year', $this_calendar_year_fiscal_year_begin_ts);
    $year_before_last_fiscal_year_end_ts = strtotime('-1 year -1 day', $this_calendar_year_fiscal_year_begin_ts);
  }
  return array(
    '%current_fiscal_year_begin' => date('Y-m-d', $current_fiscal_year_begin_ts),
    '%current_fiscal_year_end' => date('Y-m-d', $current_fiscal_year_end_ts),
    '%last_fiscal_year_begin' => date('Y-m-d', $last_fiscal_year_begin_ts),
    '%last_fiscal_year_end' => date('Y-m-d', $last_fiscal_year_end_ts),
    '%year_before_last_fiscal_year_begin' => date('Y-m-d', $year_before_last_fiscal_year_begin_ts),
    '%year_before_last_fiscal_year_end' => date('Y-m-d', $year_before_last_fiscal_year_end_ts),
  );
}

/**
 * Utility function for calculating fiscal years
 **/
function sumfields_zero_pad($num) {
  if(strlen($num) == 1) return '0' . $num;
  return $num;
}

/**
 * hook_civicrm_triggerInfo()
 *
 * Add triggers for our tables
 **/

function sumfields_civicrm_triggerInfo(&$info, $tableName) {
  // Our triggers all use custom fields. CiviCRM, when generating
  // custom fields, sometimes gives them different names (appending
  // the id in most cases) to avoid name collisions.
  //
  // So, we have to retrieve the actual name of each field that is in
  // use.

  $table_name = _sumfields_get_custom_table_name();
  $custom_fields = _sumfields_get_custom_field_parameters();

  // Load the field and group definitions because we need the trigger
  // clause that is stored here.
  $custom = sumfields_get_custom_field_definitions();

  // We create a trigger sql statement for each table that should
  // have a trigger
  $tables = array();
  $generic_sql = "INSERT INTO `$table_name` SET ";
  $sql_field_parts = array();

  $active_fields = sumfields_get_setting('active_fields', array());

  $session = CRM_Core_Session::singleton();

  // Iterate over all our fields, and build out a sql parts array
  while(list($base_column_name, $params) = each($custom_fields)) {
    if(!in_array($base_column_name, $active_fields)) continue;
    $table = $custom['fields'][$base_column_name]['trigger_table'];
    if(!is_null($tableName) && $tableName != $table) {
      // if triggerInfo is called with particular table name, we should
      // only respond if we are contributing triggers to that table.
      continue;
    }
    $trigger = $custom['fields'][$base_column_name]['trigger_sql'];
    $trigger = sumfields_sql_rewrite($trigger);
    // If we fail to properly rewrite the sql, don't set the trigger
    // to avoid sql exceptions.
    if(FALSE === $trigger) {
      $msg = sprintf(ts("Failed to rewrite sql for %s field."), $base_column_name);
      $session->setStatus($msg);
      continue;
    }
    $sql_field_parts[$table][] = '`' . $params['column_name'] . '` = ' .
      $trigger;
    // Keep track of which tables we need to build triggers for.
    if(!in_array($table, $tables)) $tables[] = $table;
  }
  
  // Iterate over each table that needs a trigger, build the trigger's
  // sql clause.
  while(list(, $table) = each($tables)) {
    $parts = $sql_field_parts[$table];
    $parts[] = 'entity_id = NEW.contact_id';

    $extra_sql = implode(',', $parts);
    $sql = $generic_sql . $extra_sql . ' ON DUPLICATE KEY UPDATE ' . $extra_sql . ';';

    // We want to fire this trigger on insert, update and delete.
    $info[] = array(
      'table' => $table,
      'when' => 'AFTER',
      'event' => 'INSERT',
      'sql' => $sql,
     );
    $info[] = array(
      'table' => $table,
      'when' => 'AFTER',
      'event' => 'UPDATE',
      'sql' => $sql,
    );
    // For delete, we reference OLD.field instead of NEW.field
    $sql = str_replace('NEW.', 'OLD.', $sql);
    $info[] = array(
      'table' => $table,
      'when' => 'AFTER',
      'event' => 'DELETE',
      'sql' => $sql,
    );
  }
}

/**
 * Generate a temporary table with just fields from either the contribution
 * or participant triggers. This function is used when populating the initial
 * data after changing fields, etc.
 */
function sumfields_create_temporary_table($trigger_table) {
  $name = CRM_Core_DAO::createTempTableName();

  // These are the actual field names as created in this instance
  $custom_fields = _sumfields_get_custom_field_parameters();

  // Load the field and group definitions because we need to know
  // which fields are triggered on which tables 
  $custom_field_definitions = sumfields_get_custom_field_definitions();
  $definitions = $custom_field_definitions['fields'];

  $create_fields = array();

  // Initialize with a field to hold the entity_id
  $create_fields[] = "`contact_id` INT";
  // Iterate over the actual instantiated summary fields
  while(list($field_name, $values) = each($custom_fields)) {
    // Avoid error - make sure we have a definition for this field.
    if(array_key_exists($field_name, $definitions)) {
      $field_definition = $definitions[$field_name];
      if($field_definition['trigger_table'] == $trigger_table) {
        $data_type = $field_definition['data_type'];
        if($data_type == 'Money') {
          $data_type = "DECIMAL(10,2)";
        } 
        elseif($data_type == 'Date') {
          $data_type = 'datetime';
        }
        elseif($data_type == 'String') {
          $data_type = 'varchar(128)';
        }
        $create_fields[] = "`$field_name` $data_type";
      }
    }
  }
  $sql = "CREATE TABLE `$name` ( ".
    implode($create_fields, ',') . ')';
  CRM_Core_DAO::executeQuery($sql); 
  return $name;
}

/**
 * Generate calculated fields for all contacts.
 * This function is designed to be run once when
 * the extension is installed or initialized.
 **/
function sumfields_generate_data_based_on_current_data($session = NULL) {
  // Get the actual table name for summary fields.
  $table_name = _sumfields_get_custom_table_name();

  // These are the summary field definitions as they have been instantiated
  // on this site (with actual column names, etc.)
  $custom_fields = _sumfields_get_custom_field_parameters();
  if(is_null($session)) {
    $session = CRM_Core_Session::singleton();
  }
  if(empty($table_name)) {
    $session->setStatus(ts("Your configuration may be corrupted. 
      Please disable and renable this extension."));
    return FALSE;
  }
  $session->setStatus(ts("Regenerating content in your summary fields table."));

  // In theory we shouldn't have to truncate the table, but we
  // are doing it just to be sure it's empty.
  $sql = "TRUNCATE TABLE `$table_name`";
  $dao = CRM_Core_DAO::executeQuery($sql);

  // Load the field and group definitions because we need the trigger
  // clause that is stored here. These are the generically shipped
  // field definitions (via custom.php).
  $custom = sumfields_get_custom_field_definitions();

  $active_fields = sumfields_get_setting('active_fields', array());

  // Variables used for building the temp tables and temp insert statement.
  $contribution_sql = NULL;
  $contribution_sql_parts = array();
  $contribution_sql_parts[] = 'contact_id';
  $participant_sql = NULL;
  $participant_sql_parts = array();
  $participant_sql_parts[] = 'contact_id';

  // Variables used for building the final insert statement into the actual
  // summary fields table
  $final_sql = "INSERT INTO `$table_name` ";
  $final_sql_parts = array();
  $final_sql_parts[] = 'NULL';
  $final_sql_parts[] = 'contact_id';

  if(sumfields_are_any_contribution_fields_active()) {
    $contribution_temp_table = sumfields_create_temporary_table('civicrm_contribution');
    $contribution_sql = "INSERT INTO `$contribution_temp_table` SELECT ";
  }

  if(sumfields_are_any_event_fields_active()) {
    $participant_temp_table = sumfields_create_temporary_table('civicrm_participant');
    $participant_sql = "INSERT INTO `$participant_temp_table` SELECT ";
  }

  $tables = array();
  while(list($base_column_name, $params) = each($custom_fields)) {
    if(!in_array($base_column_name, $active_fields)) continue;
    $table = $custom['fields'][$base_column_name]['trigger_table'];

    $trigger = $custom['fields'][$base_column_name]['trigger_sql'];
    // We replace NEW.contact_id with t2.contact_id to reflect the difference
    // between the trigger sql statement and the initial sql statement
    // to load the data.
    $trigger = str_replace('NEW.contact_id', 't2.contact_id', $trigger);
    if(FALSE === $trigger = sumfields_sql_rewrite($trigger)) {
      $msg = sprintf(ts("Failed to rewrite sql for %s field."), $base_column_name);
      $session->setStatus($msg);
      continue;
    }
    if($table == 'civicrm_participant') {
      $participant_sql_parts[] = $trigger;
    }
    elseif($table == 'civicrm_contribution') {
      $contribution_sql_parts[] = $trigger;
    }
    // For the final query, we just need the field name
    $final_sql_parts[] = $base_column_name;
  }

  if(!is_null($contribution_sql)) {
    $contribution_sql .= implode(",\n", $contribution_sql_parts);
    $contribution_sql .= ' FROM `civicrm_contribution` AS t2 ';
    // Add relationship to contact to avoid sql failures due to incorrect
    // existing data + foreign key constraints
    $contribution_sql .= "JOIN civicrm_contact AS c ON t2.contact_id = c.id ";
    $contribution_sql .= ' WHERE t2.contribution_status_id = 1';
    $contribution_sql .= ' GROUP BY contact_id';
    CRM_Core_DAO::executeQuery($contribution_sql);
    // echo "$contribution_sql\n\n";
  }
  if(!is_null($participant_sql)) {
    $participant_sql .= implode(",\n", $participant_sql_parts);
    $participant_sql .= ' FROM `civicrm_participant` AS t2 ';
    $participant_sql .= "JOIN civicrm_contact AS c ON t2.contact_id = c.id ";
    $participant_sql .= ' GROUP BY contact_id';
    CRM_Core_DAO::executeQuery($participant_sql);
    // echo "$participant_sql\n\n";
  }
  $final_fields = implode(",\n", $final_sql_parts);
  if(!is_null($contribution_sql) && !is_null($participant_sql)) {
    // We have both a contribution and participant table to deal with - this
    // will require a more complicated UNION query to pull them both together
    // into the summary fields table.
    $final_sql .= " SELECT $final_fields FROM `$contribution_temp_table` c LEFT JOIN ".
      "`$participant_temp_table` t USING(contact_id) UNION SELECT $final_fields FROM ".
      "`$participant_temp_table` p LEFT JOIN `$contribution_temp_table` c USING(contact_id) ".
      "WHERE c.contact_id IS NULL";
  }
  elseif(!is_null($participant_sql)) {
    // Only participant fields.
    $final_sql .= "(SELECT $final_fields FROM `$participant_temp_table`)";
  }
  elseif(!is_null($contribution_sql)) {
    // Only contribution fields.
    $final_sql .= "(SELECT $final_fields FROM `$contribution_temp_table`)";
  }
  // echo "final: $final_sql\n";
  CRM_Core_DAO::executeQuery($final_sql);

  return TRUE;
}

/**
 * Helper function to see if any of the event fields
 * are active.
 **/

function sumfields_are_any_event_fields_active() {
  // Fields chosen by the user
  $active_fields = sumfields_get_setting('active_fields', array());
  // All custom field definitions.
  $custom = sumfields_get_custom_field_definitions();
  // Iterate over our active fields looking for ones using the
  // civicrm_participant table.
  while(list(,$base_column_name) = each($active_fields)) {
    $table = $custom['fields'][$base_column_name]['trigger_table'];
    if($table == 'civicrm_participant') {
      return TRUE;
    }
  }
  return FALSE;
}

/**
 * Helper function to see if any of the contribution fields
 * are active.
 **/

function sumfields_are_any_contribution_fields_active() {
  // Fields chosen by the user
  $active_fields = sumfields_get_setting('active_fields', array());
  // All custom field definitions.
  $custom = sumfields_get_custom_field_definitions();
  // Iterate over our active fields looking for ones using the
  // civicrm_contribution table.
  while(list(,$base_column_name) = each($active_fields)) {
    $table = $custom['fields'][$base_column_name]['trigger_table'];
    if($table == 'civicrm_contribution') {
      return TRUE;
    }
  }
  return FALSE;
}
/**
 * Create custom fields - should be called on enable.
 **/
function sumfields_create_custom_fields_and_table() {
  $session = CRM_Core_Session::singleton();

  // Load the field and group definitions.
  $custom = sumfields_get_custom_field_definitions();

  // Create the custom group first.
  $params = array_pop($custom['groups']);
  $params['version'] = 3;
  $result = civicrm_api('CustomGroup', 'create', $params);
  if($result['is_error'] == 1) {
    // Bail. No point in continuing if we can't get the table built.
    return FALSE;
  }
  // We need the id for creating the fields below.
  $value = array_pop($result['values']);
  $custom_group_id = $value['id'];

  // Save the info so we can delete it when uninstalling.
  $custom_table_parameters = array(
    'id' => $custom_group_id,
    'table_name' => $value['table_name'],
  );
  sumfields_save_setting('custom_table_parameters', $custom_table_parameters);
  $custom_field_parameters = array();

  // Get an array of fields that the user wants to use.
  $active_fields = sumfields_get_setting('active_fields', array());
  // Now create the fields.
  while(list($name, $field) = each($custom['fields'])) {
    // Skip fields not selected by the user.
    if(!in_array($name, $active_fields)) continue;

    $params = $field;
    $params['version'] = 3;
    $params['custom_group_id'] = $custom_group_id;
    $params['triggerRebuild'] = FALSE;
    $result = civicrm_api('CustomField', 'create', $params);
    if($result['is_error'] == 1) {
      $session->setStatus(sprintf(ts("Error creating custom field '%s'"), $name));
      $session->setStatus(print_r($result, TRUE));
      continue;
    }
    $value = array_pop($result['values']);
    $custom_field_parameters[$name] = array(
      'id' => $value['id'],
      'column_name' => $value['column_name']
    );
  }
  sumfields_save_setting('custom_field_parameters', $custom_field_parameters);
  return TRUE;
}

/**
 * Helper function for storing persistant data
 * for this extension.
 **/
function sumfields_save_setting($key, $value) {
  $group = 'Summary Fields';
  CRM_Core_BAO_Setting::setItem($value, $group, $key);
}

/**
 * Helper function for getting persistant data
 * for this extension.
 **/
function sumfields_get_setting($key, $default = NULL) {
  $group = 'Summary Fields';
  $ret = CRM_Core_BAO_Setting::getItem($group, $key);
  if(empty($ret)) return $default;
  return $ret;
}

/**
 * Delete custom fields. All data should be
 * generated data, so no worry about deleting
 * anything that should be kept.
 **/
function sumfields_delete_custom_fields_and_table() {
  $session = CRM_Core_Session::singleton();
  $custom_field_parameters = _sumfields_get_custom_field_parameters();

  $active_fields = sumfields_get_setting('active_fields', array());
  while(list($key, $field) = each($custom_field_parameters)) {
    // Skip fields not active (they should not have been created so
    // should not exist.
    if(!in_array($key, $active_fields)) continue;

    $params = array(
      'id' => $field['id'],
      'version' => 3
    );
    $result = civicrm_api('CustomField', 'delete', $params);
    if($result['is_error'] == 1) {
      $column_name = $field['column_name'];
      $session->setStatus(sprintf(ts("Error deleting '%s'"), $column_name));
      $session->setStatus(print_r($result,TRUE));
    }
  }
  $custom_table_parameters = _sumfields_get_custom_table_parameters();
  $id = $custom_table_parameters['id'];
  $params = array('version' => 3, 'id' => $id);
  $result = civicrm_api('CustomGroup', 'delete', $params);
  if($result['is_error'] == 1) {
    $table_name = $custom_table_parameters['table_name'];
    $session->setStatus(sprintf(ts("Error deleting '%s'"), $table_name));
  }
}

/**
 * Remove our values from civicrm_setting table
 **/
function sumfields_delete_user_settings() {
  $sql = "DELETE FROM civicrm_setting WHERE group_name = 'Summary Fields'";
  CRM_Core_DAO::executeQuery($sql);
}

/**
 * Helper helper to get just the table name out of
 * table parameters
 *
 **/
function _sumfields_get_custom_table_name() {
  $table_info = _sumfields_get_custom_table_parameters();
  if(array_key_exists('table_name', $table_info)) {
    return $table_info['table_name'];
  }
  return NULL;
}

/**
 * Since CiviCRM may give our fields a different name on different
 * sites, we store the actual name and id that was used.
 *
 **/
function _sumfields_get_custom_field_parameters() {
  return sumfields_get_setting('custom_field_parameters', array());
}

/**
 * Since CiviCRM may give our table a different name on different
 * sites, we store the actual name and id that was used.
 *
 **/
function _sumfields_get_custom_table_parameters() {
  return sumfields_get_setting('custom_table_parameters', array());
}

/**
 * Return the $custom array with all the custom field
 * definitions.
 **/
function sumfields_get_custom_field_definitions() {
  static $custom = NULL;
  if(is_null($custom)) {
    // The custom.php file defines the $custom array of field
    // definitions. Only require if necessary.
    require 'custom.php';
  }
  // Not all custom field definitions will be relevant to this
  // installation, so we have filter out any fields that come
  // from tables that are not installed.
  while(list($k, $v) = each($custom['fields'])) {
    if($v['trigger_table'] == 'civicrm_contribution') {
      if(!sumfields_component_enabled('CiviContribute')) {
        unset($custom['fields'][$k]);
      }
    }
    elseif($v['trigger_table'] == 'civicrm_participant') {
      if(!sumfields_component_enabled('CiviEvent')) {
        unset($custom['fields'][$k]);
      }
    }
    // Some of the custom fields depend on installed components
    if($k == 'contribution_amount_last_membership_payment' || 
      $k == 'contribution_date_last_membership_payment') {
       if(!sumfields_component_enabled('CiviMember')) {
        unset($custom['fields'][$k]);
      }
    }
    if($k == 'event_turnout_attempts') {
      // event_turnout_attempts is triggered on the civicrm_participant table, 
      // but it counts records in the civicrm custom table civirm_participant_info_NN.
      // We have to look up the name of that table for this particular instance as a
      // way to see if the table is installed.
      $actual_table_name = sumfields_get_participant_info_table();
      if(!$actual_table_name) {
        // Perhaps not enabled.
        unset($custom['fields'][$k]);
      }
    }
  }
  reset($custom);
  return $custom;
}

/**
 * Helper function: get name of civicrm_value_participant_info table
 * for this installation or FALSE if it's not enabled. 
 **/
function sumfields_get_participant_info_table() {
  $sql = "SELECT table_name FROM civicrm_custom_group WHERE name = 'participant_info';";
  $dao = CRM_Core_DAO::executeQuery($sql);
  if($dao->N == 0) return FALSE;

  $dao->fetch();
  return $dao->table_name;
}

/**
 * Helper function: get column name for the given field 
 * for this installation or FALSE if it's not enabled. 
 **/
function sumfields_get_column_name($name) {
  $sql = "SELECT column_name FROM civicrm_custom_field WHERE name = %0 ".
    "OR column_name LIKE %1";
  $params = array(
    0 => array($name, 'String'),
    1 => array("${name}%", 'String')
  );
  $dao = CRM_Core_DAO::executeQuery($sql, $params);
  if($dao->N == 0) return FALSE;

  $dao->fetch();
  return $dao->column_name;
}
/**
 * Helper script - report if Component is enabled
 **/
function sumfields_component_enabled($component) {
  static $config;
  if(is_null($config)) {
    $config = CRM_Core_Config::singleton();
  }
  return in_array($component, $config->enableComponents);
}

/**

 * Initialize all user settings.
 *
 * The user has the option to choose which fields they want, which contribution
 * types to include, which event types, etc. 
 * 
 * When initializing (which happens with the extension is enabled), we
 * don't choose any fields. By not choosing any fields, we don't add any
 * SQL triggers, and the extension is enabled relatively quickly.  
 *
 * When the user selects the fields they want, they can choose whether to
 * have the change go through immediately (risks timing out) or via the next
 * cron job.
 *
 * To make it more user-friendly, we choose standard options for the other user
 * selected preferences (e.g. which event types should be included, etc.)
 *
 **/
function sumfields_initialize_user_settings() {
  $fields = array();
  sumfields_save_setting('active_fields', $fields);

  // Which financial_type_ids are used to calculate the general contribution
  // summary fields?
  $values = sumfields_get_all_financial_types();
  sumfields_save_setting('financial_type_ids', array_keys($values));

  // Which financial_type_ids are used to calculate the last membership
  // payment fields?
  sumfields_save_setting('membership_financial_type_ids', array_keys($values));

  // Which event type ids are used when calculating event fields?
  $values = sumfields_get_all_event_types();
  sumfields_save_setting('event_type_ids', array_keys($values));

  // Which participant status ids are used to calculate attendended events 
  $values = sumfields_get_all_participant_status_types();
  // When initializing, only use the attended.
  $initial_status_types = preg_grep('/Attended/', $values);
  sumfields_save_setting('participant_status_ids', array_keys($initial_status_types));

  // Which participant status ids are used to calculate no shows
  $values = sumfields_get_all_participant_status_types();
  // When initializing, only use 'No-show' if it exists, otherwise nothing
  // (note: no-show was added in 4.4) 
  $initial_noshow_status_types = preg_grep('/No-show/', $values);
  sumfields_save_setting('participant_noshow_status_ids', array_keys($initial_noshow_status_types));
}

/**
 * Get all contribution types
 **/
function sumfields_get_all_financial_types() {
  $values = array();
  CRM_Core_PseudoConstant::populate($values, 'CRM_Financial_DAO_FinancialType', $all = TRUE);
  return $values;
}

/**
 * Get all event types
 **/
function sumfields_get_all_event_types() {
  $values = CRM_Core_OptionGroup::values('event_type', FALSE, FALSE, FALSE, NULL, 'label', $onlyActive = FALSE);
  return $values;
}

/**
 * Get all participant status types.
 **/
function sumfields_get_all_participant_status_types() {
  $values = array();
  CRM_Core_PseudoConstant::populate($values, 'CRM_Event_DAO_ParticipantStatusType', $all = TRUE);
  return $values;
}
/**
 * Get all available active fields
 **/
function sumfields_get_all_custom_fields() {
  $custom = sumfields_get_custom_field_definitions();
  $values = array();
  while(list($k) = each($custom['fields'])) {
    $values[] = $k;
  }
  return $values;
}

/**
 * Helper function to clean up
 **/
function sumfields_deinitialize_custom_data() {
  sumfields_delete_custom_fields_and_table();
}

/**
 * Find incorrect total lifetime contributions.
 *
 * Diangostic tool for collecting records with an incorrect
 * total lifetime contribution value in the summary field.
 *
 */
function sumfields_find_incorrect_total_lifetime_contribution_records() {
  $ret = array();

  // We're only interested in one field for this test.
  $base_column_name = 'contribution_total_lifetime';

  // We need to ensure this field is enabled on this site.
  $active_fields = sumfields_get_setting('active_fields', array());
  if(!in_array($base_column_name, $active_fields)) {
    drush_log(dt("The total lifetime contribution is not active, this test will not work."), 'error');
    return FALSE;
  }

  // Get the name of the actual summary fields table.
  $table_name = _sumfields_get_custom_table_name();

  // Get the actual names of the field in question
  $custom_fields = _sumfields_get_custom_field_parameters();
  $column_name = $custom_fields[$base_column_name]['column_name'];

  // Load the field and group definitions because we need the trigger
  // clause that is stored here.
  $custom = sumfields_get_custom_field_definitions();

  // Get the base sql
  $config_trigger_sql = $custom['fields'][$base_column_name]['trigger_sql'];

  if(empty($table_name) || empty($column_name) || empty($config_trigger_sql)) {
    // Perhaps we are not properly enabled?
    drush_log(dt("Can't get table name or column name or trigger sql. Something is wrong."), 'error');
    return FALSE;
  }
  if($db_trigger_sql != $config_trigger_sql) {
    drush_log(dt("Mis-match between db_trigger_sql (@db) and config_trigger_sql (@config). Using config.", 
      array('@db' => $db_trigger_sql, '@config' => $config_trigger_sql)));
  }
  
  // Rewrite the sql with the appropriate variables filled in.
  if(FALSE === $trigger_sql = sumfields_sql_rewrite($config_trigger_sql)) {
    $msg = sprintf(ts("Failed to rewrite sql for %s field."), $base_column_name);
    drush_log($msg, 'error');
    return FALSE;
  }
  // Iterate over all contacts with a contribution
  $contact_sql = "SELECT DISTINCT(contact_id) FROM civicrm_contribution WHERE ".
    "is_test = 0";
  $dao = CRM_Core_DAO::executeQuery($contact_sql);

  $trigger_dao = new CRM_Core_DAO();
  while($dao->fetch()) {
    $sql = str_replace("NEW.contact_id", $dao->contact_id, $trigger_sql);
    $trigger_dao->query($sql);
    $trigger_result = $trigger_dao->getDatabaseResult();
    $row = $trigger_result->fetchRow();
    $trigger_total = empty($row[0]) ? '0.00' : $row[0];

    $table_sql = "SELECT `$column_name` AS table_total FROM `$table_name` WHERE entity_id = %0";
    $table_dao = CRM_Core_DAO::executeQuery($table_sql, array(0 => array($dao->contact_id, 'Integer')));
    $table_dao->fetch();
    $table_total = empty($table_dao->table_total) ? '0.00' : $table_dao->table_total;

    if($table_total != $trigger_total) {
      $sql = "SELECT MAX(receive_date) AS last FROM civicrm_contribution WHERE contact_id = %0";
      $last_dao = CRM_Core_DAO::executeQuery($sql, array(0 => array($dao->contact_id, 'Integer')));
      $last_dao->fetch();
      $last_contribution = $last_dao->last;
      $ret[$dao->contact_id] = "Contact id: $dao->contact_id, Summary Table total: " . $table_total . ", Current trigger total: $trigger_total, Last Contribution: $last_contribution";
    }
  }
  return $ret;
}

/**
 * Test for inconsistent summaries
 *
 * Returns 1 if the test is successful, 2 if inconsistencies are found
 * and 3 if there was an error running the test.
 *
 * FIXME: how can we convince drush to set error codes properly?
 **/
function sumfields_test_inconsistent_summaries() {
  // We're only interested in one field for this test.
  $base_column_name = 'contribution_total_lifetime';

  // We need to ensure this field is enabled on this site.
  $active_fields = sumfields_get_setting('active_fields', array());
  if(!in_array($base_column_name, $active_fields)) {
    echo "3\n"; 
    return FALSE;
  }

  if(!$db_trigger_sql = sumfields_get_update_trigger('civicrm_contribution')) { 
    // If no triger is defined, there's no way this will work. Bail early. Save CPU
    // cycles.
    echo "2\n";
    return FALSE;
  }

  $ids = sumfields_find_incorrect_total_lifetime_contribution_records();
  if($ids === FALSE) {
    echo "3\n";
    return FALSE;
  }
  if(count($ids) == 0) {
    echo "1\n";
    return TRUE;
  }
  echo "2\n";
  return TRUE;
}

/**
 * Print incorrect total lifetime contributions.
 *
 * Diangostic tool for testing to see whether there are any records with an
 * incorrect total lifetime contribution value in the summary field. It appears
 * as though the trigger does not always get set. This tools helps identify which
 * records are affected. It can be run via * drush's php-eval sub-command, e.g.
 *
 * drush php-eval "_civicrm_init(); sumfields_print_inconsistent_summaries()"
 *
 *
 */
function sumfields_print_inconsistent_summaries() {
  // We're only interested in one field for this test.
  $base_column_name = 'contribution_total_lifetime';

  // We need to ensure this field is enabled on this site.
  $active_fields = sumfields_get_setting('active_fields', array());
  if(!in_array($base_column_name, $active_fields)) {
    drush_log(dt("The total lifetime contribution is not active, this test will not work."), 'error');
    return FALSE;
  }

  if(!$db_trigger_sql = sumfields_get_update_trigger('civicrm_contribution')) { 
    // If no triger is defined, there's no way this will work. Bail early. Save CPU
    // cycles.
    drush_log("Contribution table trigger not defined. This won't work.", 'error');
    return FALSE;
  }

  $ids = sumfields_find_incorrect_total_lifetime_contribution_records();
  if($ids === FALSE) {
    drush_log("Failed to test for inconsistent data. Something went wrong.", 'error');
    return FALSE;
  }
  while(list($id, $data) = each($ids)) {
    drush_log($data, 'ok');
  }
}

/**
 * Get the update trigger statement as configured in the database.
 *
 **/
function sumfields_get_update_trigger($table = 'civicrm_contribution') {
  $sql = "SELECT ACTION_STATEMENT FROM information_schema.TRIGGERS WHERE
    TRIGGER_NAME = '${table}_after_update'";

  $dao = CRM_Core_DAO::executeQuery($sql);
  $dao->fetch();

  if($dao->N == 0) return FALSE;
  return $dao->ACTION_STATEMENT;
}

function testo() {
  drush_log('this is an error', 'error');
}

/**
 * Fix incorrect contributions summary fields.
 *
 * Find all summary fields with incorrect amounts and fix them. You may need to
 * add this as a cron job if you find that you are regularly getting
 * inconsistent summaries.
 *
 */
function sumfields_fix_inconsistent_summaries() {
  // This means there is no update triggered defined.
  if(!$db_trigger_sql = sumfields_get_update_trigger('civicrm_contribution')) { 
    $msg = dt("Update trigger is not defined. This might explain inconsistent responses. ".
      "Rebuilding triggers, this may take a while.");
    drush_log($msg, 'error');
    CRM_Core_DAO::triggerRebuild();
    if(!sumfields_get_update_trigger('civicrm_contribution')) { 
      $msg = dt("Still can't find trigger after rebuilding. Bailing...");
      drush_log($msg, 'error');
      return FALSE;
    }
  }
  $ids = sumfields_find_incorrect_total_lifetime_contribution_records();
  if($ids && count($ids) > 0) {
    // Just re-initiate everything - who knows what might have gone wrong.
    drush_log(dt("Data is wrong. Re-running trigger creation."), 'error'); 
    CRM_Core_DAO::triggerRebuild();
    drush_log(dt("Repopulating all data."), 'error'); 
    sumfields_generate_data_based_on_current_data($session = NULL);
    return TRUE;
  }
}

/**
 * Updates the custom table, ensuring all required fields are present
 * and no longer needed fields are removed
 *
 * @old_fields: the currently active fields
 * @new_fields: the desired fields 
 *
 **/
function sumfields_alter_table() {
  $old_fields = sumfields_get_setting('active_fields', array());
  $new_fields = sumfields_get_setting('new_active_fields', NULL);

  if(is_null($new_fields)) {
    // This is an error - we should never be called without new fields 
    // available;
    return FALSE;
  }

  $session = CRM_Core_Session::singleton();
  $custom_field_parameters = sumfields_get_setting('custom_field_parameters', NULL);

  // Set default return - optimistically.
  $ret = TRUE;
  // Delete fields no longer needed
  reset($old_fields);
  while(list(,$field) = each($old_fields)) {
    if(!in_array($field, $new_fields)) {
      $params['id'] = $custom_field_parameters[$field]['id'];
      // First see if it exists. If it doesn't exist, don't bother (this is to help
      // with error/recovery problems)
      try {
        $result = civicrm_api3('CustomField', 'get', $params);
        if($result['count'] > 0) {
          civicrm_api3('CustomField', 'delete', $params);
        }
      }
      catch (CiviCRM_API3_Exception $e) {
        $session->setStatus(sprintf(ts("Error deleting custom field '%s': %s"), $field, $e->getMessage()));
        // This will result in a error, but let's continue anyway to see if we can get the rest of the fields
        // in working order.
        $ret = FALSE;
        continue;
      }
      // $session->setStatus(sprintf(ts("Deleted custom field '%s'"), $field));
      unset($custom_field_parameters[$field]);
    }
  }

  // Add new fields
  $custom_table_parameters = sumfields_get_setting('custom_table_parameters', NULL);
  if(is_null($custom_table_parameters)) {
    $session->setStatus(ts("Failed to get the custom group parameters. Can't add new fields."));
    return FALSE;
  }
  $custom = sumfields_get_custom_field_definitions();
  $group_id = $custom_table_parameters['id'];
  reset($new_fields);
  while(list(,$field) = each($new_fields)) {
    if(!in_array($field, $old_fields)) {
      $params = $custom['fields'][$field];
      $params['custom_group_id'] = $group_id;
      try {
        $result = civicrm_api3('CustomField', 'get', $params);
        // Skip without error if it already exists.
        if($result['count'] == 0) {
          $params['triggerRebuild'] = FALSE;
          $result = civicrm_api3('CustomField', 'create', $params);
        }
      }
      catch (CiviCRM_API3_Exception $e) {
        $session->setStatus(sprintf(ts("Error adding custom field '%s': %s"), $field, $e->getMessage()));
        $ret = FALSE;
        continue;
      }
      // $session->setStatus(sprintf(ts("Added custom field '%s'"), $field));
      $value = array_pop($result['values']);
      $custom_field_parameters[$field] = array(
        'id' => $value['id'], 
        'column_name' => $value['column_name']
      );
    }
  }
  sumfields_save_setting('custom_field_parameters', $custom_field_parameters);
  if($ret == TRUE) {
    // This was successfully, make the new fields that active fields
    sumfields_save_setting('active_fields', $new_fields);
    sumfields_save_setting('new_active_fields', NULL);
  }
  return $ret;
}

/**
 * Helper/debug function: output all triggers with replacements.
 *
 **/
function sumfields_print_triggers() {
  // Get list of custom fields and triggers
  $custom = sumfields_get_custom_field_definitions();
  while(list($k, $v) = each($custom['fields'])) {
    $out = sumfields_sql_rewrite($v['trigger_sql']);
    if(FALSE === $out) {
      $out = "Failed sql_write.";
    }
    drush_print("Field: $k");
    drush_print($out);
  }
}

/**
 * Used for generating the schema and data 
 *
 * Should be called whenever the extension has the chosen
 * fields saved.
 *
 * Called by the API gendata.
 */
function sumfields_gen_data(&$returnValues) {
  // generate_schema_and_data variable can be set to any of the following:
  // 
  // NULL It has never been run, no need to run
  // scheduled:YYYY-MM-DD HH:MM:SS -> it should be run
  // running:YYYY-MM-DD HH:MM:SS -> it is currently running, and started at the given date/time
  // success:YYYY-MM-DD HH:MM:SS -> It completed successfully on the last run at the given date/time
  // failed:YYYY-MM-DD HH:MM:SS -> It failed on the last run at the given date/time
  //
  $status = $new_status = sumfields_get_setting('generate_schema_and_data', FALSE);
  $date = date('Y-m-d H:i:s');
  $exception = FALSE;
  if (preg_match('/^scheduled:/', $status)) {
    $new_status = 'running:' . $date;
    sumfields_save_setting('generate_schema_and_data', $new_status);

    // Check to see if the new_active_fields setting is set. This means we have to alter the fields
    // from the current setting.
    $new_active_fields = sumfields_get_setting('new_active_fields', NULL);
    if(!is_null($new_active_fields)) {
      if(!sumfields_alter_table()) {
        // If we fail to properly alter the table, bail and record that we had an error.
        $date = date('Y-m-d H:i:s');
        $new_status = 'failed:' . $date;
        $exception = TRUE;
      }
      else {
        // Set new active fields to NULL to indicate that they no longer
        // need to be updated
        sumfields_save_setting('new_active_fields', NULL);
      }
    }
    if(!$exception) {
      if(sumfields_generate_data_based_on_current_data()) {
        CRM_Core_DAO::triggerRebuild();
        $date = date('Y-m-d H:i:s');
        $new_status = 'success:' . $date;
      }
      else {
        $date = date('Y-m-d H:i:s');
        $new_status = 'fail:' . $date;
        $exception = TRUE;
      }
    }
  }
  $returnValues = array("Original Status: $status, New Status: $new_status");
  sumfields_save_setting('generate_schema_and_data', $new_status);
  if(!$exception) {
    return FALSE;
  }
  return TRUE;
}

/**
 * Help function: if we are multi-lingual, rewrite the
 * given query.
 **/
function sumfields_multilingual_rewrite($query) {
  global $dbLocale;
  if($dbLocale) {
    return CRM_Core_I18n_Schema::rewriteQuery($query);
  }
  return $query;
}
