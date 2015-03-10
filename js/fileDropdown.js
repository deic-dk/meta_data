
$(document).ready(function(){


		// Attach tag button to 'files' mouse-over bar'
		if(typeof FileActions !== 'undefined') {
				// Add action to tag a group of files
				/*    $(".selectedActions").html(function(index, oldhtml) {
					  if(oldhtml.indexOf("download") > 0) {
					  var tagIconPath = OC.imagePath('meta_data','icon_tag');
					  var newAction = "<a class=\"donwload\" id=\"tagGroup\">";
					  newAction += "<img class=\"svg\" src=\"" + tagIconPath + "\" alt=\"Tag group of file\" style=\"width: 17px; height: 17px; margin: 0px 5px 0px 5px;\" />";
					  newAction += t('meta_data', 'Tag selected files') + "</a>";
					  return newAction + oldhtml;
					  } else {
					  return oldhtml;
					  }
					  });*/
/*
				var infoIconPath = OC.imagePath('meta_data','icon_info');
				FileActions.register('file', t('meta_data', 'Tags'), OC.PERMISSION_UPDATE, infoIconPath, function(filename) {
						// Action to perform when clicked
						if(scanFiles.scanning) { return; } // Workaround to prevent additional http request block scanning feedback
						if($('#dropdown').length == 0){      
								var tr = FileList.findFileEl(filename);                                    
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
*/
		}



});




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
										var $tag = $('<span data-id="tag-'+value.tagid+'" style="opacity: 0;"><span class="label outline '+colorTranslate(value.color)+'"><i class="icon-tag"></i> '+value.descr+' </span></span>'); 
										
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


