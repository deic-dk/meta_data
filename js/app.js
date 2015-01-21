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

//				this._linkFileList.appName = t('Meta_data', 'Tagname');
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

				//				if (!this._globalActionsInitialized) {
				//						// in case actions are registered later
				//						this._onActionsUpdated = _.bind(this._onActionsUpdated, this);
				//						OCA.Files.fileActions.on('setDefault.app-sharing', this._onActionsUpdated);
				//						OCA.Files.fileActions.on('registerAction.app-sharing', this._onActionsUpdated);
				//						this._globalActionsInitialized = true;
				//				}


				// when the user clicks on a folder, redirect to the corresponding
				// folder in the files app instead of opening it directly
				//				fileActions.register('dir', 'Open', OC.PERMISSION_READ, '', function (filename, context) {
				//				OCA.Files.App.setActiveView('files', {silent: true});
				//				OCA.Files.App.fileList.changeDirectory(context.$file.attr('data-path') + '/' + filename, true, true);
				//				});
				//				fileActions.setDefault('dir', 'Open');
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
})
