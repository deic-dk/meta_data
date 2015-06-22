function updateTagsView(sortValue, direction){	
  $('tbody#fileList').html('');
  $('tfoot').html('');

  sortValue = typeof sortValue !== 'undefined' ? sortValue : 'color';
  direction = typeof direction !== 'undefined' ? direction : 'asc';

  var total=0;
  $.ajax({
	url: OC.filePath('meta_data', 'ajax', 'temp.php'),
	data: {sortValue: sortValue, direction: direction, fileCount: true},
	success: function(response) {
	  if(response) {
		$.each(response['tags'], function(key,value) {	
		  total+=value.size;
		  $('tbody#fileList').append(' \
			  <tr data-id="'+value.tagid+'" data-name="'+value.descr+'">\
			  <td class="filename" style="height:34px">\
			  <div class="row">\
			  <div class="col-xs-1 text-right"></div>\
			  <div class="col-xs-8 filelink-wrap" style="height:34px">\
			  <span class="taginfo">\
			  <a href="/index.php/apps/files/?dir=%2F&view=tag-'+value.tagid+'" style="text-decoration:none" >\
			  <span class="label outline '+colorTranslate(value.color)+'" data-tag="'+value.tagid+'">\
			  <i class="icon-tag" style="display: inline;"></i>\
			  <span class="tagtext">'+value.descr+'</span>\
			  </span>\
			  </a>\
			  </span>\
			  <span class="editstuff hidden">\
			  <input class="edittag" type="text" value="'+value.descr+'">\
			  <div class="color-box color-1"></div>\
			  <div class="color-box color-2"></div>\
			  <div class="color-box color-3"></div>\
			  <div class="color-box color-4"></div>\
			  <div class="color-box color-5"></div>\
			  <div class="color-box color-6"></div>\
			  <span class="meta_data btn-group btn-group-xs"><a href="#" original-title="" class="btn btn-flat btn-default action action-primary action-meta_data">Meta data template</a></span>\
			  </span>\
			  </div>\
			  <div class="col-xs-3 fileactions-wrap text-right">\
			  <div class="btn-group btn-group-xs fileactions">\
			  <a class="btn btn-flat btn-default action-primary action action-edit" href="#" original-title=""> Edit</a>\
			  <a class="btn btn-flat btn-default dropdown-toggle" data-toggle="dropdown" href="#">\
			  <i class="icon-angle-down"></i>\
			  </a>\
			  <ul class="dropdown-menu" style="display: none;">\
			  <li><a class="action action-delete" data-action="Delete" href="#">\
			  <img class="svg" src="/core/img/actions/delete.svg">\
			  <span> Delete</span>\
			  </a></li>\
			  </ul>\
			  </div>\
			  <div class="btn-group btn-group-xs confirm" style="display:none">\
			  <a class="btn btn-flat btn-default action-primary action action-ok" href="#" original-title=""> Confirm</a>\
			  </div>\
			  </div>\
			  </div>\
			  </td>\
			  <td class="display">  <input type="checkbox" name="display"> </td>\
			  <td class="filesize">'+value.size+'</td>\
			  </tr>\
			  ');
		  $('tbody#fileList tr[data-id='+value.tagid+'] div.'+value.color).addClass('border');
		  if(value.public==1){  
			$('tbody#fileList tr[data-id='+value.tagid+'] td.display input').prop('checked', true);
		  } else {
			$('tbody#fileList tr[data-id='+value.tagid+'] td.display input').prop('checked', false);
		  }
		});
	  }
	  if(response){
		var ntags=response['tags'].length;
	  } else {
		var ntags = 0;
	  }
	  $('tfoot').append('\
		  <tr class="summary text-sm">\
		  <td><span class="info">'+ntags+' tags</span></td>\
		  <td></td>\
		  <td class="filesize">'+total+' files</td>\
		  </tr>\
		  ');


	}
  });
}

function toggleBorder(){
  $(this).siblings('div').removeClass('border');
  $(this).addClass('border');
}

function resetInput(){
  $('div#controls div.color-box').removeClass('border');
  $('div#controls div.color-1').addClass('border');
  $('div#controls input.edittag').val('');;
  $('div#newtag').toggle();
}





