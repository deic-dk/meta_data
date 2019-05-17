/*
 * Copyright (c) 2015, written by Christian Brinch, DeIC.
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * THIS FILE contains the main functions for the meta data app. Functions
 * are documented below.
 *
 */

// For comparing two arrays - from http://stackoverflow.com/questions/7837456/how-to-compare-arrays-in-javascript
// Warn if overriding existing method
if(Array.prototype.equals)
    console.warn("Overriding existing Array.prototype.equals. Possible causes: New API defines the method, there's a framework conflict or you've got double inclusions in your code.");
// attach the .equals method to Array's prototype to call it on any array
Array.prototype.equals = function (array) {
    // if the other array is a falsy value, return
    if (!array)
        return false;

    // compare lengths - can save a lot of time 
    if (this.length != array.length)
        return false;

    for (var i = 0, l=this.length; i < l; i++) {
        // Check if we have nested arrays
        if (this[i] instanceof Array && array[i] instanceof Array) {
            // recurse into the nested arrays
            if (!this[i].equals(array[i]))
                return false;
        }
        else if (typeof this[i].id!=='undefined' && typeof array[i].id!=='undefined') {
          if(this[i].id != array[i].id){
            return false;
          }
        }
        else if (this[i] != array[i]) {
            // Warning - two different object instances will never be equal: {x:20} != {x:20}
            return false;   
        }
    }
    return true;
}

// Hide method from for-in loops
Object.defineProperty(Array.prototype, "equals", {enumerable: false});

if (!OCA.Meta_data){
  OCA.Meta_data = {};
}

