$(document).ready(
    $(function(){    
    
      // Toggle pop-ups on page click 
      $("body").click(function(){
        $("#tc_colors").hide();
        $("#addNewTag #test").hide();
      });

      // Load tags and 'all files' when page is loaded
      getTags();
      getAllFiles();

      // Show tag color chooser
      $('#tag_col').on('click', 'ul li span.tagcolor', function(e) {
        e.stopPropagation();
        var pos=$(this).offset();
        var h=$(this).height();
        var w=$(this).width();
        $('#tc_colors').css({ left: pos.left + w - 270, top: pos.top + h - 90 });
        $('#tc_colors').removeAttr('class');
        $('#tc_colors').addClass($(this).parents('li').attr('id'));
        $('#tc_colors').toggle();
      });

      // Change tag color on click
      $('#tc_colors').on("click", "li", function() {
        $('#tc_colors').hide();
        $.ajax({
          url: OC.filePath('meta_data', 'ajax', 'updateColor.php'),
          async: false,
          timeout: 200,
          data: {
            tagid: $(this).parents().attr('class'),
            color: $(this).children('i').attr('class'),
          },
          type: "POST",
          success: function(result) {
            getTags();
          },
          error: function() {
            alert("Failed to change tag color");
          }
        });
      });

      // Load keys and files when a tag is clicked
      $('div#tag_col').on('click', 'ul li', function(e) {
        if(e.ctrlKey){
          var multi = true;
        } else multi = false;
        loadKeysOnClick($(this), multi);
      });
      
      // Rename tag when tag is double clicked
      $('div#tag_col').on('dblclick', 'ul li', function(e) {
        $(this).children('span').addClass('hidden');
        $(this).children('input').removeClass('hidden');
        $(this).children('input').focus();
        $(this).on('focusout', 'input', function(){
          $(this).siblings('span').removeClass('hidden');
          $(this).addClass('hidden');
          if($(this).val() != ''){
            $.ajax({                                                                                                                                                                                                                    
              url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),                                                                                                                                                                      
              async: false,                                                                                                                                                                                                             
              timeout: 2000,                                                                                                                                                                                                            
              data: {                                                                                                                                                                                                                   
                tagOp: 'rename_tag',  
                tagId: $(this).parents('li').attr('id'),                                                                                                                                                                                                   
                tagName: $(this).val(),                                                                                                                                                                                                   
                tagState: "1",                                                                                                                                                                                                       
              },    
              type: "POST",  
              error: function(result){
                alert("Failed to rename tag");
              }
            });
            $(this).siblings('span#tagname').html( $(this).val() );
          } else {
            $(this).val($(this).siblings('span#tagname').html());
          }        
        });
      });


      // Add tag when add icon is clicked
      $('.addtag').click(function(){
        $('div#tag_col').append("<ul class=\"new\"><li id=\"\">"                                                 
          +"<span class=\"tagcolor hidden\"><i class=\"icon-tag tc_white\"></i></span>"
          +"<span id=\"tagname\"></span>"                                              
          +"<input class=\"\" type=\"text\" value=\"\" placeholder=\"New tag name\">"  
          +"<span class=\"deletetag\">&#10006;</span>"                                
          +"</li></ul>");
        $('div#tag_col ul.new li input').focus();
        $('div#tag_col ul.new li').children('span').addClass('hidden');
        $('div.meta_data_add.adddata').addClass('hidden');
        $('div#tag_col ul li').removeClass('active');
        $('div#tag_col ul.new li').addClass("active");
        $('div#tag_col').on('focusout', 'ul.new li input', function(){
          $(this).siblings('span#tagname').html( $(this).val() );
          $(this).siblings('span').removeClass('hidden');
          $(this).addClass('hidden');
          if($(this).val() != ''){
            $.ajax({                                                                                                                                                                                                                    
              url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),                                                                                                                                                                      
              async: false,                                                                                                                                                                                                             
              timeout: 2000,                                                                                                                                                                                                            
              data: {                                                                                                                                                                                                                   
                tagOp: 'new',                                                                                                                                                                                                        
              tagName: $(this).val(),                                                                                                                                                                                                   
              tagState: "1",                                                                                                                                                                                                       
              },                                                                                                                                                                                                                        
              type: "POST",                                                                                                                                                                                                             
              success: function(result) {               
                $('div#tag_col ul.new li').attr('id', $.parseJSON(result)['tagid']); 
                $('div#tag_col ul').removeClass('new');
                $('div.meta_data_add.adddata').removeClass('hidden');
                $('div#fileInfo').addClass('hidden');
                $('div#files_col').html('<div id=\"emptysearch\">No files found</div>');
                $('div#data_col').html('<div id=\"emptysearch\">No meta data defined</div>');
              },
              error: function(result){
                alert("Failed to add tag");
              }
            });
          } else {
            $('div#tag_col ul.new').remove();
          }         
        });
      });

      // Delete tag when delete button is pressed
      $('div#tag_col').on('click', '.deletetag', function(e) {
        e.stopPropagation();
        $( "#deleteConfirm" ).dialog( "open" );                                                            
        $('#deleteType').addClass('tag');
        $('#tagToDelete').html( $(this).siblings('#tagname').html() );
        var tempclass = $('#tagToDelete').attr('class');
        $('#tagToDelete').removeClass( tempclass );
        $('#tagToDelete').addClass( $(this).parents('li').attr('id'));
        $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane button:eq(0)').focus(); 
      }); 

      $( "#deleteConfirm" ).dialog({
        resizable: false,
        autoOpen: false,
        width: 320,
        height: 200,
        modal: true,
        buttons: {
          Cancel: {
            text: t('meta_data', 'Cancel'),
        click: function() {
          $( this ).dialog( "close" );
        }
          },
        Delete: {
          text: t('meta_data', 'Delete'),
        click: function() {
          $( this ).dialog( "close" );
          if($('#deleteType').hasClass('tag')) {  
            $.ajax({
              url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
              async: false,
              timeout: 2000,
              data: {
                tagOp: 'delete',
              tagId: $('#tagToDelete').attr('class')
              },
              type: "POST",
              success: function() {
                if( $('div#tag_col ul li[id='+ $('#tagToDelete').attr('class') +']').hasClass('active')){
                  $('div#data_col').html('');
                  getAllFiles();
                  $('div#fileInfo').addClass('hidden');
                  $('div#tag_col ul li.active').removeClass('active')
                }
                $('div#tag_col ul li[id='+ $('#tagToDelete').attr('class') +']').remove();
                $('div.meta_data_add.adddata').addClass('hidden');
              }, 
            });
          } else if ($('#deleteType').hasClass('key')) {
            $.ajax({
              url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
              async: false,
              timeout: 2000,
              data: {
                tagOp: 'delete_key',
              tagId: $('div#tag_col .active').attr('id'), 
              keyId: $('#tagToDelete').attr('class')
              },
              type: "POST",
              success: function() {
                $('div#data_col ul li[id='+ $('#tagToDelete').attr('class') +']').remove();
              }, 
            });
          }

        }
        }
        }
      });

      // -------------- End of tag events ------------------










      // Rename Key on double click
      $('div#data_col').on('dblclick', 'ul li span.keyname', function() {
        $(this).addClass('hidden');
        $(this).siblings('span').addClass('hidden');
        $(this).siblings('input.edit').removeClass('hidden');
        $(this).siblings('input.edit').focus();
        $(this).parents('li').on('focusout', 'input.edit', function(){
          $(this).siblings('span').removeClass('hidden');
          $(this).addClass('hidden');
          if($(this).val() != ''){
            $.ajax({                                                                                                                                                                                                                    
              url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),                                                                                                                                                                      
              async: false,                                                                                                                                                                                                             
              timeout: 2000,                                                                                                                                                                                                            
              data: {                                                                                                                                                                                                                   
                tagOp: 'rename_key',                                                                                                                                                                                                        
              tagId: $('div#tag_col .active').attr('id'),                                                                                                                                                                                                   
              keyId: $(this).parents('li').attr('id'),  
              newName: $(this).val(),                                                                                                                                                                                                   
              },                                                                                                                                                                                                                        

              type: "POST",                                                                                                                                                                                                             

              success: function(result) {                
              },
              error: function(result){
              }
            });
            $(this).siblings('span.keyname').html( $(this).val() );
          } else {
            $(this).val($(this).siblings('span.keyname').html());
          }        
        });
      });

      // Add key when add icon is clicked
      $('.adddata').click(function(){
        $('div#data_col').append("<ul class=\"new\"><li id=\"\">"
          +"<span class=\"keyname\"></span>"
          +"<input class=\"edit\" type=\"text\" value=\"\" placeholder=\"New key name\">"
          +"<span class=\"deletetag hidden\">&#10006;</span>"
          + "<input class=\"value hidden\" type=\"text\" value=\"\">"
          +"</li></ul>");
        $('div#data_col ul.new li input').focus();

        $('div#data_col').on('focusout', 'ul.new li input', function(){
          $(this).siblings('span.keyname').html( $(this).val() );
          $(this).siblings('span.keyname').removeClass('hidden');
          $(this).addClass('hidden');
          if($(this).val() != ''){
            $.ajax({                                                                                                                                                                                                                    
              url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),                                                                                                                                                                      
              async: false,                                                                                                                                                                                                             
              timeout: 2000,                                                                                                                                                                                                            
              data: {                                                                                                                                                                                                                   
                tagOp: 'new_key',                                                                                                                                                                                                        
              keyName: $(this).val(),                                                                                                                                                                                                   
              tagId: $('div#tag_col .active').attr('id')                                                                                                                                                                                                        
              },                                                                                                                                                                                                                        
              type: "POST",                                                                                                                                                                                                             
              success: function(result) {               
                $('div#data_col ul.new li').attr('id', $.parseJSON(result)['keyid']); 
                $('div#data_col ul.new li span.deletetag').removeClass('hidden'); 
                $('div#data_col ul.new li input.edit').val($.parseJSON(result)['tagname']); 
                $('div#data_col ul.new li input.value').addClass($.parseJSON(result)['keyid']); 
                if( $('div#files_col ul li.active').length ) {
                  $('div#data_col ul.new li input.value').removeClass('hidden');
                }
                $('div#data_col ul').removeClass('new');
              },
              error: function(result){
              }
            });
          } else {
            $('div#data_col ul.new').remove();
          }         
        });
      });

      // Delete key when delete button is pressed -- this function can possibly be reduced by merging it with deletetag() above
      $('div#data_col').on('click', '.deletetag', function(e) {
        e.stopPropagation();
        $( "#deleteConfirm" ).dialog( "open" );                                                            
        $('#deleteType').addClass('key');
        $('#tagToDelete').html( $(this).siblings('.keyname').html() );
        $('#tagToDelete').removeClass();
        var tempclass = $('#tagToDelete').attr('class');
        $('#tagToDelete').removeClass( tempclass );
        $('#tagToDelete').addClass( $(this).parents('li').attr('id'));
        $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane button:eq(0)').focus(); 
      }); 

      // add new tag in the fileinfo menu 
      $('div#fileInfo').on("click", "#taginfo #addNewTag", function(e) {
        e.stopPropagation();        
        addNewDropDown($('div#files_col .active').attr('id'));
      }); 


      // -------------- End of key events ------------------



      // Load meta data when a file is clicked
      $('div#files_col').on('click', 'ul li', function() {
        if($(this).hasClass('active')){
          $(this).removeClass('active');
          $('div#data_col ul li input.value').val('');
          $('div#fileInfo').addClass('hidden'); 
        } else {
          $('div#files_col li.active').removeClass("active");
          $('div#data_col input.value').removeClass('hidden');
          $('div#fileInfo').removeClass('hidden');
          $(this).addClass("active");
          $.ajax({                                                                                                                    
            url: OC.filePath('meta_data', 'ajax', 'loadfileinfo.php'),                                                                 
            async: false,                                                                                                             
            timeout: 200,                                                                                                             
            data: {
              type: "key",            
            fileid: $(this).attr('id'),                                                                                               
            },                                                                                                                        
            type: "POST",                                                                                                             
            success: function(result) { 
              $('div#data_col input').val('');
              var values = $.parseJSON(result);            
              if(values!=null){                                                                                                  
                for(var i=0;i < values.length; i++) {  
                  $('#data_col ul li input.'+values[i].keyid).val(values[i].value);
                }
              }
            },                                                                                                                        
            error: function( xhr, status ) {                                                                                          
            }                                                                                                                         
          });                                  

          getFileInfo( $(this).attr('id') );

        }
      });


      // Update file meta data info when unfocusing text field
      $('div#data_col').on('focusout', 'ul li input.value', function(){
        $.ajax({                                                                                                                                                                                                                    
          url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),                                                                                                                                                                      
          async: false,                                                                                                                                                                                                             
          timeout: 2000,                                                                                                                                                                                                            
          data: {                                                                                                                                                                                                                   
            tagOp:  'update_file_key',                                                                                                                                                                                                        
          tagId:  $('div#tag_col .active').attr('id'),                                                                                                                                                                                                   
          keyId:  $(this).parents('li').attr('id'),
          fileId: $('div#files_col ul li.active').attr('id'),  
          newName:$(this).val(),                                                                                                                                                                                                   
          },                                                                                                                                                                                                                        
          type: "POST",                                                                                                                                                                                                             
          success: function(result) {                
          },
          error: function(result){
          }
        });
      });


      // end here     
    })
);



