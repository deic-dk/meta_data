/*
 * Copyright (c) 2015, written by Christian Brinch, DeIC.
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * THIS FILE contains the script for the meta data main view
 *
 */

function updateTagsView(sortValue, direction){
  $('tbody#fileList').html('');
  $('tfoot').html('');

  // sortValue = typeof sortValue !== 'undefined' ? sortValue : 'color';
  //direction = typeof direction !== 'undefined' ? direction : 'asc';

	var total=0;
	$.ajax({
	url: OC.filePath('meta_data', 'ajax', 'getTags.php'),
	data: {sortValue: sortValue, direction: direction, fileCount: true, display: true},
	success: function(response) {
		if(response) {
		$.each(response['tags'], function(key, value) {
			total+=value.size;
			$('tbody#fileList').append(' \
					<tr data-id="'+value.id+'" data-name="'+value.name+'" data-color="'+value.color+'">\
					<td class="filename row meta_data_row">\
						<span class="taginfo">\
							<a class="action-meta_data" href="#" style="text-decoration:none">\
								<span class="label outline label-'+colorTranslate(value.color)+'" data-tag="'+value.id+'">\
								<i class="icon-tag" style="display: inline;"></i>\
									<span class="tagtext">'+value.name+'</span>\
								</span>\
							</a>\
						</span>\
					</td>\
					<td class="color">\
						<span class="editstuff">\
							<div class="color-box color-1"></div>\
							<div class="color-box color-2"></div>\
							<div class="color-box color-3"></div>\
							<div class="color-box color-4"></div>\
							<div class="color-box color-5"></div>\
							<div class="color-box color-6"></div>\
						</span>\
					</td>\
					<td class="display"><input type="checkbox" name="display"> </td>\
					<td class="public"><input type="checkbox" name="public"> </td>\
					<td class="taggedfiles"><a href="/index.php/apps/files/?dir=%2F&view=tag-'+value.id+'" style="text-decoration:none">'+value.size+'</a></td>\
					<td><a class="action action-delete icon icon-trash-empty" style="color:#c5c5c5;font-size:16px;background-image:none" data-action="Delete" href="#"></a>\
					</td>\
				</tr>\
				');
			$('tbody#fileList tr[data-id='+value.id+'] div.'+value.color).addClass('border');
			if(value.display==1){
				$('tbody#fileList tr[data-id='+value.id+'] td.display input').prop('checked', true);
			}
			else {
				$('tbody#fileList tr[data-id='+value.id+'] td.display input').prop('checked', false);
			}
			if(value.public==1){
				$('tbody#fileList tr[data-id='+value.id+'] td.public input').prop('checked', true);
				if(value.owner!=$('head').attr('data-user')){
					$('tbody#fileList tr[data-id='+value.id+']').attr('data-tag-owner', value.owner);
					$('tbody#fileList tr[data-id='+value.id+'] td.public input').prop('disabled', true);
					$('tbody#fileList tr[data-id='+value.id+'] td.color .editstuff .color-box').css('cursor', 'default');
					$('tbody#fileList tr[data-id='+value.id+']').css('background-color', '#fffffb');
					$('tbody#fileList tr[data-id='+value.id+'] td a.action').remove();
				}
			}
			else {
				$('tbody#fileList tr[data-id='+value.id+'] td.public input').prop('checked', false);
			}
		});
		}
		$('tbody#fileList tr td.color').hover(
			function(){
				if($(this).parents('tr').attr('data-tag-owner')){
					return false;
				}
				$(this).find('.color-box:not(.border)').css('display', 'inline-block');
			},
			function(){
				$(this).find('.color-box:not(.border)').hide();
			}
		);
		if(response){
			var ntags = response['tags'].length;
		}
		else{
			var ntags = 0;
		}
		$('tfoot').append('\
			<tr class="summary text-sm">\
			<td><span class="info">'+ntags+' tags</span></td>\
			<td></td>\
			<td></td>\
			<td class="filesize">'+total+' files</td>\
			</tr>\
			');
		}
	});
}

