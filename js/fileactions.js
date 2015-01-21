(function() {
		if (!OCA.Meta_data) {
				OCA.Meta_data = {};
		}
		OCA.Meta_data.Util = {
				initialize: function(fileActions) {
						FileActions.register('file', 'Tags', OC.PERMISSION_UPDATE, OC.imagePath('core', 'actions/star'), function(filename) {
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

				$.extend(OCA.Files.FileList.prototype, {
						_createRow: function(fileData,options) {

								var td, simpleSize, basename, extension, sizeColor,
								icon = OC.Util.replaceSVGIcon(fileData.icon),
										name = fileData.name,
										type = fileData.type || 'file',
												mtime = parseInt(fileData.mtime, 10) || new Date().getTime(),
												mime = fileData.mimetype,
														path = fileData.path,
														linkUrl;
								options = options || {};

								if (type === 'dir') {
										mime = mime || 'httpd/unix-directory';
								}

								//containing tr
								var tr = $('<tr></tr>').attr({
										"data-id" : fileData.id,
										"data-type": type,
										"data-size": fileData.size,
												"data-file": name,
												"data-mime": mime,
														"data-mtime": mtime,
														"data-etag": fileData.etag,
																"data-permissions": fileData.permissions || this.getDirectoryPermissions()
								});

								if (fileData.mountType) {
										tr.attr('data-mounttype', fileData.mountType);
								}

								if (!_.isUndefined(path)) {
										tr.attr('data-path', path);
								}
								else {
										path = this.getCurrentDirectory();
								}

								if (type === 'dir') {
										// use default folder icon
										icon = icon || OC.imagePath('core', 'filetypes/folder');
								}
								else {
										icon = icon || OC.imagePath('core', 'filetypes/file');
								}

								// filename td
								td = $('<td></td>').attr({
										"class": "filename",
										"style": 'background-image:url(' + icon + '); background-size: 32px;'
								});

								// linkUrl
								if (type === 'dir') {
										linkUrl = this.linkTo(path + '/' + name);
								}
								else {
										linkUrl = this.getDownloadUrl(name, path);
								}
								td.append('<input id="select-' + this.id + '-' + fileData.id +
												'" type="checkbox" /><label for="select-' + this.id + '-' + fileData.id + '"></label>');
								var linkElem = $('<a></a>').attr({
										"class": "name",
										"href": linkUrl
								});

								// from here work on the display name
								name = fileData.displayName || name;

								// split extension from filename for non dirs
								if (type !== 'dir' && name.indexOf('.') !== -1) {
										basename = name.substr(0, name.lastIndexOf('.'));
										extension = name.substr(name.lastIndexOf('.'));
								} else {
										basename = name;
										extension = false;
								}
								var nameSpan=$('<span></span>').addClass('nametext');
								var innernameSpan = $('<span></span>').addClass('innernametext').text(basename);
								nameSpan.append(innernameSpan);
								linkElem.append(nameSpan);
								if (extension) {
										nameSpan.append($('<span></span>').addClass('extension').text(extension));
								}
								// dirs can show the number of uploaded files
								if (type === 'dir') {
										linkElem.append($('<span></span>').attr({
												'class': 'uploadtext',
												'currentUploads': 0
										}));
								}
								td.append(linkElem);
								tr.append(td);

								// size column
								if (typeof(fileData.size) !== 'undefined' && fileData.size >= 0) {
										simpleSize = humanFileSize(parseInt(fileData.size, 10), true);
										sizeColor = Math.round(160-Math.pow((fileData.size/(1024*1024)),2));
								} else {
										simpleSize = t('files', 'Pending');
								}

								td = $('<td></td>').attr({
										"class": "filesize",
										"style": 'color:rgb(' + sizeColor + ',' + sizeColor + ',' + sizeColor + ')'
														}).text(simpleSize);
										tr.append(td);

										// date column (1000 milliseconds to seconds, 60 seconds, 60 minutes, 24 hours)
										// difference in days multiplied by 5 - brightest shade for files older than 32 days (160/5)
										var modifiedColor = Math.round(((new Date()).getTime() - mtime )/1000/60/60/24*5 );
										// ensure that the brightest color is still readable
										if (modifiedColor >= '160') {
												modifiedColor = 160;
										}
										td = $('<td></td>').attr({ "class": "date" });
										td.append($('<span></span>').attr({
												"class": "modified",
												"title": formatDate(mtime),
												"style": 'color:rgb('+modifiedColor+','+modifiedColor+','+modifiedColor+')'
																}).text( relative_modified_date(mtime / 1000) ));
												tr.find('.filesize').text(simpleSize);
												tr.append(td);


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



																tr.attr('data-tags', tags['data']);
																var tag = $('<span></span>').addClass('tag');
																var tagids = tags['data'].split(',');
																tagids.forEach(function(entry) {
																		tag.append('<i class=\'fa fa-tag\'></i>');
																});

																tr.children('td').children('a').append(tag);
														}
												} else {
														tr.attr('data-tags', fileData.tags);
														var tag = $('<span></span>').addClass('tag');
														var tagids = fileData.tags.split(',');
														tagids.forEach(function(entry) {
																tag.append('<i class=\'fa fa-tag\'></i>');
														});

														tr.children('td').children('a').append(tag);



												}
												return tr;
						}
				});
		}
});