OCA.Meta_data.App = {

  _tagid: null,
  _FileList: null,
  initTaggedFiles: function($el, tagid) {
		if(this._FileList && this._tagid==tagid) {
			return this._FileList;
		}
		this._tagid = tagid;
		this._FileList = new OCA.Meta_data.FileList(
			$el,
			{
				scrollContainer: $('#app-content'),
				fileActions: this._createFileActions(),
				allowLegacyActions: true,
				tagid: tagid
			}
		);
		this._FileList.$el.find('#emptycontent').text(t('Meta_data', 'No files found'));
		return this._FileList;
  },

  removeTaggedFiles: function() {
		if (this._FileList) {
			this._FileList.$fileList.empty();
		}
  },

  destroy: function() {
		this.removeTaggedFiles();
		this._FileList = null;
		delete this._globalActionsInitialized;
  },

  tagMultipleDropdown: function(event){
  	event.stopPropagation();
		if(scanFiles.scanning) {
			return false;// Workaround to prevent additional http request block scanning feedback
		}
		if(getGetParam('view')=='trashbin'){
			return false;
		}
  	if($('#dropdown').length>0){
			$("#dropdown").slideUp(200, function(){ $(this).remove();});
			$('tr').removeClass('mouseOver');
			$('a#tag.tag').removeClass('mouseOver');
		return false;
  	}
  	var files = FileList.getSelectedFiles();
  	var fileIds = [];
  	var dirIds = [];
  	var type = 'file';
  	for( var i=0;i<files.length;++i){
  		if(fileIds.indexOf(files[i].id)==-1){
    		if(files[i].type=='dir'){
    			dirIds.push(files[i].id);
    			}
    			else{
        		fileIds.push(files[i].id);
    			}
  		}
  	}
  	if(dirIds.length>0){
  		type = 'folder';
    	// TODO: Consider allowing tagging directories and directory content recursively
  		OC.dialogs.alert('You cannot tag directories', 'Tagging not possible', function(res){}, true);
  		return false;
  	}
		var html = '\
			<div id="dropdown" class="drop" data-item-type="'+type+'" data-item-source="'+fileIds.join(':')+'">\
			<div id="tag_action"></div>\
		</div>\
		';
		$('a#tag.tag').parent().append(html);
		$('a#tag.tag').addClass('mouseOver');
  	if(dirIds.length==1){
  		addNewDropDown(dirIds[0]);
  	}
  	else{
  		addNewDropDown(fileIds.join(':'));
  	}
  	return false;
  },

  _createFileActions: function() {
		var fileActions = new OCA.Files.FileActions();
		fileActions.registerDefaultActions();
		fileActions.register('file', 'Tags', OC.PERMISSION_UPDATE, OC.imagePath('meta_data', 'tag.png'), function(filename,context){
			// Action to perform when clicked
			if(scanFiles.scanning) { return; } // Workaround to prevent additional http request block scanning feedback
			if($('#dropdown').length == 0){
				var tr = context.$file;
				var itemType = 'file';
				var itemSource = $(tr).data('id');
				var html = '\
								<div id="dropdown" class="drop" data-item-type="'+itemType+'" data-item-source="'+itemSource+'">\
								<div id="tag_action"></div>\
							</div>\
							';
				$(html).appendTo($(tr).find('td.filename') );
				$(tr).addClass('mouseOver');
				addNewDropDown(itemSource);
			}
			else{
				$("#dropdown").slideUp(200, function(){ $(this).remove();});
				$('tr').removeClass('mouseOver');
			}
		});
		fileActions.merge(OCA.Files.fileActions);
		return fileActions;
  },

	newCreateRow: function(fileData, tr) {
		// Group folder support - Not necessary - group is now set directly in fileData, by list.php
		/*fileData.group =  '';
		if(fileData.path){
			var firstSlashIndex = fileData.path.indexOf('/');
			if(firstSlashIndex === fileData.path.indexOf('/user_group_admin/')){
				var secondSlashIndex =  fileData.path.indexOf('/', firstSlashIndex+1);
				var thirdSlashIndex =  fileData.path.indexOf('/', secondSlashIndex+1);
				fileData.group =  thirdSlashIndex>0?fileData.path.substring(secondSlashIndex+1, thirdSlashIndex-1):
					fileData.path.substring(secondSlashIndex+1);
			}
		}*/
		if(fileData.type == 'file'){
			var tagwidth = 0;
			var overflow = 0;
			tr.find('div.filelink-wrap').after('<div class="filetags-wrap col-xs-4"></div>');
			if(typeof fileData.tags !== 'undefined' && fileData.tags.length > 0){
				$.each(fileData.tags, function(key,value) {
					var color = colorTranslate(value.color);
					if(tagwidth + value.name.length <= 20){
						tr.find('div.filetags-wrap').append('\
						<span data-tag=\''+value.id+'\' class=\'label outline label-'+color+'\'>\
						<span class="deletetag" style="display:none">\
						<i class=\'icon-cancel-circled\'></i>\
						</span>\
						<i class=\'icon-tag\'></i>\
						<span class=\'tagtext\'>'+value.name+'</span>\
						</span>\
						');
					}
					else{
						overflow += 1;
					}
					tagwidth += value.name.length;
				});
			}
			if(overflow > 0){
				tr.find('div.filetags-wrap').append('<span class=\'label outline label-default more-tags\' title="Show more tags"><span class="tagtext">+'+overflow+' more</span></span>');
			}
			tr.find('div.filelink-wrap').removeClass('col-xs-8').addClass('col-xs-4');
			var width = tr.find('div.filelink-wrap').width();
			var filename = tr.find('span.innernametext').html();
			tr.find('span.innernametext').html(start_and_end( filename, tr));
	}
	tr.find('.more-tags').tipsy({gravity:'s',fade:true});
	// Support sharding
	if(typeof fileData.owner != 'undefined'){
		tr.find('.filename .name').prepend('<span class="fileicon extraicon"><i class="icon-share invert-image">&nbsp;</i></span>');
		tr.attr('data-share-owner', fileData.owner);
		tr.attr('data-share-permissions', '0');
		tr.find('td.filename a').click({
			owner: fileData.owner,
			id: fileData.id,
			group: fileData.group
		}, function (event) {
			event.stopPropagation();
			event.preventDefault();
			(OCA.Files.FileList.prototype.serveFiles(event.data.dir, event.data.file, event.data.owner, event.data.id, fileData.group));
	});
	}
	return tr;
	},

	getParam: function(href, key) {
    var results = new RegExp('[\?&]' + key + '=([^&#]*)').exec(href);
    if (results==null){
       return '';//null;
    }
    else{
       return results[1] || 0;
    }
},

  modifyFilelist: function(_filelist) {
  	var filelist;
  	if(typeof _filelist === 'undefined'){
  		filelist = OCA.Files.FileList;
  	}
  	else{
  		filelist = _filelist;
  	}
		OCA.Meta_data.App.tag_semaphore = true;
		OCA.Meta_data.App.previous_tag_fileids = [];
		var oldnextPage = filelist.prototype._nextPage;
		filelist.prototype._nextPage = function(animate) {
			if(getGetParam('view')=='trashbin'){
				return  oldnextPage.apply(this,arguments);
			}
			var getfiletags = function(data, dir, dirowner, fileowners, callback) {
				OCA.Meta_data.App.tag_semaphore = false;
				$.ajax({
					async: false,
					type: "POST",
					url: OC.linkTo('meta_data','ajax/getFileTags.php'),
					data: {
						files: data,
						dir: dir,
						owner: dirowner,
						fileowners: fileowners
					},
					success: function(data){
						callback(data);
					}
				});
			}
			var files;
			var fileids = this.files.map(function(obj){ return {id: obj.id};});
			var owner = $('.crumb.last a').length>0?OCA.Meta_data.App.getParam($('.crumb.last a').attr('href'), 'owner'):'';
			var fileowners = '';
			if(typeof owner=='undefined' || owner==''){
				fileowners = this.files.map(function(obj){ return {owner: typeof obj.shareOwnerUID=='undefined'?'':obj.shareOwnerUID};});
			}
			if(OCA.Meta_data.App.tag_semaphore && !OCA.Meta_data.App.previous_tag_fileids.equals(fileids)){
				getfiletags(fileids, this.getCurrentDirectory(), typeof owner != 'undefined' ? owner : '', fileowners, function(data){
					files = data.files;
					OCA.Meta_data.App.tag_semaphore = true;
					OCA.Meta_data.App.previous_tag_fileids = fileids;
				});
				for(var i=0; i<this.files.length; i++){
					var id = this.files[i]['id'];
					var entry = $.grep(files, function(e){ return e.id==id});
					if(entry.length>0 && typeof entry[0].tags!=='undefined') {
						this.files[i]['tags'] = entry[0].tags;
					}
					else {
						this.files[i]['tags'] = {};
					}
				}
			}
			return  oldnextPage.apply(this,arguments);
		}

		var oldCreateRow = filelist.prototype._createRow;

		filelist.prototype._createRow = function(fileData) {
			var tr = oldCreateRow.apply(this, arguments);
			return OCA.Meta_data.App.newCreateRow(fileData, tr);
		}
  }
};

