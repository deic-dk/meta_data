

<button id="addTag"><?php print($l->t('Add new tag')); ?></button>
<button id="editTag"><?php print($l->t('Edit selected tag')); ?></button>
<button id="deleteTag"><?php print($l->t('Delete selected tag')); ?></button>


<div id="meta_data_content">
    <div id="tagscontainer">
        <div id="tagstree">
        </div>
    </div>

</table>
    <div class="center" id="fileTable">
        <div id="meta_data_fileList"></div>
<table id="filestable"><legend id="filestable_leg"></legend></table>        
<div id="meta_data_emptylist"><?php p($l->t('Select one or more tags to view the associated files.')) ?></div>
    </div>
</div>


<div id="createTag" title="<?php p($l->t('Create a new tag')) ?>">
    <form>
        <fieldset>
            <p class="validateTips"><?php p($l->t('Insert the new tag and confirm')) ?></p>
            <input type="text" name="newTagName" style="width: 300px;" id="newTagName" class="text ui-widget-content ui-corner-all" />
            <input type="checkbox", name="tagPublic" id="tagPublic" value=1> <?php p($l->t('make tag public')) ?>    
            <p class="validateTips"><?php p($l->t('Add keys to this tag (optional)')) ?></p>

<input type="text" class="form-control" id="newtokenfield" value="" placeholder="Enter key names"/>    

</fieldset>
    </form>
</div>

<div id="renameTag" title="<?php p($l->t('Rename tag')) ?>">
    <form>
        <fieldset>
            <p class="validateTips"><?php p($l->t('Rename the tag and confirm')) ?></p>
            <input type="text" name="tagName" style="width: 300px;" id="tagName" class="text ui-widget-content ui-corner-all" />
            <input type="hidden" name="tagID" id="tagID" value="" />
            <input type="checkbox", name="tagPublic" id="tagPublic" value=1> <?php p($l->t('make tag public')) ?>    

<input type="text" class="form-control" id="tokenfield" value="" placeholder="Enter key names"/>    
        </fieldset>
    </form>
</div>

<div id="deleteConfirm" title="<?php p($l->t('Delete tag')) ?>"> 
    <div>
        <?php p($l->t('Really delete the tag:')) ?><br />
        <div style="width: 100%; text-align: center; padding: 5px 0px 15px 0px; font-weight: bold;" id="tagToDelete"></div>
    </div>
    <input type="hidden" name="deleteID" id="deleteID" value="-1" />
</div>

