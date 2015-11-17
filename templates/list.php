<div id="controls" style="height:63px;">
  <input type="hidden" name="permissions" value="" id="permissions">
</div>

<div id="emptycontent" class="hidden"></div>

<table id="filestable" class="panel">

  <thead class="panel-heading">
	<tr>
	  <th id='headerName' class="hidden column-name">
		<div id="headerName-container" class="row">
		  <div class="col-xs-4 col-sm-1">
			<input type="checkbox" id="select_all_files" class="select-all"/>
			<label for="select_all_files"></label>
		  </div>
		  <div class="col-xs-3">
			<a class="name sort columntitle" data-sort="name"><span class="text-semibold"><?php p($l->t( 'Name' )); ?></span><span class="sort-indicator"></span></a>
		  </div>
		  <div class="col-xs-5 col-sm-5 text-right">
			<span id="" class="selectedActions">
			  <a href="" class="download btn btn-xs btn-default">
				<i class="icon-download-cloud"></i>
				<?php p($l->t('Download'))?>
			  </a>
			  <a href="" class="delete-selected btn btn-xs btn-danger">
				<i class="icon-trash"></i>
				<?php p($l->t('Delete'))?>
			  </a>
			</span>
		  </div>
		</div>
	  </th>
	  <th id="headerSize" class="hidden column-size">
		<a class="size sort columntitle" data-sort="size"><span><?php p($l->t('Size')); ?></span><span class="sort-indicator"></span></a>
	  </th>
	  <th id="headerDate" class="hidden column-mtime">
		<a id="modified" class="columntitle" data-sort="mtime"><span><?php p($l->t( 'Modified' )); ?></span><span class="sort-indicator"></span></a>
	  </th>
	</tr>
  </thead>



  <tbody id="fileList">
  </tbody>
  <tfoot>
  </tfoot>
</table>