/*
 * This function translates color code into color name
 */
function colorTranslate(color){
	if(typeof color == 'undefined' || color == null)  return "default";
  if(color.indexOf('color-1') > -1)  return "default";
  if(color.indexOf('color-2') > -1)  return "primary";
  if(color.indexOf('color-3') > -1)  return "success";
  if(color.indexOf('color-4') > -1)  return "info";
  if(color.indexOf('color-5') > -1)  return "warning";
  if(color.indexOf('color-6') > -1)  return "danger";
  return "default";
}

/*
 * This function shortens the file name to make room for the tags
 */
function start_and_end(str, tr) {
	if($('#body-user:visible').length && tr.find('.filetags-wrap:visible').length && str.length > 24 ){
		return str.substr(0, 10) + '...' + str.substr(str.length-8, str.length);
	}
	else {
		return str;
	}
}

/*
 * This function updates the tags in the left hand side menu
 */
function updateSidebar(){
  $('.nav-sidebar li[data-id^=tag-]').remove();
  $('ul.nav-sidebar li#tags').hide();
  $.ajax({
	url: OC.filePath('meta_data', 'ajax', 'getUserDisplayTags.php'),
	success: function(response){
	  if(response){
			var tags = '';
			$.each( response['tags'], function(key, value) {
				$('ul.nav-sidebar li#tags').show();
				tags = tags+'\
				<li data-id="tag-'+value.id+'">\
				<a href="#"><i class="icon icon-tag tag-'+colorTranslate(value.color)+'" data-tag="'+value.id+'"></i>\
				<span>'+value.name+'</span>\
				</a>\
				</li>\
				';
			});
			$('ul.nav-sidebar li#tags').after(tags);
			if($('ul.nav-sidebar li#tags span i.icon-angle-right').is(':visible')){
				$('ul.nav-sidebar li[data-id^="tag-"]').hide();
			}
	  }
	}
  });
}

