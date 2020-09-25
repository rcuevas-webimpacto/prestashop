<div class="m-b-1 m-t-1">
    <h2>{l s='Custom Attribute from module' mod='product_text'}</h2>
        <fieldset class="form-group">
            <div class="col-lg-12 col-xl-12">        
                <label class="form-control-label">{l s='my custom lang field wysiwyg' mod='product_text'}</label>
                <div class="translations tabbable">
                    <div class="translationsFields tab-content bordered">
                        {foreach from=$languages item=language }
                            <div class="tab-pane translation-label-{$language.iso_code} {if $default_language == $language.id_lang}active{/if}">
                               <textarea name="custom_field_lang_wysiwyg_{$language.id_lang}" class="autoload_rte">{if isset({$custom_field_lang_wysiwyg[$language.id_lang]}) && {$custom_field_lang_wysiwyg[$language.id_lang]} != ''}{$custom_field_lang_wysiwyg[$language.id_lang]}{/if}</textarea>    
                            </div>    
                        {/foreach}    
                    </div>
                </div>
            </div>
            
        </fieldset>
    
        <div class="clearfix"></div>
</div>
