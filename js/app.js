if (!OCA.Meta_data){
		OCA.Meta_data = {};
}

OCA.Meta_data.App = {

		_dummy: null,

		initTaggedFiles: function($el, tagid) {
				if (this._linkFileList && this._dummy==tagid) {
						return this._linkFileList;
				}
				this._dummy = tagid;
				
				this._linkFileList = new OCA.Meta_data.FileList(
								$el,
								{
										scrollContainer: $('#app-content'),
										linksOnly: true,
										fileActions: this._createFileActions(),
										tagid: tagid
								}
								);
				
				this._linkFileList.$el.find('#emptycontent').text(t('Meta_data', 'No files found'));
				
				return this._linkFileList;
		},

		removeTaggedFiles: function() {
				if (this._linkFileList) {
						this._linkFileList.$fileList.empty();
				}
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
								var tags = '';
								$.ajax({
										async: false,
										url: OC.filePath('meta_data', 'ajax', 'single.php'),
										data: {fileid: fileData.id},
										success: function( response )
										{
												if( response){

														
														var tagwidth = 0;
														var overflow = 0;
														tr.find('div.filelink-wrap').after('<div class="filetags-wrap col-xs-4"></div>');
														$.each(response['tags'], function(key,value) {
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
										}
								});


						}
						return tr;
				}
		}

};


function colorTranslate(color){
		if(color.indexOf('color-1') > -1)  return "label-default";
		if(color.indexOf('color-2') > -1)  return "label-primary";
		if(color.indexOf('color-3') > -1)  return "label-success";
		if(color.indexOf('color-4') > -1) return "label-info";
		if(color.indexOf('color-5') > -1) return "label-warning";
		if(color.indexOf('color-6') > -1) return "label-danger";
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
		$.ajax({
				url: OC.filePath('meta_data', 'ajax', 'temp.php'),
				success: function(response)	{
						if(response){
								$('.nav-sidebar').append('<li class="empty"></li>');
								$.each( response['tags'], function(key,value) {
										if(value.public==1){
												$('.nav-sidebar').append('<li data-id="tag-'+value.tagid+'"><span class="label outline '+colorTranslate(value.color)+'" data-tag="'+value.tagid+'"><span class="deletetag" style="display:none"><i class=\'icon-cancel-circled\'></i></span><i class="icon-tag"></i> '+value.descr+' </span></li>');
										}
								});
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
														tr.find('div.filetags-wrap').append('<p>');
														tr.find('div.filetags-wrap').append('<span data-tag=\''+value.tagid+'\' class=\'label outline '+color+'\'><span class="deletetag" style="display:none"><i class=\'icon-cancel-circled\'></i></span><i class=\'icon-tag\'></i><span class=\'tagtext\'>'+value.descr+'</span></span>' );
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

		$('ul.nav-sidebar').on('click', 'li[data-id^=tag-] span', function(e) {
				var tagid = $(e.target).parent().attr('data-id').split('-');
				$('div[id^=app-content-]').hide();
				$('div#app-content-tag-'+tagid[1]).removeClass('hidden');
				$('div#app-content-tag-'+tagid[1]).show();
				window.history.pushState("object or string","title", '/index.php/apps/files/?dir=%2F&view=tag-'+tagid[1]);
				location.reload();
		});

		$('[id^=app-content-tag]').on('show', function(e) {
				var tagid = e.target.getAttribute('id').split('-');
				OCA.Meta_data.App.initTaggedFiles($(e.target), tagid[3]);
		});

		$('[id^=app-content-tag]').on('hide', function() {
				OCA.Meta_data.App.removeTaggedFiles();
		});

		if (OCA.Files) {
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

		$('tbody').on('click', 'span.more', function(e){
				e.stopPropagation();
				updateFileListTags($(this).parents('tr'), true)
		});


})
