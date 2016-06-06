/*
 * Copyright (c) 2015, written by Christian Brinch, DeIC.
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * THIS FILE contains the script for the meta data editor
 *  
 */

$(document).ready(function() {
  $('body').on('click', '.oc-dialog-close', function(){
	$('body').find('.ui-dialog').remove();
  });

  $('body').on('click', '#popup_cancel', function(){
	$('body').find('.ui-dialog').remove();
  });
})