$(document).ready(function() {
  updateTagsView("color");


  /* 
   * Bind click to 'New tag' button and to 'Ok' and 'Cancel' buttons within
   */
  $('div#controls div#upload a').on('click', function(){
	$('div#newtag').toggle();
  });	   

  $('div#controls div#cancel').on('click', resetInput );

  $('div#controls div#ok').on('click', function() {
	if($('div#controls input.edittag').val() != ''){	
	  var color=$('div#controls div.border').attr('class');
	  color=color.replace('color-box','').replace('border','').replace(' ','')
		$.ajax({
		  url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
		  type: "post",
		  data: {tagOp:'new',tagState:'1', tagName:$('div#controls input.edittag').val(), tagColor: color},
		});
	  updateTagsView("color");					
	  updateSidebar();			
	}
	resetInput();
  });

  $("div#controls").on('click', '.color-box', toggleBorder );


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
	var tagid   = $(this).parents('tr').attr('data-id');
	var tagname = $(this).parents('tr').attr('data-name');
	var color   = $(this).parent('td').siblings('td.filename').find('div.border').attr('class');
	color=color.replace('color-box','').replace('border','').replace(' ','');

	if($(this).is(":checked")){
	  $.ajax({
		url: OC.filePath('meta_data', 'ajax', 'update.php'), 
		data: {tagid: tagid, tagname: tagname, color: color, visible: 1},
	  });
	  updateSidebar();
	} else {
	  $('ul.nav-sidebar li[data-id="tag-'+tagid+'"]').remove();
	  $.ajax({
		url: OC.filePath('meta_data', 'ajax', 'update.php'), 
		data: {tagid: tagid, tagname: tagname, color: color, visible: 0},
	  });
	}
  });



  /* 
   * Bind click to tag action items and toggle action drop down
   */
  $('tbody#fileList').on('click', 'tr a.dropdown-toggle', function(){
	$(this).siblings('ul').toggle();		
  });


  $("tbody#fileList").on('click', 'tr td.filename a.action-edit', function(){
	$(this).parent('div').siblings('div.confirm').toggle();
	$(this).parent('div').hide();
	$(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').children().toggleClass('hidden'); 
	$(this).parent('div').find('.meta_data').toggle();

  });	

  $('tbody#fileList').on('click', 'tr a.action-delete', function(){
	var tagid=$(this).parents('tr').attr('data-id');
	$.ajax({
	  url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
	  type: "post",
	  data: {tagOp:'delete',tagId:tagid},
	});
	updateTagsView();					
	updateSidebar();			

  });


  /*
   * Bind click to Edit meta data button
   */
  $("tbody#fileList").on('click', 'tr td.filename a.action-meta_data', function(){
	var title=$(this).parents('.filename').find('.tagtext').html();
	var tagid=$(this).parents('tr').attr('data-id');


	var html = $('<div><span id="tagid" class="'+tagid+'"><h3 class="oc-dialog-title">Meta data template editor for the tag: '+title+'</h3></span><a class="oc-dialog-close close svg"></a><div id="meta_data_container">\
		<div id=\"emptysearch\">No meta data defined</div><ul id="meta_data_keys"></ul></div><div style="text-align:center;"><button id="add_key" class="btn btn-flat btn-default">Add meta data field</button></div>\
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
		}
	  }
	});




  });

  $('body').on('click', '#add_key', function(){
	$('body').find('#emptysearch').hide();
	$('body').find('#meta_data_keys').append(newEntry());
	$('body').find('#meta_data_keys li:last-child input').focus();
  });

  $('body').on('focusout', 'div.oc-dialog div.ui-dialog-content div#meta_data_container ul#meta_data_keys li input.edit', function(){
	if( !$(this).val() ){
	  if($(this).parent('li').siblings().size() === 0) $('body').find('#emptysearch').show();
	  $(this).parent('li').remove();
	} else if ( !$(this).parent('li').hasClass('new')) {
	  $(this).parent('li').addClass('alt') 
	}
  }); 

  $('body').on('keypress', 'div.oc-dialog div.ui-dialog-content div#meta_data_container ul#meta_data_keys li input.edit', function (e) {
	var key = e.which;
	if(key == 13)  
	{
	  $('body').find('#add_key').focus();
	  return false;  
	}
  });


  $('body').on('click', 'div.oc-dialog div.ui-dialog-content div#meta_data_container ul#meta_data_keys li span.deletekey', function(){
	if($(this).parent('li').siblings().size() === 0) $('body').find('#emptysearch').show();
	if( !$(this).parent('li').hasClass('new') ){
	  $(this).parent('li').addClass('del');
	  $(this).parent('li').hide();
	} else {
	  $(this).parent('li').remove();
	}
  }); 




  $('body').on('click', '#popup_ok', function(){
	var tagid=$(this).parent('div').siblings('span').attr('class'); 
	$(this).parent('div').siblings('div#meta_data_container').children('ul').children('li').each(function() {
	  if($(this).children('input.edit').val() != '' && $(this).hasClass('new') ){
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
	  } else if($(this).children('input.edit').val() != '' && $(this).hasClass('del')) {
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
	  } else if($(this).children('input.edit').val() != '' && $(this).hasClass('alt')) {
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
	$('body').find('.ui-dialog').remove();
  });














  /*
   * Handle edit tag
   */
  $("tbody#fileList").on('click', '.color-box', toggleBorder );	


  $("tbody#fileList").on('click', 'tr td.filename a.action-ok', function(){
	$(this).parent('div.confirm').hide();
	$(this).parent('div').siblings('div.fileactions').show();
	$(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').children().toggleClass('hidden');  

	var color   = $(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').find('div.border').attr('class');
	color=color.replace('color-box','').replace('border','').replace(' ','');
	var tagid   = $(this).parents('tr').attr('data-id');
	var newname = $(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').find('input').val();	
	if(newname == ""){
	  newname = $(this).parents('tr').attr('data-name');
	}

	$(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').children('span.taginfo').children('a').children('span').removeClass(function (index, css) {
	  return (css.match (/(^|\s)label-\S+/g) || []).join(' ');
	}).addClass(colorTranslate(color));
	$('ul.nav-sidebar li[data-id="tag-'+tagid+'"] span').removeClass(function (index, css) {
	  return (css.match (/(^|\s)tag-\S+/g) || []).join(' ');
	}).addClass(colorTranslateTag(color));		

	$(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').children('span.taginfo').children('span').html('<i class="icon-tag" style="display: inline;"></i>'+newname);
	$('ul.nav-sidebar li[data-id="tag-'+tagid+'"] a').html('<i class="icon icon-tag" style="display: inline;"></i><span>'+newname+'</span>');
	$('ul.nav-sidebar li[data-id="tag-'+tagid+'"] a i').addClass(colorTranslateTag(color));
	$(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').find('input').val(newname);

	if($(this).parents('td').siblings('td.display').children('input').is(':checked')){
	  var state=1;
	} else {
	  var state=0;
	}
	$.ajax({
	  url: OC.filePath('meta_data', 'ajax', 'update.php'), 
	  data: {tagid: tagid, tagname: newname, color: color, visible: state},
	});
  });	


})