function getTags() {
  var tempids = [];
  $('#tag_col ul li.active').each( function() {
    tempids.push( $(this).attr('id') );
  });
  $.ajax({
    url: OC.filePath('meta_data', 'ajax', 'getTags.php'),
    async: false,
    timeout: 200,
    type: "POST",
    success: function(result) {
      $('#meta_data_table #tag_col').html(result);
    },
  }); 
  tempids.forEach( function(index) {
    $('#tag_col ul li#'+index).addClass('active');
  });
}


function getFileInfo(fileid){
  $.ajax({
    url: OC.filePath('meta_data', 'ajax', 'getFileInfo.php'),
  async: false,
  timeout: 2000,
  data: {
    fileId: fileid
  },
  type: "POST",
  success: function( result ) {
    $('div#fileInfo').html( $.parseJSON(result) );
  },
  error: function( xhr, status ) {
  }
  }) 
}

function getAllFiles(){
  $.ajax({
    url: OC.filePath('meta_data', 'ajax', 'searchfiles.php'),
  async: false,
  timeout: 200,
  type: "POST",
  success: function(result) {
    $('#meta_data_table #files_col').html(result);
  },
  error: function( xhr, status ) {
  }       
  }); 
  var items = $('div#files_col ul li'); 
  items.each( function() {

    var width = $(this).width()-$(this).children('span#tags').width();

    $(this).children('span#name').html(start_and_end( $(this).data('original'), width));  
  });
}

