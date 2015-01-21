
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
				url: OC.filePath('meta_data', 'ajax', 'getTags.php'),                                                                               
				async: false,                                                                                                                         
				timeout: 200,                                                                                                                         
				data: {                                                                                                                               
						type: "drop"                                                                                                                        
				},                                                                                                                                    
				type: "POST",                                                                                                                         
				success: function(result) {                                                                                                           
						$('#test').html('<ul><li id="newTag"><input type=\"text\" value=\"\" placeholder=\"new tag\"></li></ul>');                          
								$('#test').append(result);                                                                                                          
						$('#test').show();                                                                                                                  
				},                                                                                                                                    
				error: function() {                                                                                                                   
				}                                                                                                                                     
		});  
		$('div#dropwdown').on("click", "input", function(e) {                                                                                 
				e.stopPropagation();                                                                                                                
		});                                                                                                                                   

		$('div#dropdown').on("focusout", "input", function() {                                                                                
				if($(this).val() != ''){                                                                                                            
						$.ajax({                                                                                                                          
								url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),                                                                            
								async: false,                                                                                                                   
								timeout: 2000,                                                                                                                  
								data: {                                                                                                                         
										tagOp: 'new',                                                                                                                 
										tagName: $(this).val(),                                                                                                         
										tagState: "1",                                                                                                                  
								},                                                                                                                              
								type: "POST",                                                                                                                   
								success: function(result) {                                                                                                     
										$.ajax({                                                                                                                      
												url: OC.filePath('meta_data', 'ajax', 'updatefileinfo.php'),                                                                
												async: false,                                                                                                                 
												timeout: 200,                                                                                                                 
												data: {                                                                                                                       
														fileid: file,                                                                                                               
														tagid: $.parseJSON(result)['tagid']                                                                                           
												},                                                                                                                            
												type: "POST",                                                                                                                 
												success: function(result) {                                                                                                   
														$("#dropdown").remove();                                                                                                    
														$('tr').removeClass('mouseOver');                                                                                           
												},                                                                                                                            
												error: function() {                                                                                                           
												}                                                                                                                             
										});                                                                                                                           
								},                                                                                                                              
								error: function(){                                                                                                            
								}                                                                                                                             
						});            

				};                                                                                                                                  
		});                                                                                                                                   
		$('div#dropdown').on("click", "div#test ul li" , function() {                                                                         
				if( $(this).attr('id') != "newTag"){                                                                                                
						$.ajax({                                                                                                                          
								url: OC.filePath('meta_data', 'ajax', 'updatefileinfo.php'),                                                                    
								async: false,                                                                                                                   
								timeout: 200,                                                                                                                   
								data: {                                                                                                                         
										fileid: file,                                                                                                                 
										tagid: $(this).attr('id')                                                                                                       
								},                                                                                                                              
								type: "POST",                                                                                                                   
								success: function(result) {                                                                                                     
										$("#dropdown").remove();                                                                                                      
										$('tr').removeClass('mouseOver');                                                                                             
								},                                                                                                                              
								error: function() {                                                                                                             
								}                                                                                                                               
						});                                                                                                                               
				}                                                                                                                                   
		});                                                                                                                                   
}          


