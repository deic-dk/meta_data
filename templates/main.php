

<div id="meta_data_main">
<div id="searchbar">
<input id="searchinput" type="text" name="" placeholder="Search meta data [e.g., tag:mp3 or key:artist=dylan]"> </div>
<table id="meta_data_table">
<thead>
<tr>
<td id="tag_head">Tags</td>
<td id="files_head">Files</td>
<td id="data_head">Meta data</td>
</tr>
</thead>
<tbody>
<tr>
<td><div id="tag_col"  class="scrollable"></div><div class="meta_data_add addtag">+</div></td>
<td><div id="files_col" class="scrollable2"></div><div class="meta_data_add addfile hidden">+</div></td>
<td><div class="scrollable"><div id="fileInfo" class="hidden"></div><div id="data_col"></div></div><div class="meta_data_add adddata hidden">+</div></td>
</tr>
</tbody>
</table>
</div>

<ul id="tc_colors">
  <li><i class="icon-tag tc_white"></i></li>
  <li><i class="icon-tag tc_gray"></i></li>
  <li><i class="icon-tag tc_red"></i></li>
  <li><i class="icon-tag tc_yellow"></i></li>
  <li><i class="icon-tag tc_blue"></i></li>
  <li><i class="icon-tag tc_green"></i></li>
  <li><i class="icon-tag tc_purple"></i></li>
</ul>

<div id="deleteConfirm" title="<?php p($l->t('Delete tag')) ?>"> 
    <div>
        <span id="deleteType"></span>
        <?php p($l->t('Are you sure you want to delete the tag:')) ?><br />
        <div style="width: 100%; text-align: center; padding: 5px 0px 15px 0px; font-weight: bold;" id="tagToDelete"></div>
        <?php p($l->t('This operation cannot be undone.')) ?><br />
    </div>
    <input type="hidden" name="deleteID" id="deleteID" value="-1" />
</div>

