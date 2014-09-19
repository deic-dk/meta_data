
$(document).ready(function(){

  // Attach tag button to 'files' mouse-over bar'
  if(typeof FileActions !== 'undefined') {
    // Add action to tag a group of files
    $(".selectedActions").html(function(index, oldhtml) {
      if(oldhtml.indexOf("download") > 0) {
        var tagIconPath = OC.imagePath('meta_data','icon_tag');
        var newAction = "<a class=\"donwload\" id=\"tagGroup\">";
        newAction += "<img class=\"svg\" src=\"" + tagIconPath + "\" alt=\"Tag group of file\" style=\"width: 17px; height: 17px; margin: 0px 5px 0px 5px;\" />";
        newAction += t('meta_data', 'Tag selected files') + "</a>";
        return newAction + oldhtml;
      } else {
        return oldhtml;
      }
    });

    var infoIconPath = OC.imagePath('meta_data','icon_info');
    FileActions.register('file', t('meta_data', 'Tags'), OC.PERMISSION_UPDATE, infoIconPath, function(fileName) {
      // Action to perform when clicked
      if(scanFiles.scanning) { return; } // Workaround to prevent additional http request block scanning feedback

      showFileInfo(fileName);
    });
  }






  // This is the content of the tag pop-up window 
  $('#content').append('\n\
      <div id="meta_data_infos" title="' + t('meta_data', 'Information') + '">\n\
      <div id="meta_data_infosData" data-id=""></div>\n\
      <fieldset class="meta_data_tagsbox" id="meta_data_tags_container"><legend><strong>Tags</strong></legend>\n\
      <input type="text" class="form-control" id="meta_data_tags" placeholder="' + t('meta_data', 'Enter tags here') + '" min-width: 150px; />\n\
      </fieldset>\n\
      <fieldset class="meta_data_keysbox" id="meta_data_keys_container"><legend><strong>Keys</strong></legend>\n\
      <div id="meta_data_keys_here">\n\
      <table id="keyTable"></table>\n\
      </div>\n\
      <div id="addNewKey">\n\
      <input type="text" id="newKey"><input type="button" id="newKeyButton" value="New key">\n\
      </div>\n\
      </fieldset>\n\
      <div id="activeTag"></div>\n\
      <div id="placeholder"></div>\n\
      </div>');


  // Genereal tag pop-up window properties  
  $("#meta_data_infos").dialog({
    autoOpen: false,
    width: 640,
    height: 400,
    modal: true,
    close: function() {
      $('#keyTable').html("");
      $('#addNewKey').hide();
      $('#meta_data_tags').off('tokenfield:createtoken');
      $('#importTags').off("click");
      $('#meta_data_tags').tokenfield('destroy');
      $('#meta_data_tags').val(""); 
    },
    buttons: {                                                                                                     
      Cancel: {                                                                                                      
        text: t('meta_data', 'Cancel'),                                                                              

    click: function() {                                                                                            
      $( this ).dialog( "close" );                                                                                 
    }                                                                                                              
      },
      Confirm: {                                                                                                     
        text: t('meta_data', 'Ok'),                                                                           
        click: function() {                                                                                            
          updateFileKeys( $('#meta_data_infosData').attr('data-id') );
          $( this ).dialog( "close");
        }                                                                                                              
      }                                                                                                            
    }
  });


  // Define button for adding a key
  // Clean up and add error mesage
  $('#newKeyButton').on("click", function() {
    var tag = $('#activeTag').data('tag');  
    var key = $('#newKey').val();
    if( $('#newKey').val() ){
    addsinglekey(tag,key,"");
    }
  });

});






function addsinglekey(tag,key,value){
    $.ajax({                                                                                                                   
      url: OC.filePath('meta_data', 'ajax', 'addSingleKey.php'),                                                                        
      async: false,                                                                                                            
      timeout: 2000,                                                                                                           

      data: {                                                                                                                  
        tagName: tag,                                                                                                       
      keyName: key                                                                                                            
      },                                                                                                                       

      type: "POST",                                                                                                            

      success: function(result) {
        var array=JSON.parse(result);
        $('#newKey').val('');
        $('#keyTable').append('<tr data-tag="'+ tag + '" class="keyRow visible"><td>'+key+'</td><td><input type="text" value="'+value+'" data-key="'+ key +'" data-keyid="'+array[0]['keyid']+'"></td></tr>');            
      },                                                                                                                       

      error: function(xhr, status) {                                                                                         
        window.alert(t('meta_data', 'Unable to create new key! Ajax error.'));                                                  
      }                                                                                                                      
    });              
}







