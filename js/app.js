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
						this._linkFileList.$el.find('.breadcrumb').remove();
				}
		},

		_createFileActions: function() {
				// inherit file actions from the files app
				var fileActions = new OCA.Files.FileActions();
				// note: not merging the legacy actions because legacy apps are not
				// compatible with the sharing overview and need to be adapted first
				fileActions.registerDefaultActions();
				fileActions.register('file', 'Tags', OC.PERMISSION_UPDATE, OC.imagePath('core', 'actions/star'), function(filename) {
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
				fileActions.merge(OCA.Files.fileActions);

				return fileActions;
		},

		_onActionsUpdated: function(ev) {
				_.each([this._inFileList, this._outFileList, this._linkFileList], function(list) {
						if (!list) {
								return;
						}

						if (ev.action) {
								list.fileActions.registerAction(ev.action);
						} else if (ev.defaultAction) {
								list.fileActions.setDefault(
												ev.defaultAction.mime,
												ev.defaultAction.name
												);
						}
				});
		},

		modifyFilelist: function() {
				var oldCreateRow = OCA.Files.FileList.prototype._createRow;
				OCA.Files.FileList.prototype._createRow = function(fileData) {
						var tr = oldCreateRow.apply(this, arguments);

						if(fileData.type == 'file'){

								//	if(fileData.tags){
								//			tr.attr('data-tags', fileData.tags);
								//			var tagids = fileData.tags.split(',');
								//	} else {
								var tags = '';
								$.ajax({
										async: false,
										url: OC.filePath('meta_data', 'ajax', 'single.php'),
										data: {fileid: fileData.id},
										success: function( response )
										{
												tags = response;
										}
								});

								if(tags['tagids']){

										tr.attr('data-tags', tags['tagids']);
										var tagids = tags['tagids'].split(',');
								}
								//	}
								if(tagids){
										var tag = $('<div></div>').addClass('col-xs-4').addClass('filetags-wrap');
										tagids.forEach(function(entry,i) {
												var color = colorTranslate(tags['tagcolor'][i]);
												tag.append('<span data-tag=\''+entry+'\' class=\'label outline '+color+'\'><span class="deletetag" style="display:none"><i class=\'icon-cancel-circled\'></i></span><i class=\'icon-tag\'></i><span class=\'tagtext\'>'+tags['tagnames'][i]+'</span></span>' );
										});

										tr.find('div.filelink-wrap').after(tag);
										tr.find('div.filelink-wrap').removeClass('col-xs-8').addClass('col-xs-4');

										var width = tr.find('div.filelink-wrap').width();
										var filename = tr.find('span.innernametext').html();
										tr.find('span.innernametext').html(start_and_end( filename, tr.find('div.filelink-wrap')));										
								}
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

function updateFileListTags(tr){
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
						tags = response;
				}
		});

		if(tags['tagids']){

				tr.attr('data-tags', tags['tagids']);
				var tagids = tags['tagids'].split(',');
		}

		if(tagids){
				tagids.forEach(function(entry,i) {
						var color = colorTranslate(tags['tagcolor'][i]);
						$('.filetags-wrap').append('<span data-tag=\''+entry+'\' class=\'label outline '+color+'\'><span class="deletetag" style="display:none"><i class=\'icon-cancel-circled\'></i></span><i class=\'icon-tag\'></i><span class=\'tagtext\'>'+tags['tagnames'][i]+'</span></span>' );
				});

		}
}


$(document).ready(function() {
		updateSidebar();

		$('ul.nav-sidebar').on('click', 'li[data-id^=tag-] span:not(.deletetag)', function(e) {
				var tagid = $(e.target).parent().attr('data-id').split('-');
				$('div[id^=app-content-]').hide();
				$('div#app-content-tag-'+tagid[1]).removeClass('hidden');
				$('div#app-content-tag-'+tagid[1]).show();
				window.location = '?dir=%2F&view=tag-'+tagid[1];
				//				window.history.pushState("object or string","title", '/index.php/apps/files/?dir=%2F&view=tag-'+tagid[1]);
		});

		$('[id^=app-content-tag]').on('show', function(e) {
				var tagid = e.target.getAttribute('id').split('-');
				OCA.Meta_data.App.initTaggedFiles($(e.target), tagid[3]);
				$('table#filestable').addClass('panel');
				$('table#filestable thead').addClass('panel-heading');

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
								$('tr[data-id="'+fileid+'"]').find('span[data-tag="'+tagid+'"]').remove();
						}

				});


		});				


		/*
		 *
		 * This next block of code is for deleting tags (and removes the tag from all files)
		 * CURRENTLY DISABLED
		 */ 

/*		$('.nav-sidebar').on('mouseenter', 'span[class^=label]', function(){
				$(this).children('i').hide();
				$(this).children('span.deletetag').show();
		}).on('mouseleave', 'span[class^=label]', function(){
				$(this).children('i').show();
				$(this).children('span.deletetag').hide();
		});

		$('.nav-sidebar').on('click', 'span.deletetag', function(e){
				e.stopPropagation();
				var tagid = $(this).parent('span').attr('data-tag');
				var fileid= $(this).parent('span').parent('div').parent('div').parent('td').parent('tr').attr('data-id');
				
				
				$.ajax({
						async: false,
						url: OC.filePath('meta_data', 'ajax', 'deletetag.php'),
						data: {
								tagid:  tagid	
						},
						success: function( response )
						{
								$('tr').find('span[data-tag="'+tagid+'"]').remove();
								$('ul.nav-sidebar li[data-id="tag-'+tagid+'"]').remove();
						}

				});


		});				
*/

})
