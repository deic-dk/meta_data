(function() {
		if (!OCA.Meta_data) {
				OCA.Meta_data = {};
		}
		OCA.Meta_data.Util = {
				initialize: function(fileActions) {
						FileActions.register('file', 'Tags', OC.PERMISSION_UPDATE, OC.imagePath('meta_data', 'tag.png'), function(filename) {
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
				},


		};
})();

$(document).ready(function() {
		if (!_.isUndefined(OCA.Files)) {
				OCA.Meta_data.Util.initialize(OCA.Files.fileActions);
		}
});
