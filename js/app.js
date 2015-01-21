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
						if(!fileData.tags){
								if(fileData.type == 'file'){
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
										
										if(tags['data']){
												tr.attr('data-tags', tags['data']);
												var tag = $('<span></span>').addClass('tag');
												var tagids = tags['data'].split(',');
												tagids.forEach(function(entry) {
														tag.append('<i class=\'icon-tag\'></i>');
												});

												tr.children('td.date').append(tag);
										}
								}
						} else {
								tr.attr('data-tags', fileData.tags);
								var tag = $('<span></span>').addClass('tag');
								var tagids = fileData.tags.split(',');
								tagids.forEach(function(entry) {
										tag.append('<i class=\'icon-tag\'></i>');
								});

								tr.children('td.filename').children('div.row').append(tag);



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
})