function start_and_end(str, width) {
  if (str.length*8 > width) {
    return str.substr(0, 15) + '...' + str.substr(str.length-10, str.length);
  }
  return str;
}

function addNewDropDown(file){
  $('#addNewTag').toggleClass('active');                                               
  $.ajax({
    url: OC.filePath('meta_data', 'ajax', 'getTags.php'),
    async: false,
    timeout: 200,
    data: {
      type: "drop"
    },
    type: "POST",
    success: function(result) {
      $('#addNewTag #test').html('<ul><li id="newTag"><input type=\"text\" value=\"\" placeholder=\"new tag\"></li></ul>');
      $('#addNewTag #test').append(result);
      $('#addNewTag #test').toggle();                                                         
    },
    error: function() {
    }                            
  }); 


  $('div#fileInfo').on("click", "input", function(e) {
    e.stopPropagation();
  });

  $('div#fileInfo').on("focusout", "input", function() {
    if($(this).val() != ''){
      $.ajax({                                                                                                                                                                                                                    
        url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),                                                                                                                                                                      
        async: false,                                                                                                                                                                                                             
        timeout: 2000,                                                                                                                                                                                                            
        data: {                                                                                                                                                                                                                   
          tagOp: 'new',                                                                                                                                                                                                        
        tagName: $(this).val(),                                                                                                                                                                                                   
        tagState: "1",                                                                                                                                                                                                       
        },                                                                                                                                                                                                                        
        type: "POST",                                                                                                                                                                                                             
        success: function(result) {               
          $.ajax({
            url: OC.filePath('meta_data', 'ajax', 'updatefileinfo.php'),
          async: false,
          timeout: 200,
          data: {
            fileid: file,
          tagid: $.parseJSON(result)['tagid'] 
          },
          type: "POST",
          success: function(result) {
            getTags();
            getFileInfo(file);
            $('#addNewTag #test').hide();
          },
          error: function() {
          }          
          });
        },
          error: function(){
          }
      });

    };
  });
  $('div#fileInfo').on("click", "div#test ul li" , function() {
    $.ajax({
      url: OC.filePath('meta_data', 'ajax', 'updatefileinfo.php'),
      async: false,
      timeout: 200,
      data: {
        fileid: file,
      tagid: $(this).attr('id') 
      },
      type: "POST",
      success: function(result) {
        getFileInfo( $('div#files_col .active').attr('id') );
        $('#addNewTag div').toggle();
      },
      error: function() {
      }          
    });
  });
}

