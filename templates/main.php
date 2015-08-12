
<div id="app-content" style="transition: all 0.3s ease 0s;">
<div id="app-content-meta_data" class="viewcontainer">
<div id="controls">
  <div class="row">
    <div class="col-sm-12 text-right">
      <div class="actions creatable">
        <div id="addtag" original-title="">
          <a class="btn btn-primary btn-flat" href="#"><i class="icon-tag"></i>
             New tag
          </a>
        </div>
      </div>
    </div>
  </div>
  <div id="newtag" class="panel-heading">
    <span class="newtag-edit">
	  <input class="edittag" type="text" placeholder="New tag"> 
	  <div class="color-box color-1 border"></div>
	  <div class="color-box color-2"></div>
	  <div class="color-box color-3"></div>
	  <div class="color-box color-4"></div>
	  <div class="color-box color-5"></div>
	  <div class="color-box color-6"></div>
	    <span class="newtag-buttons">	
					<a class="newtag-add btn btn-default btn-flat" href="#">Add</a>&nbsp;
					<a class="newtag-clear btn btn-default btn-flat" href="#">Cancel</a>
	    </span>
      </span>
  </div>
</div>

<table id="filestable" class="panel">
<thead class="panel-heading">
<tr>
	<th id="headerName" class="column-name">
		<a class="name sort columntitle" data-sort="descr">
			<span>Name</span>         
			<span class="sort-indicator hidden icon-triangle-n"></span>
		</a>
	</th>
	<th id="headerColor" class="column-color">
		<a class="color sort columntitle" data-sort="color">
			<span>Color</span>         
			<span class="sort-indicator hidden icon-triangle-n"></span>
		</a>
	</th>
	<th id="headerDisplay" class="column-display">
		<a class="display sort columntitle" data-sort="public">
			<span>Visible in menu</span>
			<span class="sort-indicator hidden icon-triangle-n"></span>
		</a>
	</th>
	<th id="headerSize" class="column-size">
		<a class="size sort columntitle" data-sort="size">
			<span>Tagged files</span>
			<span class="sort-indicator hidden icon-triangle-n"></span>
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

