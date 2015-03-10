$(document).ready(
    $(function(){    
      $('#searchinput').on({
        keydown: function(e){
          if(e.keyCode == 13){
            $(this).focusout();
          }
        },
        focusout: function() {
          if($(this).val() != ""){
          $.ajax({
            url: OC.filePath('meta_data', 'ajax', 'search.php'),
            async: false,
            timeout: 200,
            data: { data: $(this).val() },
            type: "POST",
            success: function(result) {
              var data=$.parseJSON(result);  
              //alert(data);
              if(data[0] == 'tag'){
                loadKeysOnClick($('#tag_col ul li[id='+data[1]+']'),false);
              } else if(data[0] == 'key'){
                loadKeysOnClick($('#tag_col ul li[id='+data[1]+']'),false);
                for(i=2; i<data.length; i++){
                  loadKeysOnClick($('#tag_col ul li[id='+data[i]+']'), true);
                }
              }
            },
            error: function() {}
          })
          }
        }
      }); 
    })
); 
