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

								if(fileData.tags){
										tr.attr('data-tags', fileData.tags);
										var tagids = fileData.tags.split(',');
								} else {
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
								}
								if(tagids){
										var tag = $('<div></div>').addClass('col-xs-4').addClass('filetags-wrap');
										tagids.forEach(function(entry,i) {
												tag.append('<span data-tag=\''+entry+'\' class=\'label label-warning\'><span class="deletetag" style="display:none"><i class=\'icon-cancel-circled\'></i></span><i class=\'icon-tag\'></i><span class=\'tagtext\'>'+tags['tagnames'][i]+'</span></span>' );
								//				tag.append('<span data-tag=\''+entry+'\' class=\'label label-info\'><span class="deletetag" style="display:none"><i class=\'icon-cancel-circled\'></i></span><i class=\'icon-tag\'></i><span class=\'tagtext\'>'+tags['tagnames'][i]+'</span></span>' );
										});

										tr.find('div.filelink-wrap').after(tag);
										tr.find('div.filelink-wrap').removeClass('col-xs-8').addClass('col-xs-4');
								}
						}

						return tr;
				}
		}

};


$(document).ready(function() {
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

		$('tbody').on('mouseenter', 'tr td div.row div.filetags-wrap span[class^=label]', function(){
				$(this).children('i').hide();
				$(this).children('span.deletetag').show();
		}).on('mouseleave', 'tr td div.row div.filetags-wrap span[class^=label]', function(){
				$(this).children('i').show();
				$(this).children('span.deletetag').hide();
		});
				
		$('tbody').on('click', 'span.deletetag', function(e){
				e.stopPropagation();
				var tagid = $(this).parent('span').attr('data-tag')
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


})
