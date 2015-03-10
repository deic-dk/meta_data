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
													<td class="filename">\
													<div class="col-xs-1 text-right"></div>\
													<div class="col-xs-8 filelink-wrap">\
													<span class="tagtext">\
													<a href="/index.php/apps/files/?dir=%2F&view=tag-'+value.tagid+'" style="text-decoration:none" >\
													<span class="label outline '+colorTranslate(value.color)+'" data-tag="'+value.tagid+'"><i class="icon-tag" style="display: inline;"></i>'+value.descr+'</span>\
													</a>\
													<input class="edittag" type="text" value="'+value.descr+'" style="display: none">\
													<div class="color-box color-1" style="display:none"></div>\
													<div class="color-box color-2" style="display:none"></div>\
													<div class="color-box color-3" style="display:none"></div>\
													<div class="color-box color-4" style="display:none"></div>\
													<div class="color-box color-5" style="display:none"></div>\
													<div class="color-box color-6" style="display:none"></div>\
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
				$(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').children('span.tagtext').children().toggle(); 
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
		 * Handle edit tag
		 */
		$("tbody#fileList").on('click', '.color-box', toggleBorder );	


		$("tbody#fileList").on('click', 'tr td.filename a.action-ok', function(){
				$(this).parent('div.confirm').hide();
				$(this).parent('div').siblings('div.fileactions').show();
				$(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').children('span.tagtext').children().toggle(); 

				var color   = $(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').find('div.border').attr('class');
				color=color.replace('color-box','').replace('border','').replace(' ','');
				var tagid   = $(this).parents('tr').attr('data-id');
				var newname = $(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').find('input').val();	
				if(newname == ""){
						newname = $(this).parents('tr').attr('data-name');
				}

				$(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').children('span.tagtext').children('a').children('span').removeClass(function (index, css) {
						return (css.match (/(^|\s)label-\S+/g) || []).join(' ');
				}).addClass(colorTranslate(color));
				$('ul.nav-sidebar li[data-id="tag-'+tagid+'"] span').removeClass(function (index, css) {
						return (css.match (/(^|\s)tag-\S+/g) || []).join(' ');
				}).addClass(colorTranslateTag(color));		

				$(this).parents('div.fileactions-wrap').siblings('div.filelink-wrap').children('span.tagtext').children('span').html('<i class="icon-tag" style="display: inline;"></i>'+newname);
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