function resetInput(){
	$('div#controls div.color-box').removeClass('border');
	$('div#controls div.color-1').addClass('border');
	$('div#controls input.edittag').val('');
	$('div#newtag').toggle();
}

function setColor(){

	var tagid  = $(this).parents('tr').attr('data-id');
	
	if($(this).parents('tr').attr('data-tag-owner')){
		return false;
	}

	var oldColor = $('tr[data-id='+tagid+'] td .editstuff .border').attr('class');
	if(typeof oldColor == 'undefined'){
		oldColor = '';
	}
	oldColor = oldColor.replace('color-box','').replace('border','').replace(' ','');

	var newColor = $(this).attr('class');
	newColor = newColor.replace('color-box','').replace('border','').replace(' ','');

	if(oldColor==newColor){
		return false;
	}

	$(this).siblings('div').removeClass('border');
	$(this).addClass('border');

	$('tr[data-id='+tagid+'] td span.taginfo a span.label').removeClass(function(index, css){
		return (css.match (/(^|\s)label-\S+/g) || []).join(' ');
	}).addClass('label-'+colorTranslate(newColor));

	$('ul.nav-sidebar li[data-id="tag-'+tagid+'"] a i').removeClass(function (index, css) {
		return (css.match (/(^|\s)tag-\S+/g) || []).join(' ');
	}).addClass('tag-'+colorTranslate(newColor));
	
	$(this).parents('tr').attr('data-color', newColor);

	$.ajax({
		url: OC.filePath('meta_data', 'ajax', 'updateTag.php'),
				 data: {id: tagid, color: newColor},
	});

};

function setName(tagid, newname){
	if(!newname){
		return false;
	}
	$.ajax({
		url: OC.filePath('meta_data', 'ajax', 'updateTag.php'),
		data: {id: tagid, tagname: newname},
		success: function(result){
			$('tr[data-id='+tagid+'] td span.taginfo span').html('<i class="icon-tag" style="display: inline;"></i><span class="tagtext">'+newname+'</span>');
			$('ul.nav-sidebar li[data-id="tag-'+tagid+'"] a').html('<i class="icon icon-tag" style="display: inline;"></i><span>'+newname+'</span>');

		}
	});
}

function toggleBorder(){
	$(this).siblings('div').removeClass('border');
	$(this).addClass('border');
}

function addTag(){
	if($('div#controls input.edittag').val() != ''){
		var color = $('div#controls div.border').attr('class');
		color = color.replace('color-box','').replace('border','').replace(' ','')
		$.ajax({
			url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
										type: "post",
										data: {tagOp:'new',tagVisibleState:'1', tagName:$('div#controls input.edittag').val(), tagColor: color, tagPublicState: '0'},
		});
		updateTagsView();
		updateSidebar();
	}
	resetInput();
}

