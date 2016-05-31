/*
 * Copyright (c) 2015, written by Christian Brinch, DeIC.
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * THIS FILE extends the default filelist. When the filelist is reloaded, tags
 * are loaded too.
 *
 */
 
(function() {

	var FileList = function($el, options) {
		this.initialize($el, options);
		this.tagid = options.tagid;
	};
	
	FileList.oldCreateRow = OCA.Files.FileList.prototype._createRow;

  FileList.prototype = _.extend({}, OCA.Files.FileList.prototype, {
		
		appName: 'Meta_data',

		reload: function() {
			if(this.tagid) {
				this._selectedFiles = {};
				this._selectionSummary.clear();
				this.$el.find('.select-all').prop('checked', false);
				this.showMask();
				$('ul.nav-sidebar').find('.active').removeClass('active');
				$('.nav-sidebar li[data-id=tag-'+this.tagid+'] a').addClass('active');
				/*if (this._reloadCall) {
					this._reloadCall.abort();
				}*/
				if(!this._reloadCall){
					this._reloadCall = $.ajax({
						url: this.getAjaxUrl('list'),
						data: { 
							dir : this.getCurrentDirectory(),
							sort: this._sort,
							sortdirection: this._sortDirection,
							tagid: this.tagid
						}
					});
				}
				var callBack = this.reloadCallback.bind(this);
				return this._reloadCall.then(callBack, callBack);
			}
			else {
				return OCA.Files.FileList.prototype.reload.apply(this, arguments);
			}
		},

		getAjaxUrl: function(action, params) {
			var q = '';
			if (params) {
				q = '?' + OC.buildQueryString(params);
			}
			return OC.filePath('meta_data', 'ajax', action + '.php') + q;
		},

		updateEmptyContent: function() {
			var dir = this.getCurrentDirectory();
			if (dir === '/') {
				// root has special permissions
				this.$el.find('#emptycontent').toggleClass('hidden', !this.isEmpty);
				this.$el.find('#filestable thead th').toggleClass('hidden', this.isEmpty);
			}
			else {
				OCA.Files.FileList.prototype.updateEmptyContent.apply(this, arguments);
			}
		},
		
		_createRow: function(fileData) {
			var tr = FileList.oldCreateRow.apply(this, arguments);
			return OCA.Meta_data.App.newCreateRow(fileData, tr);
		}
		
	});
	
	OCA.Meta_data.FileList = FileList;
})();