function addTagToSidebar(id, name, color){
	$('ul.nav-sidebar li#tags').show();
	tag = '\
	<li data-id="tag-'+id+'">\
	<a href="#"><i class="icon icon-tag tag-'+colorTranslate(color)+'" data-tag="'+id+'"></i>\
	<span>'+name+'</span>\
	</a>\
	</li>\
	';
	$('ul.nav-sidebar li#tags').after(tag);
}

/*
 * This function updates the tags on a single file
 */
function updateFileListTags(tr, showall, width){
	if(typeof width==='undefined'){
		width = 20;
	}
	if(tr.find('.filetags-wrap').length !=0){
		tr.find('.filetags-wrap').empty();
	}
	else{
		var tag = $('<div></div>').addClass('col-xs-4').addClass('filetags-wrap');
		tr.find('div.filelink-wrap').after(tag);
		tr.find('div.filelink-wrap').removeClass('col-xs-8').addClass('col-xs-4');
	}
	var tags = '';
	var owner = tr.attr('data-share-owner');
	$.ajax({
		async: false,
		url: OC.filePath('meta_data', 'ajax', 'single.php'),
		data: {
			fileid: tr.attr('data-id'),
			owner: typeof owner != 'undefined'?owner:''
			},
		success: function( response ){
			if(response){
					var tagwidth = 0;
					var overflow = 0;
					if(showall){
						tr.find('div.filetags-wrap').append('<a class="less-tags action" href="#" title="Show fewer tags"><i class="icon icon-resize-small"></i></a>').tipsy({gravity:'s',fade:true});
					}
					$.each(response['tags'], function(key,value) {
						var color = colorTranslate(value.color);
						if(tagwidth + value.name.length <= width){
							tr.find('div.filetags-wrap').append('\
								<span data-tag=\''+value.id+'\' class=\'label outline label-'+color+'\'>\
									<span class="deletetag" style="display:none">\
										<i class=\'icon-cancel-circled\'></i>\
									</span>\
									<i class=\'icon-tag\'></i>\
									<span class=\'tagtext\'>'+value.name+'</span>\
								</span>\
							');
						}
						else{
							if(showall) {
								tr.find('div.filetags-wrap').append('<br class="tags-space"/>\
									<span data-tag=\''+value.id+'\' class=\'label outline label-'+color+'\'>\
										<span class="deletetag" style="display:none">\
											<i class=\'icon-cancel-circled\'></i>\
										</span>\
										<i class=\'icon-tag\'></i>\
										<span style="padding-bottom:10px;" class=\'tagtext\'>'+value.name+'</span>\
									</span>\
								');
								width=42;
								tagwidth=0;
							}
							else{
								overflow += 1;
							}
						}
						tagwidth += value.name.length;
					});
					if(overflow > 0){
						tr.find('div.filetags-wrap').append('<span class=\'label outline label-default more-tags\' title="Show more tags"><span class="tagtext" >+'+overflow+' more</span></span>');
					}
			}
		}
	});
	tr.find('.more-tags').tipsy({gravity:'s',fade:true});
	return tr;
}

function removeTag(fileIds, tagid){
	$.ajax({
		async: false,
		url: OC.filePath('meta_data', 'ajax', 'removeFileTag.php'),
		data: {
			fileid: fileIds.join(':'),
			tagid:  tagid
		},
		success: function(response){
		}
	});
	for( var i=0;i<fileIds.length;++i){
		if($('tr[data-id="'+fileIds[i]+'"] span.more-tags').length !== 0 ){
			updateFileListTags($('tr[data-id="'+fileIds[i]+'"]'));
		}
		else{
			$('tr[data-id="'+fileIds[i]+'"]').find('span[data-tag="'+tagid+'"]').remove();
		}
	}
}

