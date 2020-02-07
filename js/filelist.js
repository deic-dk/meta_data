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
 
function getGetParams() {
  var ptrn = new RegExp('([\?&])([^&#]+)=([^&#]+)', 'g');
	var match;
	var results = {};
	while ((match = ptrn.exec(window.location.href)) != null) {
		if(match[1]=='?' && match[2]=='dir'){
			continue;
		}
		if(match[2]=='view' && match[3].substring(0, 4)=='tag-'){
			continue;
		}
		results[match[2]] = match[3];
	}
	return results;
}

function addTags(){
	var FileList = function($el, options) {
		this.initialize($el, options);
		this.tagid = options.tagid;
	};
	
	if(typeof OCA.Files == 'undefined'){
		return;
	}
	
	FileList.oldCreateRow = OCA.Files.FileList.prototype._createRow;

  FileList.prototype = _.extend({}, OCA.Files.FileList.prototype, {
		
		appName: 'Meta_data',

		reload: function(_id, _owner, _group, _errorCallback) {
			if(this.tagid) {
				this._selectedFiles = {};
				this._selectionSummary.clear();
				this.$el.find('.select-all').prop('checked', false);
				this.showMask();
				$('ul.nav-sidebar').find('.active').removeClass('active');
				$('.nav-sidebar li[data-id=tag-'+this.tagid.replace( /(:|\.|\[|\]|,|=)/g, "\\$1" )+'] a').addClass('active');
				// This causes abort and reload. Dropping it causes double calls... FO
				if (this._reloadCall) {
					this._reloadCall.abort();
				}
				if(!this._reloadCall){
				var data = {
						dir : this.getCurrentDirectory(),
						sort: this._sort,
						sortdirection: this._sortDirection,
						tagid: this.tagid,
						keyvals: getGetParams()
					}
					this._reloadCall = $.ajax({
						url: this.getAjaxUrl('list'),
						data: data
					});
				}
				var callBack = this.reloadCallback.bind(this);
				var errorCallback = (typeof _errorCallback !== 'undefined'?_errorCallback:function(){return true;});
				return this._reloadCall.then(function(response){return callBack(response, errorCallback);},
				 function(response){return callBack(response, errorCallback);});
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
			if(typeof OCA.Meta_data.App.tag_semaphore!=='undefined'){
				return tr;
			};
			return OCA.Meta_data.App.newCreateRow(fileData, tr);
		}
		
	});
	
	OCA.Meta_data.FileList = FileList;
}

$(document).ready(function() {
	addTags();
});
