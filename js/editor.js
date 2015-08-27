/*
 * Copyright (c) 2015, written by Christian Brinch, DeIC.
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * THIS FILE contains the script for the meta data editor
 *  
 */
 

function newEntry(entry){
  entry = typeof entry !== 'undefined' ? entry : null;

  if(!entry){
	return $('\
				<li class="new">\
					<span class="keyname hidden"></span>\
					<input class="edit" type="text" placeholder="New key name" value="" />\
						<span class="deletekey">&#10006;</span>\
					<input class="value hidden" type="text" value="" />\
			  </li>');
  } else {
	return $('\
				<li id="'+entry['keyid']+'">\
					<span class="keyname hidden">'+entry['name']+'</span>\
					<input class="edit" type="text" value="'+entry['name']+'" />\
					<span class="deletekey">&#10006;</span>\
					<input class="'+entry['keyid']+' value hidden" type="text" value="" />\
				</li>');
  }
}


$(document).ready(function() {
  $('body').on('click', '.oc-dialog-close', function(){
	$('body').find('.ui-dialog').remove();
  });

  $('body').on('click', '#popup_cancel', function(){
	$('body').find('.ui-dialog').remove();
  });
})