// This function load tag names and unique IDs for the file
// Tag names are displayed in the tokenfield
// TODO error message
function loadFileTags(fileID){
  var tokens = [];
  $.ajax(  {
    url: OC.filePath('meta_data', 'ajax', 'loadfileinfo.php'),
    async: false,
    timeout: 2000,

    data: {
      type  : "tag",
    fileid: fileID
    },
    type: "POST",
    success: function(result) {
      array = JSON.parse(result);        
      for(var i=0;i < array.length; i++) {                                                                                    
        var token = { 
          value: array[i].tagid,
    label: array[i].descr
        }; 
        tokens.push(token);    
      } 
      $('#meta_data_tags').tokenfield('setTokens', tokens);
      array = []
    },
    error: function() {}
  });
  return tokens;
}







// This function first loads all the keys and creates a table.
// Then the key values are loaded with an additional ajax call
function loadKeysAndFileKeys(taglist, fileID){
  $.ajax({
    url: OC.filePath('meta_data', 'ajax', 'loadKeys.php'),
  async: false,
  timeout: 2000,

  data: {
    list: taglist 
  },

  success: function(result) {
    $('#keyTable').append(result);
  },

  error: function (xhr, status) {
    window.alert(t('meta_data', 'Could not load keys ajax error'));
  },

  type: "POST"});

  $.ajax({
    url: OC.filePath('meta_data', 'ajax', 'loadfileinfo.php'),
    async: false,
    timeout: 2000,

    data: {
      type  : "key",
    fileid: fileID 
    },

    success: function(result) {
      var values = JSON.parse(result); 
      if(values!=null){
        for(var i=0;i < values.length; i++) {
          $('input[data-keyid="' +values[i].keyid+ '"]').val(values[i].value);
        }
      }
    },

    error: function (xhr, status) {
      window.alert(t('meta_data', 'Could not load keys ajax error'));
    },

    type: "POST"});
}






// Write file tags when clicking 'OK'
// TODO: proper return functions
function updateFileTags(eventData, fileID){
  $.ajax({
    url: OC.filePath('meta_data', 'ajax', 'updatefileinfo.php'),
  async: false,
  timeout: 2000,

  data: {
    tagid: eventData.attrs.label,
  fileid: fileID
  },
  type: "POST",
  success: function(result) {
    var array = JSON.parse(result);                                                                                            

    var tokens = [];                                                                                                           
    var token = {                                                                                                              
      value: array[0].tagid,                                                                                                   
  label: array[0].descr                                                                                                    
    };                                                                                                                         
    tokens.push(token);                                                                                                        
    loadKeysAndFileKeys( tokens, fileID);                                                                                      
    displayKeys( tokens[0].value);
  },
  error: function() {}
  });
}




// Write file key/values when clicking 'OK'
// TODO: proper return functions
function updateFileKeys(file){
  var tagList = $('#meta_data_tags').tokenfield('getTokensList');
  var tagArray = tagList.split(', ');

  $('tr.keyRow').each( function() {
    var tag = $(this).attr('data-tag');
    if( jQuery.inArray( tag, tagArray) > -1) {
      var key = $(this).children('td').children('input').attr('data-keyid'); 
      var value = $(this).children('td').children('input').val(); 

      $.ajax({                                                                                                                       
        url: OC.filePath('meta_data', 'ajax', 'updatefileinfo.php'),                                                                 
        async: false,                                                                                                                
        timeout: 2000,                                                                                                               

        data: {                                                                                                                      
          fileid: file,
        tagid:  tag,        
        keyid:  key,
        val:    value        
        },                                                                                                                           
        type: "POST",                                                                                                                
        success: function() {},                                                                                                      
        error: function() {}                                                                                                         
      });                    
    }
  }); 
}



