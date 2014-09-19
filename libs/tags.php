<?php

namespace OCA\meta_data;

class tags {

  public function getAllTags($userid, $public) {
    $output=$this->searchTag('%', $userid, $public);

    for($i=0;$i<count($output);$i++){  
      $result[$i] = array(
        'key' => '-1',
        'title' => $output[$i]['descr'],
        'expanded' => false,
        'class' => 'global',
        'children' => array()
      );

      $childrenData = array();

      $output2 = $this->searchKey($output[$i]['tagid'],'%');
      for($j=0;$j<count($output2);$j++){
        $childrenData[] = array('key'=> '1', 'title'=>$output2[$j]['descr'], 'class'=>'global','icon'=>'/apps/meta_data/img/icon_document.png');
      }
      $result[$i]['children'] = $childrenData;
    }

    return $result;
  }


  public function searchTag($descr, $userid, $public) {                                                                           
    $sql = "SELECT tagid,descr FROM *PREFIX*meta_data_tags WHERE descr LIKE ? AND owner LIKE ? AND public LIKE ?";
    $args = array($descr,$userid,$public);                                                                                    
    $query = \OCP\DB::prepare($sql);                                                                                       
    $output = $query->execute($args);                                                                                     

    while($row=$output->fetchRow()){
      $result[] = $row;
    }    
    return $result;
  }       

  public function searchKey($tagid,$descr) {                                                                           
    $sql = "SELECT keyid,descr FROM *PREFIX*meta_data_keys WHERE tagid=? AND descr LIKE ? ORDER BY keyid";
    $args = array($tagid,$descr);                                                                                    
    $query = \OCP\DB::prepare($sql);                                                                                       
    $output = $query->execute($args);                                                                                     

    while($row=$output->fetchRow()){
      $result[] = $row;
    }    
    return $result;
  }  

  public function searchFiles($tagid) {                                                                           
    $sql = "SELECT fileid FROM *PREFIX*meta_data_docTags WHERE tagid = ?";
    $args = array($tagid);                                                                                    
    $query = \OCP\DB::prepare($sql);                                                                                       
    $output = $query->execute($args);                                                                                     

    while($row=$output->fetchRow()){
      $sql = "SELECT * FROM *PREFIX*filecache WHERE fileid=? ";
      $args = array($row['fileid']);                                                                                    
      $query = \OCP\DB::prepare($sql);                                                                                       
      $output2 = $query->execute($args);                                                                                     
      $result[] = $output2->fetchRow();
    }    
    return $result;
  }       






  public function newKey($tagid, $key) {                                                                   
    if(trim($key) === '') {                   
      return FALSE;                                                                                                      
    }                                                                                                                      

//    if(count($this->searchKey($tagid)) != 0 ) {                                                                
//      return FALSE;                                                                                                      
//    }                                                                                                                      

    $sql = "INSERT INTO *PREFIX*meta_data_keys (tagid,descr) VALUES (?,?)";                                                          
    $args = array($tagid,$key);                                                                                              
    $query = \OCP\DB::prepare($sql);                                                                                       
    $resRsrc = $query->execute($args);                                                                                     
  }



  public function newTag($descr, $userid, $public, $keylist) {                                                                   
    if(trim($descr) === '') {                   
      return FALSE;                                                                                                      
    }                                                                                                                      

    if(count($this->searchTag($descr,$userid, "0")) != 0 ) {                                                                
      return FALSE;                                                                                                      
    }                                                                                                                      

    if(count($this->searchTag($descr,"%", "1")) != 0 ){
      return FALSE;
    }

    $sql = "INSERT INTO *PREFIX*meta_data_tags (descr,owner,public) VALUES (?,?,?)";                                                          
    $args = array($descr,$userid,$public);                                                                                              
    $query = \OCP\DB::prepare($sql);                                                                                       
    $resRsrc = $query->execute($args);                                                                                     

    $tag = $this->searchTag($descr,$userid, "%"); 

    if(!empty($keylist)){
    $keyarray = explode(', ', $keylist);
    foreach($keyarray as $key){
      $this->newKey($tag[0]['tagid'],$key);
    }
    }
    return true;
  }         


  public function alterTag($oldtagname, $newtagname, $userid, $tagstate, $keylist) {

    if($newtagname === '') {

      $this->deleteTag($oldtagname, $userid);

    } else {

      $tag=$this->searchTag($oldtagname,$userid, "%");
      if(count($tag) != 1 ) {                                                                
        return FALSE;                                                                                                      
      }                                                                                                                      

      $sql = 'UPDATE *PREFIX*meta_data_tags SET descr=?, public=? WHERE tagid=?';
      $args = array($newtagname, $tagstate, $tag[0]['tagid']);
      $query = \OCP\DB::prepare($sql);
      $resRsrc = $query->execute($args);


      $newkeyarray = explode(', ', $keylist);
      $keys = $this->searchKey($tag[0]['tagid'], "%");
      foreach($keys as $key){
        $oldkeyarray[]=$key['descr'];
      } 

      $diff = array_diff($oldkeyarray,$newkeyarray);
      foreach($diff as $key){ 
        $this->deleteKeys($tag[0]['tagid'], $key);
      }

      $diff = array_diff($newkeyarray,$oldkeyarray);
      if(count($diff) > 0){
        foreach($diff as $key){ 
          $this->newKey($tag[0]['tagid'],$key);
        }  
      }

    }
    return $key;
  }