$(document).ready(function() {
  updateTagsView();

  /*
   * Bind click to 'New tag' button and to 'Ok' and 'Cancel' buttons within
   */
  $('div#controls div#addtag a').on('click', function(){
		$('div#newtag').toggle();
		if($('div#newtag').is(":visible")){
			$('div#newtag .newtag-edit .edittag').focus();
		}
  });

  $('div#controls .newtag-clear').on('click', resetInput);

	$('div#controls .newtag-add').on('click', function() {
		addTag();
	});

	$('div#controls input.edittag').keyup(function(e){
		if(e.keyCode == 13) {
			addTag();
		}
	});

  $("div#controls").on('click', '.color-box', toggleBorder);

  /*
   * Flip sort switches
   */

	$('thead a').on('click', function() {
		$(this).siblings('a').find('span.text-semibold').removeClass('text-semibold');
		$(this).siblings('a').find('span.sort-indicator').addClass('hidden');
		$(this).parent('div').siblings('div').find('span.text-semibold').removeClass('text-semibold');
		$(this).parent('div').siblings('div').find('span.sort-indicator').addClass('hidden');
		$(this).parents('th').siblings('th').find('span.text-semibold').removeClass('text-semibold');
		$(this).parents('th').siblings('th').find('span.sort-indicator').addClass('hidden');
		$(this).children('span').addClass('text-semibold');
		$(this).children('span.sort-indicator').removeClass('hidden');
		if($(this).children('span.sort-indicator').hasClass('icon-triangle-s')){
			var direction = 'desc';
		} else {
			var direction = 'asc';
		}
		updateTagsView($(this).attr('data-sort'), direction);
	});

	$('thead span.icon-triangle-s, thead span.icon-triangle-n').on('click', function() {
		$(this).toggleClass("icon-triangle-s icon-triangle-n");
	});

  /*
   * Bind click to 'Toggle visibility'
   */
	$("tbody#fileList").on('change', 'tr td.display input', function() {
		var tagid = $(this).parents('tr').attr('data-id');
		if($(this).is(":checked")){
			$.ajax({
				url: OC.filePath('meta_data', 'ajax', 'updateTag.php'),
				data: {id: tagid, visible: 1},
			});
			//updateSidebar();
			var tagname = $(this).parents('tr').attr('data-name');
			var tagcolor = $(this).parents('tr').attr('data-color');
			addTagToSidebar(tagid, tagname, tagcolor);
		}
		else{
			$('ul.nav-sidebar li[data-id="tag-'+tagid+'"]').remove();
			$.ajax({
				url: OC.filePath('meta_data', 'ajax', 'updateTag.php'),
				data: {id: tagid, visible: 0},
			});
		}
	});
	
	/*
	 * Bind click to 'Toggle privacy state'
	 */
	$("tbody#fileList").on('change', 'tr td.public input', function() {
		var tagid = $(this).parents('tr').attr('data-id');
		if($(this).is(":checked")){
			$.ajax({
				url: OC.filePath('meta_data', 'ajax', 'updateTag.php'),
				data: {id: tagid, public: 1},
			});
		}
		else{
			$.ajax({
				url: OC.filePath('meta_data', 'ajax', 'updateTag.php'),
				data: {id: tagid, public: 0},
			});
		}
	});

  $('tbody#fileList').on('click', 'tr a.action-delete', function(){
		var tagid = $(this).parents('tr').attr('data-id');
		$.ajax({
			url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
			type: "post",
			data: {tagOp:'delete', tagId:tagid},
		});
		updateTagsView();
		updateSidebar();
	});

  /*
   * Bind click to Edit meta data button
   */
	$("tbody#fileList").on('click', 'tr td.filename a.action-meta_data', function(){
		if($('.ui-dialog').length>0){
			return false;
		}
		var tagname = $(this).parents('.filename').find('.tagtext').html();
		var tagid = $(this).parents('tr').attr('data-id');
		var owner = $(this).parents('tr').attr('data-tag-owner');

		var html = $('<div><span id="tagid" data-id="'+tagid+'" data-name="'+tagname+'"><h3 class="tagname">Tag name: <input class="edittag" type="text" value="'+tagname+'" /></h3></span><h3>Meta-data fields:</h3><a class="oc-dialog-close close svg"></a><div id="meta_data_container">\
			<div id=\"emptysearch\">No metadata defined</div><ul id="meta_data_keys"></ul></div><div class="new_field"><button id="add_key" class="btn btn-flat btn-default">Add meta-data field</button></div>\
			<div class="schema_editor_buttons"><button id="popup_ok" class="btn btn-flat btn-primary">OK</button>&nbsp;<button id="popup_cancel" class="btn btn-flat btn-default" style="margin-right:15px;">Cancel</button></div></div>');

		$(html).dialog({
			dialogClass: "oc-dialog notitle",
			resizable: false,
			//draggable: true,
			//height: window.height,
			width: "80%"
		});
		
		var readonly = false;
		if(typeof owner != 'undefined' && owner!=$('head').attr('data-user')){
			$('#meta_data_container :input').prop('readonly', true);
			$('.tagname :input').prop('readonly', true);
			$('.schema_editor_buttons').hide();
			$('.new_field').hide();
			readonly = true;
		}

		$.ajax({
			url: OC.filePath('meta_data', 'ajax', 'loadKeys.php'),
			async: false,
			data: {
				tagid: tagid,
				readonly: readonly
			},
			type: "POST",
			success: function(result) {
				if(result['data']){
					$('body').find('#emptysearch').toggleClass('hidden');
					$.each(result['data'], function(i,item){
						$('body').find('#meta_data_keys').append(newEntry(item, readonly));
					});
				}
			}
		});
	});

	$('body').on('click', '#add_key', function(){
		if($('#meta_data_keys li:last-child input').val()==''){
			$('#meta_data_keys li:last-child input').addClass('highlight');
			setTimeout(
				function() {
					$('#meta_data_keys li:last-child input').removeClass('highlight');
				}, 1500
			);
			$('#meta_data_keys li:last-child input').focus();
			return false;
		}
		$('body').find('#emptysearch').hide();
		$('body').find('#meta_data_keys').append(newEntry());
		$('body').find('#meta_data_keys li:last-child input').focus();
	});

	$('body').on('focusout', 'div.oc-dialog div.ui-dialog-content div#meta_data_container ul#meta_data_keys li input.edit', function(){
		if(!$(this).parent('li').hasClass('new')){
			$(this).parent('li').addClass('alt');
		}
	});

	$('body').on('keypress', 'div.oc-dialog div.ui-dialog-content div#meta_data_container ul#meta_data_keys li input.edit', function (e) {
		var key = e.which;
		if(key == 13){
			$('body').find('#add_key').focus();
			return false;
		}
	});

	$('body').on('click', 'div.oc-dialog div.ui-dialog-content div#meta_data_container ul#meta_data_keys li span.deletekey', function(){
		if($(this).parent('li').siblings().size() === 0) $('body').find('#emptysearch').show();
		if(!$(this).parent('li').hasClass('new')){
			$(this).parent('li').addClass('del');
			$(this).parent('li').hide();
		}
		else {
			$(this).parent('li').remove();
		}
	});

  $('body').on('click', '#popup_ok', function(){
		var tagid = $(this).parent().parent().find('#tagid').attr('data-id');
		var tagname = $(this).parent().parent().find('#tagid').attr('data-name');
		$(this).parent('div').siblings('div#meta_data_container').children('ul').children('li').each(function(){
			if($(this).children('input.edit').val() != '' && $(this).hasClass('new')){
				$.ajax({
					url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
					type: "POST",
					data: {
						tagOp: 'new_key',
						keyName: $(this).children('input.edit').val(),
						tagId: tagid
					},
					success: function(result) {
					},
				});
			}
			else if($(this).children('input.edit').val() != '' && $(this).hasClass('del')){
				$.ajax({
					url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
					type: "POST",
					data: {
						tagOp: 'delete_key',
						keyId: $(this).attr('id'),
						tagId: tagid
					},
					success: function(result) {
					},
				});
			}
			else if($(this).children('input.edit').val() != '' && $(this).hasClass('alt')){
				$.ajax({
					url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
					type: "POST",
					data: {
						tagOp: 'rename_key',
						keyId:  $(this).attr('id'),
						tagId:  tagid,
						newName:$(this).children('input.edit').val()
					},
					success: function(result) {
					},
				});
			}
		});
		var newtagname = $(this).parent('div').parent('div').find('input.edittag').val();
		if(newtagname!=tagname){
			setName(tagid, newtagname);
		}
		$('body').find('.ui-dialog').remove();
  });


  $('body').on('click', '.editstuff .color-box', setColor);

});
