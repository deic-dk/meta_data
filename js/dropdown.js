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
	var files = (file+'').split(':');
	$.ajax({
		url: OC.filePath('meta_data', 'ajax', 'getTags.php'),
		data: {sortValue: 'color', direction: 'asc', fileId: file},
		success: function(response) {
			$('#tag_action').html('<div id="newTag"><input type=\"text\" value=\"\" placeholder=\"new tag\"></div><p>');
			if(response){
				var containerWidth = $('#tag_action').width();
				var tagWidth = 0;
				$.each( response['tags'], function(key, value){
				var $tag = $('<span data-id="tag-'+value.id+'" style="opacity: 0;"><span class="label outline label-'+colorTranslate(value.color)+'"><i class="icon-tag"></i> '+value.name+'</span></span>');
				$('#tag_action').append($tag);
				var temp = $('#tag_action span[data-id="tag-'+value.id+'"]').width();
				if(tagWidth + temp >= containerWidth){
					$('#tag_action span[data-id="tag-'+value.id+'"]').remove();
					$('#tag_action').append('<p>');
					tagWidth = 0;
					$('#tag_action').append($tag);
					$('#tag_action span[data-id="tag-'+value.id+'"]').css('opacity','1');
				}
				tagWidth = tagWidth + temp;
				$('#tag_action span[data-id="tag-'+value.id+'"]').css('opacity','1');
				});
			}
			$('#tag_action').show();
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
					tagVisibleState: "1",
					tagColor: "color-1",
					tagPublicState: "0",
				},
				type: "POST",
				success: function(result) {
					$.ajax({
					url: OC.filePath('meta_data', 'ajax', 'updateFileInfo.php'),
					data: {
						fileid: file,
						tagid: $.parseJSON(result)['id']
					},
					type: "POST",
					success: function(result) {
						$("#dropdown").remove();
						$('tr').removeClass('mouseOver');
						//updateSidebar();
						for (var i = 0; i < files.length; i++) {
							updateFileListTags($('tr[data-id='+files[i]+']'));
						}
					},
					});
				},
			});
		};
	});
	$('div#dropdown').on("click", "div#tag_action span.label" , function() {
		if($(this).attr('id') != "newTag"){
			var tagid = $(this).parent('span').attr('data-id').split('-');
			$.ajax({
				url: OC.filePath('meta_data', 'ajax', 'updateFileInfo.php'),
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
					$('a#tag.tag').removeClass('mouseOver');
					for (var i = 0; i < files.length; i++) {
						updateFileListTags($('tr[data-id='+files[i]+']'));
					}
				},
			});
		}
	});
}