function loadKeysOnClick(item, multi){ 
  $('div#fileInfo').addClass('hidden');
  if($(item).hasClass('active')){
    $(item).removeClass('active');
    if(!multi){
      $('#meta_data_table #data_col').html("");
      $('.adddata').addClass('hidden');
    }
  } else {
    if(!multi){
      $('div#tag_col li.active').removeClass("active");
      $('.adddata').removeClass('hidden');  
    }
    $(item).addClass("active");
  }

  $('#meta_data_table #data_col').html("");
  var tagids = [];
  $('div#tag_col ul li.active').each(function(index) {  
    if(multi){ 
      $('#meta_data_table #data_col').append("<div id=\"tagtitle\">"+$(this).children('span#tagname').text()+"</div>");
      $('.adddata').addClass('hidden');
    }
    tagids[index]= $(this).attr('id');
    $.ajax({                                                                                                                    
      url: OC.filePath('meta_data', 'ajax', 'loadKeys_new.php'),                                                                 
      async: false,                                                                                                             
      timeout: 200,                                                                                                             
      data: {
        tagid: $(this).attr('id')            
      },                                                                                                                        
      type: "POST",                                                                                                             
      success: function(result) { 
        $('#meta_data_table #data_col').append(result);
      },                                                                                                                        
      error: function( xhr, status ) {                                                                                          
      }                                                                                                                         
    });
  })

  if(tagids.length > 0){
    $.ajax({
      url: OC.filePath('meta_data', 'ajax', 'searchfiles.php'),
      async: false,
      timeout: 200,
      data: {
        tagids: JSON.stringify(tagids)
      },
      type: "POST",
      success: function(result) {
        var active = $('#files_col ul li.active').attr('id'); 
        $('#meta_data_table #files_col').html(result);
        if(typeof active != 'undefined'){
          $('#files_col ul li[id="'+active+'"]').trigger('click');
          $('#files_col ul li[id="'+active+'"]').addClass('active'); 
        }
      },
      error: function( xhr, status ) {
      }                            
    });
  } else {
    getAllFiles();
  }
  var items = $('div#files_col ul li'); 
  items.each( function() {
    var width = $(this).width()-$(this).children('span#tags').width();
    $(this).children('span#name').html(start_and_end( $(this).data('original'), width));  
  });
};


