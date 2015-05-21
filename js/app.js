if (!OCA.Meta_data){
  OCA.Meta_data = {};
}

OCA.Meta_data.App = {

  _dummy: null,
  _FileList: null,

  initTaggedFiles: function($el, tagid) {
	if (this._FileList && this._dummy==tagid) {
	  return this._FileList;
	}
	this._dummy = tagid;
	
	this._FileList = new OCA.Meta_data.FileList(
		$el,
		{
		  scrollContainer: $('#app-content'),
		  //linksOnly: true,
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

  _createFileActions: function() {
	var fileActions = new OCA.Files.FileActions();
	fileActions.registerDefaultActions();

	fileActions.register('file', 'Tags', OC.PERMISSION_UPDATE, OC.imagePath('meta_data', 'tag.png'), function(filename,context) {
	  // Action to perform when clicked
	  if(scanFiles.scanning) { return; } // Workaround to prevent additional http request block scanning feedback
	  if($('#dropdown').length == 0){      
		var tr = context.$file;
		var itemType = 'file';                                                             
		var itemSource = $(tr).data('id');
		var html = '<div id="dropdown" class="drop" data-item-type="'+itemType+'" data-item-source="'+itemSource+'"><div id="test"></div></div>';
		$(html).appendTo( $(tr).find('td.filename') );  
		$(tr).addClass('mouseOver');
		addNewDropDown(itemSource);
	  } else {
		$("#dropdown").remove();
		$('tr').removeClass('mouseOver');
	  }
	});
	fileActions.merge(OCA.Files.fileActions);

	return fileActions;
  },

  modifyFilelist: function() {
	var oldCreateRow = OCA.Files.FileList.prototype._createRow;
	OCA.Files.FileList.prototype._createRow = function(fileData) {
	  var tr = oldCreateRow.apply(this, arguments);
	  
	  if(fileData.type == 'file'){
		var tagwidth = 0;
		var overflow = 0;
		tr.find('div.filelink-wrap').after('<div class="filetags-wrap col-xs-4"></div>');
		$.each(fileData.tags, function(key,value) {
		  var color = colorTranslate(value.color);
		  if(tagwidth + value.descr.length <= 20){
			tr.find('div.filetags-wrap').append('<span data-tag=\''+value.tagid+'\' class=\'label outline '+color+'\'><span class="deletetag" style="display:none"><i class=\'icon-cancel-circled\'></i></span><i class=\'icon-tag\'></i><span class=\'tagtext\'>'+value.descr+'</span></span>' );
		  } else {
			overflow += 1;
		  }
		  tagwidth += value.descr.length;
		});
		if(overflow > 0){
		  tr.find('div.filetags-wrap').append('<span class=\'label outline label-default more\'>+'+overflow+' more</span>');
		}


		tr.find('div.filelink-wrap').removeClass('col-xs-8').addClass('col-xs-4');
		var width = tr.find('div.filelink-wrap').width();
		var filename = tr.find('span.innernametext').html();
		tr.find('span.innernametext').html(start_and_end( filename, tr.find('div.filelink-wrap')));										



	  }
	  return tr;
	}
  },

  loadTags: function() {
	OCA.Files.FileList.prototype.reload = function() {
	  this._selectedFiles = {};
	  this._selectionSummary.clear();
	  this.$el.find('.select-all').prop('checked', false);
	  this.showMask();
	  if (this._reloadCall) {
		this._reloadCall.abort();
	  }
	  this._reloadCall = $.ajax({
		url: OC.linkTo('meta_data','ajax/list_mod.php'),
		data: {
		  dir : this.getCurrentDirectory(),
		  sort: this._sort,
		  sortdirection: this._sortDirection
		}
	  });
	  var callBack = this.reloadCallback.bind(this);
	  return this._reloadCall.then(callBack, callBack);

	}
  }
};


function colorTranslate(color){
  if(color.indexOf('color-1') > -1)  return "label-default";
  if(color.indexOf('color-2') > -1)  return "label-primary";
  if(color.indexOf('color-3') > -1)  return "label-success";
  if(color.indexOf('color-4') > -1)  return "label-info";
  if(color.indexOf('color-5') > -1)  return "label-warning";
  if(color.indexOf('color-6') > -1)  return "label-danger";
  return "label-default";
}

function colorTranslateTag(color){
  if(color.indexOf('color-1') > -1)  return "tag-default";
  if(color.indexOf('color-2') > -1)  return "tag-primary";
  if(color.indexOf('color-3') > -1)  return "tag-success";
  if(color.indexOf('color-4') > -1)  return "tag-info";
  if(color.indexOf('color-5') > -1)  return "tag-warning";
  if(color.indexOf('color-6') > -1)  return "tag-danger";
  return "label-default";
}

function start_and_end(str, element) {
  if(str.length > 24 ){
	return str.substr(0, 10) + '...' + str.substr(str.length-8, str.length);
  } else {		
	return str;
  }
}


function updateSidebar(){
  $('.nav-sidebar li[data-id^=tag-]').remove();
  $('ul.nav-sidebar li#tags').hide();
  $.ajax({
	url: OC.filePath('meta_data', 'ajax', 'temp.php'),
	success: function(response)	{
	  if(response){
		var tags = '';
		$.each( response['tags'], function(key,value) {
		  if(value.public==1){
			$('ul.nav-sidebar li#tags').show();
			tags = tags+'<li data-id="tag-'+value.tagid+'"><a href="#"><i class="icon icon-tag '+colorTranslateTag(value.color)+'" data-tag="'+value.tagid+'"></i><span>'+value.descr+'</span></a></li>';
		  }
		});
		$('ul.nav-sidebar li#tags').after(tags);
		if($('ul.nav-sidebar li#tags span i.icon-angle-right').is(':visible')){
		  $('ul.nav-sidebar li[data-id^="tag-"]').hide();
		}
	  }
	}
  });


}

function updateFileListTags(tr, showall){
  var width = 20;
  if(tr.find('.filetags-wrap').length !=0){
	tr.find('.filetags-wrap').empty();
  } else {
	var tag = $('<div></div>').addClass('col-xs-4').addClass('filetags-wrap');
	tr.find('div.filelink-wrap').after(tag);
	tr.find('div.filelink-wrap').removeClass('col-xs-8').addClass('col-xs-4');
  }
  var tags = '';
  $.ajax({
	async: false,
	url: OC.filePath('meta_data', 'ajax', 'single.php'),
	data: {fileid: tr.attr('data-id')},
	success: function( response )
	{
	  if(response){

		var tagwidth = 0;
		var overflow = 0;
		$.each(response['tags'], function(key,value) {
		  var color = colorTranslate(value.color);

		  if(tagwidth + value.descr.length <= width){
			tr.find('div.filetags-wrap').append('<span data-tag=\''+value.tagid+'\' class=\'label outline '+color+'\'><span class="deletetag" style="display:none"><i class=\'icon-cancel-circled\'></i></span><i class=\'icon-tag\'></i><span class=\'tagtext\'>'+value.descr+'</span></span>' );
		  } else {
			if(showall) { 
			  tr.find('div.filetags-wrap').append('<br>');
			  tr.find('div.filetags-wrap').append('<span style="line-height:1.5;" data-tag=\''+value.tagid+'\' class=\'label outline '+color+'\'><span class="deletetag" style="display:none"><i class=\'icon-cancel-circled\'></i></span><i class=\'icon-tag\'></i><span style="padding-bottom:10px;" class=\'tagtext\'>'+value.descr+'</span></span>' );
			  width=42;
			  tagwidth=0;
			} else overflow += 1;
		  }
		  tagwidth += value.descr.length;
		});
		if(overflow > 0){
		  tr.find('div.filetags-wrap').append('<span class=\'label outline label-default more\'>+'+overflow+' more</span>');
		}
	  }
	}
  });

  return tr;
}


$(document).ready(function() {
  updateSidebar();

  $('ul.nav-sidebar').on('click', 'li[data-id^="tag-"]', function(e) {
	$('ul.nav-sidebar').find('.active').removeClass('active');	
	$(this).children('a').addClass('active');
	if($('#app-navigation').length !== 0){
	  $('#app-navigation ul li[data-id="'+$(this).attr('data-id')+'"] a').click();
	} else {
	  window.location.href = "/index.php/apps/files?dir=%2F&view=" + $(this).attr('data-id');;
	}
  });

  $('[id^=app-content-tag]').on('show', function(e) {
	var tagid = e.target.getAttribute('id').split('-');
	OCA.Meta_data.App.initTaggedFiles($(e.target), tagid[3]);
	//	OCA.Meta_data.App.modifyFilelist();
  });

  $('[id^=app-content-tag]').on('hide', function() {
	OCA.Meta_data.App.removeTaggedFiles();
  });

  if (OCA.Files) {
	OCA.Meta_data.App.loadTags();
	OCA.Meta_data.App.modifyFilelist();
  }

  /*
   *
   * This next block of code is for deleting tags from a file
   *
   */ 

  $('tbody').on('mouseenter', 'tr td div.row div.filetags-wrap span[class^=label]', function(){
	$(this).children('i').hide();
	$(this).children('span.deletetag').show();
  }).on('mouseleave', 'tr td div.row div.filetags-wrap span[class^=label]', function(){
	$(this).children('i').show();
	$(this).children('span.deletetag').hide();
  });

  $('tbody').on('click', 'span.deletetag', function(e){
	e.stopPropagation();
	var tagid = $(this).parent('span').attr('data-tag');
	var fileid= $(this).parent('span').parent('div').parent('div').parent('td').parent('tr').attr('data-id');
	$.ajax({
	  async: false,
	  url: OC.filePath('meta_data', 'ajax', 'removefiletag.php'),
	  data: {
		fileid: fileid, 
		tagid:  tagid	
	  },
	  success: function( response )
	  {
	  }

	});
	if($(this).parent('span').siblings('span[data-tag="more"]').length !== 0 ){
	  updateFileListTags($(this).parents('tr'))
	} else { 
	  $('tr[data-id="'+fileid+'"]').find('span[data-tag="'+tagid+'"]').remove();
	}

  });				

  /*
   *
   * This next block of code is for entering the meta data editor
   *
   */ 

  $('tbody').on('click', 'span.label:not(.more)', function(e){
	e.stopPropagation();
	var title=$(this).children('span.tagtext').html();
	var file =$(this).parents('tr').attr('data-file');
	var fileid=$(this).parents('tr').attr('data-id');
	var tagid=$(this).attr('data-tag');


	var html = $('<div><span id="tagid" class="'+tagid+'"><h3 class="oc-dialog-title">Meta data editor for the file: <span id="fileid" class="'+fileid+'">'+file+'</span>, tagged with: '+title+'</h3></span><a class="oc-dialog-close close svg"></a><div id="meta_data_container">\
		<div id=\"emptysearch\">No meta data defined</div><ul id="meta_data_keys"></ul></div>\
		<div style="position:absolute;bottom:0;left:0;" class="oc-dialog-buttonrow onebutton"><button id="popup_ok" class="btn-primary">OK</button><button id="popup_cancel" class="btn-default" style="margin-right:15px;">Cancel</button></div></div>');


	$(html).dialog({
	  dialogClass: "oc-dialog notitle",
	  resizeable: false,
	  draggable: false,
	  height: 800,
	  width: 1024
	});

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
	$.ajax({
	  url:OC.filePath('meta_data', 'ajax', 'loadValues.php'),
	  async: false,
	  data: {
		fileid: fileid,
		tagid: tagid
	  },
	  type: "POST",
	  success: function(result){
		if(result['data']){
		  $.each(result['data'], function(i,item){	  
			$('body').find('#meta_data_keys').children('li[id="'+item['keyid']+'"]').children('input.value').val(item['value']);
		  });
		}
	  }
	});


  });

  $('body').on('click', '#popup_ok', function(){
	$('body').find('#meta_data_keys li').each(function() {
	  if($(this).children('input.value').val() != '' ){
		$.ajax({                                                                                                                                                                                                                    
		  url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),                                                                                                                                                                      
		  type: "POST",                                                                                                                                                                                                             
		  data: {                                                                                                                                                                                                                   
			tagOp: 'update_file_key',                                                                                                                                                                                                        
			keyId: $(this).children('input.value').attr('class').replace('value',''),                                                                                                                                                                                                   
			tagId: $('body').find('#tagid').attr('class'),                                                                                                                                                                                                        
			fileId:$('body').find('#fileid').attr('class'), 
			value: $('body').find('input.value').val()
		  },                                                                                                                                                                                                                        
		  success: function(result) {               
		  }
		});
	  }
	});
	$('body').find('.ui-dialog').remove();
  });

  $('body').on('keypress', 'div.oc-dialog div.ui-dialog-content div#meta_data_container ul#meta_data_keys li input.value', function (e) {
	var key = e.which;
	if(key == 13)  
	{
	  $('body').find('#popup_ok').focus();
	  return false;  
	}
  });










  $('tbody').on('click', 'span.more', function(e){
	e.stopPropagation();
	updateFileListTags($(this).parents('tr'), true)
  });

  OC.search.resultTypes.tag = "Tag" ;
  OC.search.resultTypes.metadata = "Metadata" ;

})