  public function deleteKeys($tagid, $descr){
    $sql = 'DELETE FROM *PREFIX*meta_data_keys WHERE tagid=? AND descr LIKE ?';
    $args = array($tagid, $descr);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);
  }

  public function deleteTag($descr, $userid){
    $tag = $this->searchTag($descr, $userid, "%");
      if(count($tag) != 1 ) {                                                                
        return FALSE;                                                                                                      
      }                                                                                                                      
    $this->deleteKeys($tag[0]['tagid'], '%');
    $sql = 'DELETE FROM *PREFIX*meta_data_tags WHERE tagid=?';
    $args = array($tag[0]['tagid']);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);

    $sql = 'DELETE FROM *PREFIX*meta_data_docTags WHERE tagid=?';
    $args = array($tag[0]['tagid']);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);
  
  }


  public function searchTagbyID($tagid){
    $sql = "SELECT descr FROM *PREFIX*meta_data_tags WHERE tagid=?";                                                          
    $args = array($tagid);                                                                                              
    $query = \OCP\DB::prepare($sql);                                                                                       
    $resRsrc = $query->execute($args);
    
    $result=$resRsrc->fetchRow();
    return $result['descr'];
  }
  
  public function loadFileTags($fileid){
    $sql = "SELECT tagid FROM *PREFIX*meta_data_docTags WHERE fileid=?";                                                          
    $args = array($fileid);                                                                                              
    $query = \OCP\DB::prepare($sql);                                                                                       
    $resRsrc = $query->execute($args);

    while($row=$resRsrc->fetchRow()){
      $result[] = array( 'tagid' => $row['tagid'],
                         'descr' => $this->searchTagbyID($row['tagid']));
    }     
    
    return $result;
  }
  
  public function loadFileKeys($fileid){
    $sql = "SELECT keyid,value FROM *PREFIX*meta_data_docKeys WHERE fileid=?";                                                          
    $args = array($fileid);                                                                                              
    $query = \OCP\DB::prepare($sql);                                                                                       
    $resRsrc = $query->execute($args);

    while($row=$resRsrc->fetchRow()){
      $result[] = array( 'value' => $row['value'],
                         'keyid' => $row['keyid']);
    }     
    
    return $result;
  }
    
  public function updateFileTags($tagname, $userid, $fileid){
    $tag=$this->searchTag($tagname, $userid, "%");
    if(count($tag) == 0){
        $this->newTag($tagname, $userid, "0", "" );
        $tag = $this->searchTag($tagname, $userid, "%");
    }

    $sql = 'SELECT tagid FROM *PREFIX*meta_data_docTags WHERE fileid=? AND tagid=?';
    $args = array($fileid, $tag[0]['tagid']);
    $query = \OCP\DB::prepare($sql);
    $output = $query->execute($args);
    while($row=$output->fetchRow()){
      $result[] = $row;
    }    

    if(count($result) == 0){
      $sql = 'INSERT INTO *PREFIX*meta_data_docTags (fileid,tagid) VALUES (?,?)';
      $args = array($fileid, $tag[0]['tagid']);
      $query = \OCP\DB::prepare($sql);
      $output = $query->execute($args);
    }
    

    return $tag;
  }

  public function removeFileTag($tagid,$fileid){
      $sql = 'DELETE FROM *PREFIX*meta_data_docTags WHERE fileid=? AND tagid=?';
      $args = array($fileid, $tagid);
      $query = \OCP\DB::prepare($sql);
      $output = $query->execute($args);
  }

  public function removeFileKey($tagid,$fileid){
      $sql = 'DELETE FROM *PREFIX*meta_data_docKeys WHERE fileid=? AND tagid=?';
      $args = array($fileid, $tagid);
      $query = \OCP\DB::prepare($sql);
      $output = $query->execute($args);
  }




  public function updateFileKeys ($fileid, $tagid, $keyid, $value){
    if(is_null($value)){
      $value="";
    }

    $sql = 'SELECT id FROM *PREFIX*meta_data_docKeys WHERE fileid=? AND keyid=?';
    $args = array($fileid, $keyid);
    $query = \OCP\DB::prepare($sql);
    $output = $query->execute($args);
    while($row=$output->fetchRow()){
      $result[] = $row;
    } 
    if(count($result) == 0 ) {       
      $sql = 'INSERT INTO *PREFIX*meta_data_docKeys (fileid,tagid,keyid,value) VALUES (?,?,?,?)';
      $args = array($fileid, $tagid, $keyid ,$value);
      $query = \OCP\DB::prepare($sql);
      $resRsrc = $query->execute($args);
    } else if (count($result) == 1 ) {
      $sql = 'UPDATE *PREFIX*meta_data_docKeys SET value=? WHERE fileid=? AND keyid=?';
      $args = array($value, $fileid, $keyid);
      $query = \OCP\DB::prepare($sql);
      $resRsrc = $query->execute($args);
    } else {
      return FALSE;
    }
    
  }
}
