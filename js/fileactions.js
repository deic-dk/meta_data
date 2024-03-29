/*
 * Copyright (c) 2015, written by Christian Brinch, DeIC.
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 *
 * THIS FILE contains a function that registers the meta data file action
 * It adds the tag icon to the mouse-over fileaction menu in the file list.
 * Currently, only files can be tagged, not directories.
 *
 *
 */


(function() {
	if (!OCA.Meta_data) {
		OCA.Meta_data = {};
	}
	OCA.Meta_data.Util = {
		initialize: function(fileActions) {
			FileActions.register('all', 'Tags', OC.PERMISSION_UPDATE, OC.imagePath('meta_data', 'tag.png'), function(filename) {
				if(scanFiles.scanning) { return; } // Workaround to prevent additional http request block scanning feedback
				if($('#dropdown.metadata').length==0){
					var tr = FileList.findFileEl(filename);
					var itemType = 'file';
					var itemSource = $(tr).data('id');
					var html = '<div id="dropdown" class="drop metadata" data-item-type="'+itemType+'" data-item-source="'+itemSource+'"><div id="tag_action"></div></div>';
					$(html).appendTo( $(tr).find('td.filename') );
					$(tr).addClass('mouseOver');
					addNewDropDown(itemSource);
				}
				else {
					$("#dropdown.metadata").slideUp(200, function(){ $(this).remove();});
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


