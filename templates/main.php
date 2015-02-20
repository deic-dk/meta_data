
<div id="app-content" style="transition: all 0.3s ease 0s;">
<div id="app-content-meta_data" class="viewcontainer">
<div id="controls">
  <div class="row">
    <div class="col-sm-12 text-right">
      <div class="actions creatable">
        <div id="upload" original-title="">
		  <a class="btn btn-primary btn-flat" href="#"><i class="icon-tag"></i>
             New tag
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<table id="filestable" class="panel">
<thead class="panel-heading">
<tr>
  <th id="headerName" class="column-name">
    <div id="headerName-container" class="row">
      <div class="col-xs-4 col-sm-1"></div>
      <div class="col-xs-3 col-sm-6">	  
        <a class="name sort columntitle" data-sort="name">
		  <span class="text-semibold">Tag name</span>         
          <span class="sort-indicator icon-triangle-n"></span>
	    </a>
      </div>
    </div>
  </th>
  <th id="headerDisplay" class="column-display">
    <a class="display sort columntitle" data-sort="display">
      <span>Visible in menu</span>
      <span class="sort-indicator hidden icon-triangle-s"></span>
    </a>
  </th>
  <th id="headerSize" class="column-size">
    <a class="size sort columntitle" data-sort="size">
      <span>Tagged files</span>
      <span class="sort-indicator hidden icon-triangle-s"></span>
    </a>
  </th>
</tr>
</thead>

<tbody id="fileList">
</tbody>
<tfoot>
</tfoot>





</table>

</div>
</div>














<div class="hidden" id="deleteConfirm" title="<?php p($l->t('Delete tag')) ?>"> 
    <div>
        <span id="deleteType"></span>
        <?php p($l->t('Are you sure you want to delete the tag:')) ?><br />
        <div style="width: 100%; text-align: center; padding: 5px 0px 15px 0px; font-weight: bold;" id="tagToDelete"></div>
        <?php p($l->t('This operation cannot be undone.')) ?><br />
    </div>
    <input type="hidden" name="deleteID" id="deleteID" value="-1" />
</div>

