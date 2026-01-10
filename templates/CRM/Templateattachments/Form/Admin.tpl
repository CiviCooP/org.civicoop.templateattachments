<div id="templateattachments_wrapper">
    <details class="crm-accordion-bold crm-html_email-accordion" open>
        <summary>
            {ts}Attachments{/ts}
        </summary>
        <div class="crm-accordion-body">
                <table>
                {if $form.attachFile_1}
                    <tr>
                        <td class="label">{$form.attachFile_1.label}</td>
                        <td>{$form.attachFile_1.html}&nbsp;{$form.attachDesc_1.html}<a href="#" class="crm-hover-button crm-clear-attachment" style="visibility: hidden;" title="{ts escape='htmlattribute'}Clear{/ts}"><span class="icon ui-icon-close"></span></a>
                            <div class="description">{ts}Browse to the <strong>file</strong> you want to upload.{/ts}{if $maxAttachments GT 1} {ts 1=$maxAttachments}You can have a maximum of %1 attachment(s).{/ts}{/if} {ts 1=$config->maxFileSize}Each file must be less than %1M in size. You can also add a short description.{/ts}</div>
                        </td>
                    </tr>
                    {section name=attachLoop start=2 loop=$numAttachments+1}
                        {assign var=index value=$smarty.section.attachLoop.index}
                        {assign var=attachName value="attachFile_"|cat:$index}
                        {assign var=attachDesc value="attachDesc_"|cat:$index}
                        {assign var=tagElement value="tag_"|cat:$index}
                        <tr class="attachment-fieldset solid-border-top"><td colspan="2"></td></tr>
                        <tr>
                            <td class="label">{$form.attachFile_1.label}</td>
                            <td>{$form.$attachName.html}&nbsp;{$form.$attachDesc.html}<a href="#" class="crm-hover-button crm-clear-attachment" style="visibility: hidden;" title="{ts escape='htmlattribute'}Clear{/ts}"><span class="icon ui-icon-close"></span></a></td>
                        </tr>
                    {/section}
                {/if}
                {if $currentAttachmentInfo}
                    <tr class="attachment-fieldset solid-border-top"><td colspan="2"></td></tr>
                    <tr>
                        <td class="label">{ts}Current Attachment(s){/ts}</td>
                        <td class="view-value">
                            {foreach from=$currentAttachmentInfo key=attKey item=attVal}
                                <div class="crm-attachment-wrapper crm-entity" id="file_{$attVal.fileID}">
                                    <strong><a class="crm-attachment" href="{$attVal.url}">{$attVal.cleanName}</a></strong>
                                    {if $attVal.description}&nbsp;-&nbsp;{$attVal.description}{/if}
                                    {if $attVal.deleteURLArgs}
                                        <a href="#" class="crm-hover-button delete-attachment" data-filename="{$attVal.cleanName}" data-args="{$attVal.deleteURLArgs}" title="{ts escape='htmlattribute'}Delete File{/ts}"><span class="icon delete-icon"></span></a>
                                    {/if}
                                    {if !empty($attVal.tag)}
                                        <br/>
                                        {ts}Tags{/ts}: {$attVal.tag}
                                        <br/>
                                    {/if}
                                </div>
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">&nbsp;</td>
                        <td>{$form.is_delete_attachment.html}&nbsp;{$form.is_delete_attachment.label}
                        </td>
                    </tr>
                {/if}
                </table>
        </div><!-- /.crm-accordion-body -->
    </details>
</div>

{literal}
<script type="text/javascript">
    CRM.$(function($) {
        $('#templateattachments_wrapper').insertBefore($('#message_templates #pdf_format'));

        var $form = $("form.CRM_Admin_Form_MessageTemplates");
        $form
        .on('click', '.crm-clear-attachment', function(e) {
            e.preventDefault();
            $(this).css('visibility', 'hidden').closest('td').find(':input').val('');
        })
        .on('change', '#attachments :input', function() {
            $(this).closest('td').find('.crm-clear-attachment').css('visibility', 'visible');
        });
    });
</script>
{/literal}

{if $currentAttachmentInfo}
    {include file="CRM/Form/attachmentjs.tpl"}
{/if}