function loadKeys(fileid, tagid, owner){
	$.ajax({
		url: OC.filePath('meta_data', 'ajax', 'loadKeys.php'),
		async: false,
		data: {
		tagid: tagid
		},
		type: "POST",
		success: function(result) {
			if(result['data']){
				$('body').find('#emptysearch').toggleClass('hidden');
				$.each(result['data'], function(i,item){
					$('body').find('#meta_data_keys').append(newEntry(item));
				});
				$('body').find('#meta_data_keys').children('li').children().toggleClass('hidden');
			}
		}
	});
}
function loadValues(fileid, file, tagid, owner, callback){
	$.ajax({
		url:OC.filePath('meta_data', 'ajax', 'loadValues.php'),
		async: false,
		data: {
			fileid: fileid,
			tagid: tagid,
			fileowner: owner
		},
		type: "POST",
		success: function(result){
			if(result['data']){
				$.each(result['data'], function(i,item){
					$('body').find('#meta_data_keys').children('li[id="'+item['keyid']+'"]').children('.value').val(item['value']);
				});
				if(typeof callback!='undefined'){
					callback(fileid, file);
				}
			}
		}
	});
}

function showMetaPopup(fileid, tagid, file, title, callback){
	var html = $('\
			<div>\
			<span>\
			<h3 class="oc-dialog-title"><span  id="metadata" tagid="'+tagid+'" fileid="'+fileid+'">'+title+'</span></h3>\
		</span>\
		<a class="oc-dialog-close close svg"></a>\
		<div id="meta_data_container">\
						<div id=\"emptysearch\">No meta data defined</div>\
			<ul id="meta_data_keys"></ul>\
		</div>\
		</div>');
	$(html).dialog({
		dialogClass: "oc-dialog notitle",
		resizable: true,
		overflow: scroll,
		draggable: true,
		maxHeight:0.9*$(window).height(),
		minHeight: 240,
		/*create: function() {
		  $(this).css("maxHeight", 0.9*$(window).height()); 
		},*/
		position:{my: 'top',at: 'top+'+0.1*$(window).height()},
		width: "80%",
		buttons: [
		{"id": "ok-"+fileid+"-"+tagid, "text": "OK", "class": "popup_ok btn btn-flat btn-primary",
			 "click": function(){saveMeta(); if(typeof callback!= 'undefined'){callback(fileid, file);}}},
			{"id": "cancel-"+fileid+"-"+tagid, "text": "Cancel", "class": "popup_cancel btn btn-flat btn-default",
				 "click": function() {$('body').find('.ui-dialog').remove();}}]
	});
	var fileids = (''+fileid).split(':');
	var owner = $('tr[data-id='+fileids[0]+']').attr('data-share-owner-uid');
	if(typeof owner=='undefined' || owner==''){
		owner = '';
	}
	return owner;
}

$(this).click(function(event) {
	if ($('.row #dropdown').has(event.target).length===0 && $('#dropdown').hasClass('drop')) {
		$('.row #dropdown').hide('blind', function() {
			$('.row #dropdown').remove();
			$('tr').removeClass('mouseOver');
		});
	}
});

function newEntry(entry, readonly, newkey){
	entry = typeof entry !== 'undefined' ? entry : null;
	optionSelect = '<select title="'+t('meta_data', 'Non-string types')+'" class="type"><option selected value="">'+t('meta_data', 'Type')+'</option>\
		<option value="controlled">'+t('meta_data', 'Controlled')+'</option><option value="json">JSON</option></select>\
	<input placeholder="value1, value2, ..." title="Comma separated list of allowed values" class="controlled_values" type="text" value="" />'
	if(!entry){
		var ret = $('\
				<li class="new">\
					<span class="keyname hidden"></span>\
					<input class="edit" type="text" placeholder="'+t('meta_data', 'New key name')+'" value="" />'+
					'<input class="value hidden" type="text" value="" />'+
					optionSelect+
					(readonly?'':'<span class="deletekey">&#10006;</span>')+
				'</li>');
	}
	else {
		var valueInputField;
		if(entry['allowed_values']){
			valueInputField = '<select class="value hidden">';
			valueInputField+= '<option value="" disabled></option>';
			var allowedValues = JSON.parse(entry['allowed_values']);
			valueInputField+= '<option value=""></option>';
			for(var i=0; i<allowedValues.length; ++i){
				valueInputField+= '<option value="'+allowedValues[i]+'">'+allowedValues[i]+'</option>';
			}
			valueInputField+= '</select>';
		}
		else{
			valueInputField = '<input class="value hidden" type="text" value="" />';
		}
		ret = $('\
				<li '+(newkey?'class="new"':'id="'+entry['id'])+'">\
					<span class="keyname hidden">'+entry['name']+'</span>\
					<input class="edit" type="text" value="'+entry['name']+'"'+(readonly?' readonly':'')+' />'+
					(readonly?'':optionSelect+'<span class="deletekey">&#10006;</span>')+
						valueInputField+
				'</li>');
		}
		if(entry && entry['allowed_values']){
			ret.find('select.type').val('controlled');
			ret.find('.controlled_values').val(JSON.parse(entry['allowed_values']).join(', '));
		}
		else{
			if(entry && entry['type']){
				ret.find('select.type').val( entry['type']);
			}
			ret.find('.controlled_values').hide();
		}

		ret.find('select.type').tipsy({gravity:'s',fade:true});
		ret.find('select.type').change(function(ev){
			$(this).parent('li').addClass('alt');
			if($(this).val()=='controlled'){
				$(this).parent().find('.controlled_values').show();
			}
			else{
				$(this).parent().find('.controlled_values').hide();
			}
		});
		return ret;
	}

