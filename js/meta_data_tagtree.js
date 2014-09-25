
$(document).ready(

    $(function(){    
      var dataPath = OC.filePath('meta_data', 'ajax', 'getTags.php');

      $("#tagstree").fancytree({

//        renderNode: function(event, data) {
          // Optionally tweak data.node.span
//          var nodeClass = data.node.data.class;
//
//          if(nodeClass === 'global') {
//            var globalIconCSS = "URL(" + OC.filePath('meta_data', 'img', 'icon_small.png') + ")";
//            var span = $(data.node.span);
//            var findResult = span.find("> span.fancytree-icon");
//            findResult.css("backgroundImage", globalIconCSS);
//            findResult.css("backgroundPosition", "0 0");
//          }
//        },

        source: {
          url: dataPath,
          datatype: "json"
        },

        checkbox: false,

        activate: function(event, data) {
          if(data.node.getLevel() == 1){
            $.ajax({
              url: OC.filePath('meta_data', 'ajax', 'searchfiles.php'),
              async: false,
              timeout: 200,

              data: {
                tagid: data.node.data.tagid,
              },

              type: "POST",

              success: function(result) {
                $('#filestable_leg').html('Files tagged with: '+data.node.title);
                $('#filestable').html(result );
                $('#meta_data_emptylist').html("");

              },

              error: function( xhr, status ) {
              }                            
            });                     
          }   
        },

        deactivate: function(event, data) {
          var children = data.node.getChildren();                                                                                      
          if(children){ 
            children.forEach(function(child) {                                                                                          
              child.title = child.data.otitle;                                                                                          
            })   
          }
          data.node.setExpanded(flag=false);
          data.node.render(force=true, deep=true);                                                                                 
          $('#filestable').html("");


        }
 
      });


      $('#filestable').on('click', 'td', function() {
        $('td.active').removeClass("active");
        var actiNode = $("#tagstree").fancytree("getActiveNode");                                                               
        var children = actiNode.getChildren(); 

        children.forEach(function(child) {
          child.title = child.data.otitle;
        })
        $(this).addClass("active");
        actiNode.setExpanded(); 
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
            var values = JSON.parse(result);            
            var actiNode = $("#tagstree").fancytree("getActiveNode");                                                               
            var children = actiNode.getChildren(); 

            children.forEach(function(child) {
              if(values!=null){                                                                                                  
                for(var i=0;i < values.length; i++) {  
                  if(child.data.keyid == values[i].keyid) 
              child.title = child.data.otitle+": "+values[i].value;                                                                              
                }
              }
            })
            actiNode.render(force=true, deep=true);                                                                                 
          },                                                                                                                        

          error: function( xhr, status ) {                                                                                          
          }                                                                                                                         
        });                                  
      });



      function updateStatusBar( t ) {
        $('#notification').html(t);
        $('#notification').slideDown();
        window.setTimeout(
            function(){
              $('#notification').slideUp();
            }, 5000);            
      }



      $("#editTag").button({text:true}).bind('click',function(){                                                                  
        var node = $("#tagstree").fancytree("getActiveNode") 
        var children = node.getChildren()
        var keys = new Array()
        for (child in children){
          keys[child] = children[child].data.otitle
        }
      if(node.getLevel() == 1){
        $( "#renameTag" ).dialog( "open" );                                                            
        $('#tagName').val(node.title);
        $('#tokenfield').tokenfield();
        $('#tokenfield').tokenfield('setTokens', keys);
        $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane button:eq(0)').focus(); 
      }
      }); 

      $("#renameTag").dialog({
        autoOpen: false,
        height: 400,
        width: 350,
        modal: true,
        resizable: false,
        buttons: {
          Cancel: {
            text: t('meta_data', 'Cancel'),
        click: function() {
          $( this ).dialog( "close" );
        }
          },          

        Confirm: {
          text: t('meta_data', 'Confirm'),
        click: function() {
          renameTag();
        }
        }


        },

        close: function() {
          // allFields.val( "" ).removeClass( "ui-state-error" );
        }
      });

      $("#renameTag").on('keypress', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code === 13) {
          e.preventDefault();
          renameTag();
        }
      });

      function renameTag() {
        var node = $("#tagstree").fancytree("getActiveNode");
        var isPublic = 0;
        if ($('.tagPublic:checked')){
          isPublic = 1;
        }; 
        var keys = $('#tokenfield').tokenfield('getTokensList');


        $.ajax({
          url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
          async: false,
          timeout: 2000,

          data: {
            tagOp: 'rename',
          tagName: tagName.value,
          oldtagname: node.title,
          tagState: isPublic, 
          keyList: keys
          },

          type: "POST",

          success: function(result) {
            var resultData = jQuery.parseJSON(result);

            if(resultData.result === 'OK') {
              $("#tagstree").fancytree("getActiveNode").remove(); 

              var keyarray = keys.split(', ');
              var childrenArray = new Array();

              for(key in keyarray) {                                                                                   
                childrenArray[key] = {'key':'1', 'title':keyarray[key], 'class':'global','icon':'/apps/meta_data/img/icon_document.png'};
              } 
              var nodeData = {
                'title': resultData.title,
                'key': parseInt(resultData.key),
                'class': resultData.class,
                'children': childrenArray
              };

              if(nodeData.title != ""){
                var rootNode = $("#tagstree").fancytree("getRootNode");
                var newNode = rootNode.addChildren(nodeData);                                                    
                newNode.setActive(true);
                newNode.setExpanded();
              }

              updateStatusBar(t('meta_data', 'Rename done!'));
            } else {
              updateStatusBar(t('meta_data', 'Unable to rename! Data base error!'));
            }
          },

          error: function( xhr, status ) {
            updateStatusBar(t('meta_data', 'Unable to rename! Ajax error!'));
          }                            
        });                        

        $("#renameTag").dialog( "close" );                        
      }


      $("#addTag").button({text:true}).bind('click',function(){                                                                  

        $('#newtokenfield').tokenfield();


        $( "#createTag" ).dialog( "open" );                                                             
        $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane button:eq(0)').focus(); 
      }); 

      $( "#createTag" ).dialog({
        autoOpen: false,
        height: 400,
        width: 350,
        modal: true,
        resizable: false,
        buttons: {

          Cancel: {
            text: t('meta_data', 'Cancel'),
        click: function() {
          $( this ).dialog( "close" );
        }
          },

        Confirm: {
          text: t('meta_data', 'Confirm'),
        click: function() {
          insertTag();
        }
        }

        },

        close: function() {
          allFields.val("").removeClass( "ui-state-error" );
        }
      });

      $("#createTag").on('keypress', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code === 13) {
          e.preventDefault();
          insertTag();
        }
      });

      function insertTag() {

        var descr = newTagName.value;
        var isPublic = 0;
        if ($('.tagPublic:checked')){
          isPublic = 1;
        }; 
        var keys = $('#newtokenfield').tokenfield('getTokensList');

        $.ajax({
          url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
          async: false,
          timeout: 2000,

          data: {
            tagOp: 'new',
          tagName: descr,
          tagState: isPublic,
          keyList: keys
          },

          type: "POST",


          success: function( result ) {                                
            var resArray = jQuery.parseJSON(result);
            if(resArray.result === 'OK') {
              var node = $("#tagstree").fancytree("getRootNode");

              var childrenArray = new Array();

              if(keys){
                var keyarray = keys.split(', ');

                for(key in keyarray) {                                                                                   
                  childrenArray[key] = {'key':'1', 'title':keyarray[key], 'class':'global','icon':'/apps/meta_data/img/icon_document.png'};
                } 
              }
              var nodeData = {
                'title': resArray.title,
                'key': parseInt(resArray.key),
                'class': resArray.class,
                'children': childrenArray
              };





              var newNode = node.addChildren(nodeData);
              node.setExpanded(true);
              newNode.setActive(true);
              newNode.setExpanded();

              updateStatusBar(t('meta_data', 'Tag created successfully!'));
            } else {
              updateStatusBar(t('meta_data', 'Unable to create tag! Data base error!'));
            }
          },

          error: function( xhr, status ) {
            updateStatusBar(t('meta_data', 'Unable to create tag! Ajax error!'));
          }                            
        });                        

        $('#createTag').dialog( "close" );                        
      }



      $("#deleteTag").button({text:true}).bind('click',function(){                                                                  
        var node = $("#tagstree").fancytree("getActiveNode"); 
        if(node.getLevel() ==1){
          $( "#deleteConfirm" ).dialog( "open" );                                                            
          $('#tagToDelete').html(node.title);
          $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane button:eq(0)').focus(); 
        }
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
          updateStatusBar(t('meta_data', 'Operation canceled: No deletion occurred!'));
        }
          },

        Delete: {
          text: t('meta_data', 'Delete'),
        click: function() {
          $( this ).dialog( "close" );

          var node = $("#tagstree").fancytree("getActiveNode")
            $.ajax({
              url: OC.filePath('meta_data', 'ajax', 'tagOps.php'),
              async: false,
              timeout: 2000,

              data: {
                tagOp: 'delete',
              tagName: node.title
              },

              type: "POST",

              success: function(result) {
                var resArray = jQuery.parseJSON(result);

                if(resArray.result === 'OK') {
                  $("#tagstree").fancytree("getActiveNode").remove();
                  updateStatusBar(t('oclife', 'Tag removed successfully!'));
                } else {
                  updateStatusBar(t('oclife', 'Tag not removed! Data base error!'));
                }
              },
              error: function( xhr, status ) {
                updateStatusBar(t('oclife', 'Tags not removed! Ajax error!'));
              }
            });                    
        }
        }
        }
      });

      // end edit by Christian



      $("#filePath").dialog({
        resizable: false,
        autoOpen: false,
        width: 320,
        height: 200,
        modal: true,
        buttons: {
          "Close": function() {
            $( this ).dialog( "close" );
          }
        }
      });
      }));
