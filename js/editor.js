



function newEntry(entry){
    entry = typeof entry !== 'undefined' ? entry : null;
	
	if(!entry){
 	  return $('<li class="new"><span class="keyname hidden"></span><input class="edit" type="text" placeholder="New key name" value=""><span class="deletekey">&#10006;</span><input class="value hidden" type="text" value=""></li>');
	} else {
      return $('<li id="'+entry['keyid']+'"><span class="keyname hidden">'+entry['descr']+'</span><input class="edit" type="text" value="'+entry['descr']+'"><span class="deletekey">&#10006;</span><input class="'+entry['keyid']+' value hidden" type="text" value=""></li>');
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
