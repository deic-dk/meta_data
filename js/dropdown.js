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
		url: OC.filePath('meta_data', 'ajax', 'getTags.php'),
		data: {sortValue: 'color', direction: 'asc', fileId: file},
		success: function(response) {
			$('#test').html('<div id="newTag"><input type=\"text\" value=\"\" placeholder=\"new tag\"></div><p>');
			if(response){
				var containerWidth = $('#test').width();
				var tagWidth = 0;
				$.each( response['tags'], function(key, value){
				var $tag = $('<span data-id="tag-'+value.id+'" style="opacity: 0;"><span class="label outline label-'+colorTranslate(value.color)+'"><i class="icon-tag"></i> '+value.name+'</span></span>');
				$('#test').append($tag);
				var temp = $('#test span[data-id="tag-'+value.id+'"]').width();
				if(tagWidth + temp >= containerWidth){
					$('#test span[data-id="tag-'+value.id+'"]').remove();
					$('#test').append('<p>');
					tagWidth = 0;
					$('#test').append($tag);
					$('#test span[data-id="tag-'+value.id+'"]').css('opacity','1');
				}
				tagWidth = tagWidth + temp;
				$('#test span[data-id="tag-'+value.id+'"]').css('opacity','1');

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
					updateFileListTags($('tr[data-id='+file+']'));
				},
			});
		}
	});
}


