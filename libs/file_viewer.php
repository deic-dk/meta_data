<?php                                                                                                                               
                                                                                                                                    
                                                                                                                                    
class OC_meta_data_mainview 
{
  public function searchFiles($tagid) {                                                                           
    $sql = "SELECT fileid FROM *PREFIX*meta_data_docTags WHERE tagid = ?";
    $args = array($tagid);                                                                                    
    $query = \OCP\DB::prepare($sql);                                                                                       
    $output = $query->execute($args);                                                                                     

    while($row=$output->fetchRow()){
      $sql = "SELECT fileid,name,path FROM *PREFIX*filecache WHERE fileid=?";
      $args = array($row['fileid']);                                                                                    
      $query = \OCP\DB::prepare($sql);                                                                                       
      $output2 = $query->execute($args);                                                                                     
      $result[] = $output2->fetchRow();
    }    
    
    foreach ($result as $key => $row) {
      $fileid[$key]  = $row['fileid'];
      $name[$key] = $row['name'];
      $path[$key] = $row['path'];
    }
    array_multisort($name, SORT_ASC, $result);

    return $result; 
  }       

} 
