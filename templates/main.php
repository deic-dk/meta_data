
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
  <div id="newtag" class="panel-heading" style="border:solid 1px #e4e4e4; margin-bottom:20px; height:55px; display:none">
    <span>
	  <input class="edittag" type="text" placeholder="New tag name..."> 
	  <div class="color-box color-1 border"></div>
	  <div class="color-box color-2"></div>
	  <div class="color-box color-3"></div>
	  <div class="color-box color-4"></div>
	  <div class="color-box color-5"></div>
	  <div class="color-box color-6"></div>
	    <span style="margin-left:20px; margin-bottom:20px; position:absolute">	
		  <div id="ok" class="btn-group" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Ok</a>
          </div>
          <div id="cancel" class="btn-group" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Cancel</a>
          </div>
	    </span>
      </span>
  </div>
</div>

<table id="filestable" class="panel">
<thead class="panel-heading">
<tr>
  <th id="headerName" class="column-name">
    <div id="headerName-container" class="row">
      <div class="col-xs-4 col-sm-1"></div>
      <div class="col-xs-3 col-sm-3">	  
        <a class="name sort columntitle" data-sort="descr">
		  <span>Tag name</span>         
          <span class="sort-indicator hidden icon-triangle-n"></span>
	    </a>
      </div>
      <div class="col-xs-3 col-sm-3">	  
        <a class="color sort columntitle" data-sort="color">
		  <span class="text-semibold">Tag color</span>         
          <span class="sort-indicator icon-triangle-n"></span>
	    </a>
      </div>
    </div>
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

