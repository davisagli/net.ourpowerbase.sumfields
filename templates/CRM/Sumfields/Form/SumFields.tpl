<h2>Summary Fields Administration</h2>

<table class="form-layout-compressed">
  <tr>
    <td colspan="2" class="description"><h3>{ts}Trigger Status{/ts}</h3></td>
  </tr>
  <tr>
    <td colspan="2" class="description">{ts}Contribution Table Updates:{/ts} {$contribution_table_trigger_status}</td>
  </tr>
  <tr>
    <td colspan="2" class="description">{ts}Participant Table Updates:{/ts} {$participant_table_trigger_status}</td>
  </tr>
  <tr>
    <td colspan="2" class="description"><h3>{ts}Settings{/ts}</h3></td>
  </tr>

  <tr>
    <td colspan="2" class="description">{ts}Please indicate which of the available summary fields you would like to enable.{/ts}</td>
  </tr>
  <tr class="crm-sumfields-form-block-sumfields_active_fields">
    <td class="label">{$form.active_fields.label}</td>
    <td>{$form.active_fields.html}</td>
  </tr> 
  {if $sumfields_contribute}
    <tr>
      <td colspan="2" class="description">{ts}Please indicate the financial types you would like included when calculating contribution related summary fields.{/ts}</td>
    </tr>
    <tr class="crm-sumfields-form-block-sumfields_financial_type_ids">
      <td class="label">{$form.financial_type_ids.label}</td>
      <td>{$form.financial_type_ids.html}</td>
    </tr> 
  {/if}
  {if $sumfields_event && $sumfields_member}
    <tr>
      <td colspan="2" class="description">{ts}Please indicate the financial types you would like included when calculating membership payment related summary fields.{/ts}</td>
    </tr>
    <tr class="crm-sumfields-form-block-sumfields_membership_financial_type_ids">
      <td class="label">{$form.membership_financial_type_ids.label}</td>
      <td>{$form.membership_financial_type_ids.html}</td>
    </tr> 
  {/if}
  {if $sumfields_event}
    <tr>
      <td colspan="2" class="description">{ts}Please indicate the event types you would like included when calculating event-related summary fields.{/ts}</td>
    </tr>
    <tr class="crm-sumfields-form-block-sumfields_event_type_ids">
      <td class="label">{$form.event_type_ids.label}</td>
      <td>{$form.event_type_ids.html}</td>
    </tr>
    <tr>
      <td colspan="2" class="description">{ts}Please indicate the participat status you would like included when calculating event-related summary fields.{/ts}</td>
    </tr>
    <tr class="crm-sumfields-form-block-sumfields_participant_status_ids">
      <td class="label">{$form.participant_status_ids.label}</td>
      <td>{$form.participant_status_ids.html}</td>
    </tr>
    <tr class="crm-sumfields-form-block-sumfields_participant_noshow_status_ids">
      <td class="label">{$form.participant_noshow_status_ids.label}</td>
      <td>{$form.participant_noshow_status_ids.html}</td>
    </tr>
  {/if}
</table>
 <div class="description">{ts}Please be patient - when saving these settings, the contents of the summary table is re-created from scratch. This procedure may take a few minutes.{/ts}</div>
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>