$(this).click(function(event) {
	if ($('.row .dropdown-menu').has(event.target).length===0) {
		$('.row .dropdown-menu').hide('blind', function() {
			$('.row .dropdown-menu').hide();
			$('tr').removeClass('mouseOver');
		});
	}
});

function getParam(href, key) {
  var results = new RegExp('[\?&]' + key + '=([^&#]*)').exec(href);
  if (results==null){
     return '';// null;
  }
  else{
     return results[1] || 0;
  }
}

function getGetParam(key) {
  return this.getParam(window.location.href, key);
}

function editMeta(title, file, fileid, tagid, preCallback, callback){
	var selectedFiles = FileList.getSelectedFiles();
	var fileIds = [parseInt(fileid)];
	for( var i=0;i<selectedFiles.length;++i){
		if(fileIds.indexOf(selectedFiles[i].id)==-1){
			fileIds.push(selectedFiles[i].id);
		}
	}
	if(selectedFiles.length>1 || selectedFiles.length===1 && selectedFiles[0].id!=fileid){
		OC.dialogs.confirm('Are you sure you want to enter meta-data for multiple files (existing meta-data will be overwritten)?', 'Confirm overwrite',
        function(res){
  				if(res){
  					$(this).hide();
  					showMetaPopup(fileIds.join(':'), tagid, fileIds.length + ' files', title, callback);
  		  		loadKeys(fileid, tagid, owner);
  				}
        }
     );
	}
	else{
		var owner = showMetaPopup(fileid, tagid, file, title, callback);
		loadKeys(fileid, tagid, owner);
		loadValues(fileid, file, tagid, owner, preCallback);
		// Disable editing meta-data of files shared with me
		if(owner!=''){
  		$('#meta_data_keys input').prop('readonly', true);
  		$('.ui-dialog-buttonpane').hide();
		}
	}
}

/*
 * This block of code is for leaving the meta data editor
 */
function saveMeta(){
	$('body').find('#meta_data_keys li').each(function() {
		//if($(this).children('.value').val() != '' ){
		$.ajax(
			{
				url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
				type: "POST",
				data: {
				tagOp: 'update_file_key',
				keyId: $(this).attr('id'),
				tagId: $('#metadata').attr('tagid'),
				fileId:$('#metadata').attr('fileid'),
				type:$('#metadata').attr('type'),
				value: $(this).find('.value').val()
			},
			success: function(result) {
			}
		});
		//}
	});
	$('body').find('.ui-dialog').remove();
}

