(function() {

  var FileList = function($el, options) {
	this.initialize($el, options);
	this.tagid=options.tagid;
  };

  FileList.prototype = _.extend({}, OCA.Files.FileList.prototype, {
	appName: 'Meta_data',

	reload: function() {
	  if(this.tagid) {
		this._selectedFiles = {};
		this._selectionSummary.clear();
		this.$el.find('.select-all').prop('checked', false);
		this.showMask();
		if (this._reloadCall) {
		  this._reloadCall.abort();
		}
		this._reloadCall = $.ajax({
		  url: this.getAjaxUrl('list'),
		  data: { 
			dir : this.getCurrentDirectory(),
			sort: this._sort,
			sortdirection: this._sortDirection,
			tagid: this.tagid
		  }
		}); 
		var callBack = this.reloadCallback.bind(this);
		return this._reloadCall.then(callBack, callBack);
	  } else {
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

	_createSummary: function() {
	  var $tr = $('<tr class="summary"></tr>');
	  this.$el.find('tfoot').html($tr);

	  return new OCA.Files.FileSummary($tr);
	},

  });

  OCA.Meta_data.FileList = FileList;
})();