function removeTag(eventData, fileID){
  $.ajax({                                                                                                                       
    url: OC.filePath('meta_data', 'ajax', 'removefiletag.php'),                                                                 
  async: false,                                                                                                                
  timeout: 2000,                                                                                                               

  data: {                                                                                                                      
    tag:  eventData,     
  file: fileID   
  },                                                                                                                           
  type: "POST",                                                                                                                
  success: function() {},                                                                                                      
  error: function() {}                                                                                                         
  }); 
}

function displayKeys(eventData){
  $('.keyRow[data-tag="'+eventData+'"]').removeClass('hidden').addClass('visible');
  $('.keyRow:not([data-tag="'+eventData+'"])').removeClass('visible').addClass('hidden');
  $('#activeTag').attr('data-tag',eventData);
  $('#addNewKey').show();
}

function hideKeys(eventData){
  $('.keyRow[data-tag="'+eventData+'"]').remove();
  $('#addNewKey').hide();
}








function showFileInfo(fileName) {
  var infoContent = "";
  var fileID = -1;
  var directory = $('#dir').val();
  directory = (directory === "/") ? directory : directory + "/";


  $.ajax({
    url: OC.filePath('meta_data', 'ajax', 'getFileInfo.php'),
    async: false,
    timeout: 2000,

    data: {
      filePath: directory + fileName
    },

    type: "POST",

    success: function( result ) {
      var jsonResult = JSON.parse(result);

      infoContent = jsonResult.infos;
      fileID = jsonResult.fileid;
      $('#meta_data_infosData').attr('data-id',fileID);

      $('#meta_data_tags').tokenfield({
        autocomplete: {
          source:  function(request, response) {
            $.ajax({
              url: OC.filePath('meta_data', 'ajax', 'getTagFlat.php'),
              data: {
                term: request.term
              },

              success: function(data) {
                var returnString = data;
                var jsonResult = jQuery.parseJSON(returnString);
                response(jsonResult);
              },

              error: function (xhr, status) {
                window.alert(t('meta_data', 'Unable to get the tags! Ajax error.'));
              }
            })
          },
        minLength: 2,
        delay: 200
        },
          showAutocompleteOnFocus: false
      });           
    },

    error: function( xhr, status ) {
      infoContent = t('meta_data', 'Unable to retrieve tags for this file! Ajax error!');
    }


  });                                

  var taglist = loadFileTags(fileID);
  if(taglist)
    loadKeysAndFileKeys(taglist,fileID);

  var dialogTitle =  t('meta_data', 'Tags for') + ' "' + fileName + '"';
  $('#meta_data_infos').dialog( "option", "title", dialogTitle );


  // Install event handlers
  $('#meta_data_tags').on('tokenfield:createtoken', function(e) {
    updateFileTags(e, fileID);
    e.attrs.value=$('#activeTag').data('tag')
  });

  $('#meta_data_tags').on('tokenfield:removedtoken', function (e) {
    removeTag(e.attrs.value,fileID);
    hideKeys(e.attrs.value);         
  });


  $('#meta_data_infosData').html(infoContent);
  $('#meta_data_infos').dialog("open");

  $('#importTags').on("click", function() {
    var tag = $('#importTags').attr('class');
    var flag = 0;
    var arr = $('#meta_data_tags').tokenfield('getTokens');
    $.each(arr,  function(i){
      if(arr[i].label == tag){
        flag=1;
      }
    });

    if(!flag){
      $('#meta_data_tags').tokenfield('createToken', tag);
    }

    var url = OC.filePath('files', 'ajax', 'download.php') + '?files=' + encodeURIComponent(fileName) + '&dir=' + encodeURIComponent($('#dir').val());
    $('#placeholder').html("Reading MP3 tags. Please wait...")
    ID3.loadTags(url, function() {
      var data = ID3.getAllTags( url ); 
      $.each(data, function(key, value) {
        var found = 0;
        $.each( $('.keyRow[data-tag='+$('#activeTag').data('tag')+']').children('td').children('input[data-keyid]'), function()   {
          if( $(this).data('key').toUpperCase() == key.toUpperCase() ){
            $(this).val(value);
            found = 1;
          }
        });
        if (found == 0){
          addsinglekey($('#activeTag').data('tag'),key,value); 
        }
      });
      $('#placeholder').html("")
      displayKeys($('#activeTag').data('tag'));
    });
  });
}