$(document).ready(function() {
	/* Always update menu on reload */
	updateSidebar();

  /* Switch to tag view when tag is clicked */
	$('ul.nav-sidebar').on('click', 'li[data-id^="tag-"]', function(e) {
		$('ul.nav-sidebar').find('.active').removeClass('active');
		$(this).children('a').addClass('active');
		if($('#app-content-'+$(this).attr('data-id')).length !== 0){
			$('#app-navigation ul li[data-id="'+$(this).attr('data-id').replace( /(:|\.|\[|\]|,|=)/g, "\\$1" )+'"] a').click();
		}
		else{
			window.location.href = "/index.php/apps/files?dir=%2F&view=" + $(this).attr('data-id');
		}
	});

	$('[id^=app-content-tag]').on('show', function(e) {
		var tagid = e.target.getAttribute('id').split('-');
		OCA.Meta_data.App.initTaggedFiles($(e.target), tagid[3]);
	});

	$('[id^=app-content-tag]').on('hide', function() {
		OCA.Meta_data.App.removeTaggedFiles();
	});

	if(typeof OCA.Meta_data.App.tag_semaphore == 'undefined' && OCA.Files &&
			OCA.Files.FileList && getGetParam('view')!='trashbin'){
		OCA.Meta_data.App.modifyFilelist();
	}

  /*
   * This next block of code is for deleting tags from a file
   */
  $('#body-user tbody').on('mouseenter', 'tr td div.row div.filetags-wrap span[class^=label]', function(){
  	if($('.nav-sidebar li[data-id="sharing_in"] a.active').length){
  		return false;
  	}
		$(this).children('i').hide();
		$(this).children('span.deletetag').show();
  }).on('mouseleave', 'tr td div.row div.filetags-wrap span[class^=label]', function(){
		$(this).children('i').show();
		$(this).children('span.deletetag').hide();
  });

  $('#body-user tbody').on('click', 'span.deletetag', function(e){
		e.stopPropagation();
		var tagid = $(this).parent('span').attr('data-tag');
		var fileid = $(this).parent('span').parent('div').parent('div').parent('td').parent('tr').attr('data-id');
  	var selectedFiles = FileList.getSelectedFiles();
  	var fileIds = [parseInt(fileid)];
  	for( var i=0;i<selectedFiles.length;++i){
  		if(fileIds.indexOf(selectedFiles[i].id)==-1){
    		fileIds.push(selectedFiles[i].id);
  		}
  	}
  	if(selectedFiles.length>1 || selectedFiles.length===1 && selectedFiles[0].id!=fileid){
  		OC.dialogs.confirm('Are you sure you want to delete a tag from multiple files?', 'Confirm deletion',
          function(res){
	  				if(res){
	  					removeTag(fileIds, tagid);
	  				}
          }
       );
  	}
  	else{
  		removeTag(fileIds, tagid);
  	}
  });

  /*
   * This next block of code is for entering the meta-data editor
   */
	$('#body-user tbody').on('click', '.filetags-wrap span.label:not(.more-tags)', function(e){
		if($('.ui-dialog').length>0){
			return false;
		}
		e.preventDefault();
		e.stopPropagation();
		var title= $(this).children('span.tagtext').html();
		var file = $(this).parents('tr').attr('data-file');
		var fileid= $(this).parents('tr').attr('data-id');
		var tagid= $(this).attr('data-tag');
		editMeta(title, file, fileid, tagid);
	});

	$('body').on('keypress', 'div.oc-dialog div.ui-dialog-content div#meta_data_container ul#meta_data_keys li .value', function (e) {
		var key = e.which;
		if(key == 13)  {
			$('body').find('.popup_ok').focus();
			return false;
		}
  });

  /*
   * Show all tags when click on '+n more'
   */
  $('tbody').on('click', 'span.more-tags', function(e){
		e.stopPropagation();
		$('.tipsy').last().remove();
		updateFileListTags($(this).parents('tr'), true);
  });

	$('tbody').on('click', 'a.less-tags', function(e){
		e.stopPropagation();
		$('.tipsy').last().remove();
		updateFileListTags($(this).parents('tr'), false);
	});

  /* Additional search result types */
  OC.search.resultTypes.tag = "Tag" ;
  OC.search.resultTypes.metadata = "Metadata" ;
  
	// Add action to top bar (visible when files are selected)
	if(!$('.nav-sidebar li[data-id="sharing_in"] a.active').length &&
			!$('.nav-sidebar li[data-id="trash"] a.active').length && getGetParam('view')!='trashbin'){
		$('#headerName .selectedActions').prepend(
				'<a class="tag btn btn-xs btn-default" id="tag" href=""><i class="icon icon-tag"></i>'+t('meta_data',' Tag')+'</a>&nbsp;');
		$('#headerName .selectedActions .tag').click(OCA.Meta_data.App.tagMultipleDropdown);
	}
});
