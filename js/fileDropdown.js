/*
 * Copyright (c) 2015, written by Christian Brinch, DeIC.
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * THIS FILE contains the function for the tags drop down, when the file action
 * is clicked 
 */



function addNewDropDown(file){                                                                                                          
  $.ajax({                                                                                                                              
	url: OC.filePath('meta_data', 'ajax', 'temp.php'),                                                                               
	data: {sortValue: "color", direction: "asc"},
	success: function(response) {                                                                                                           
	  $('#test').html('<div id="newTag"><input type=\"text\" value=\"\" placeholder=\"new tag\"></div><p>');                          
		if(response){
		  var containerWidth = $('#test').width();
		  var tagWidth = 0;
		  $.each( response['tags'], function(key,value) {
			var $tag = $('<span data-id="tag-'+value.tagid+'" style="opacity: 0;"><span class="label outline label-'+colorTranslate(value.color)+'"><i class="icon-tag"></i> '+value.descr+'</span></span>'); 
			$('#test').append($tag);
			var temp = $('#test span[data-id="tag-'+value.tagid+'"]').width();
			if(tagWidth + temp >= containerWidth){
			  $('#test span[data-id="tag-'+value.tagid+'"]').remove();
			  $('#test').append('<p>');
			  tagWidth = 0;
			  $('#test').append($tag);
			  $('#test span[data-id="tag-'+value.tagid+'"]').css('opacity','1');
			}		
			tagWidth = tagWidth + temp;
			$('#test span[data-id="tag-'+value.tagid+'"]').css('opacity','1');

		  });	
		}
	  $('#test').show();                                                                                                                  
	},                                                                                                                                    
  });
  $('div#dropdown').on("click", function(e) {                                                                                 
	e.stopPropagation();                                                                                                                
  });                                                                                                                                   
  $('div#dropdown').on("keypress", 'input', function(e){
	if(e.which == 13){
	  $(this).blur();
	}
  });
  $('div#dropdown').on("focusout", "input", function() {                                                                                
	if($(this).val() != ''){                                                                                                            
	  $.ajax({                                                                                                                          
		url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),                                                                            
		data: {                                                                                                                         
		  tagOp: 'new',                                                                                                                 
		  tagName: $(this).val(),                                                                                                         
		  tagState: "1",                                                                                                                  
		  tagColor: "color-1",
		},                                                                                                                              
		type: "POST",                                                                                                                   
		success: function(result) {                                                                                                     
		  $.ajax({                                                                                                                      
			url: OC.filePath('meta_data', 'ajax', 'updatefileinfo.php'),                                                                
			data: {                                                                                                                       
			  fileid: file,                                                                                                               
			  tagid: $.parseJSON(result)['tagid']                                                                                           
			},                                                                                                                            
			type: "POST",                                                                                                                 
			success: function(result) {                                                                                                   
			  $("#dropdown").remove();                                                                                                    
			  $('tr').removeClass('mouseOver');                                                                                           
			  updateSidebar();
			  updateFileListTags($('tr[data-id='+file+']'));
			},                                                                                                                            
		  });                                                                                                                           
		},                                                                                                                              
	  });            
	};                                                                                                                                  
  });                                     
  $('div#dropdown').on("click", "div#test span.label" , function() {                                                                         
	if( $(this).attr('id') != "newTag"){                                                                                                
	  var tagid = $(this).parent('span').attr('data-id').split('-');
	  $.ajax({                                                                                                                          
		url: OC.filePath('meta_data', 'ajax', 'updatefileinfo.php'),                                                                    
		async: false,                                                                                                                   
		timeout: 200,                                                                                                                   
		data: {                                                                                                                         
		  fileid: file,                                                                                                                 
		  tagid: tagid[1]                                                                                                       
		},                                                                                                                              
		type: "POST",                                                                                                                   
		success: function(result) {                                                                                                     
		  $("#dropdown").remove();                                                                                                      
		  $('tr').removeClass('mouseOver');                                                                                             
		  updateFileListTags($('tr[data-id='+file+']'));
		},                                                                                                                              
	  });                                                                                                                               
	}                                                                                                                                   
  });                                                                                                                                   
}          


