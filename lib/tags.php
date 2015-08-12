<?php

namespace OCA\meta_data;

class tags {

  public function getAllTags($userid, $public) {
    $output=$this->searchTag('%', $userid, $public);

    for($i=0;$i<count($output);$i++){
      $result[$i] = array(
        'title' => $output[$i]['descr'],
        'expanded' => false,
        'class' => 'global',
        'tagid' => $output[$i]['tagid'],
        'icon'=>'/apps/meta_data/img/icon_tag.png',
        'children' => array()
      );

      $childrenData = array();

      $output2 = $this->searchKey($output[$i]['tagid'],'%');
      for($j=0;$j<count($output2);$j++){
        $childrenData[] = array('title'=>$output2[$j]['descr'], 'keyid'=>$output2[$j]['keyid'], 'otitle'=>$output2[$j]['descr'], 'class'=>'global','icon'=>''); ///apps/meta_data/img/icon_document.png');
      }
      $result[$i]['children'] = $childrenData;
    }

    return $result;
  }

  public static function searchTag($descr, $userid, $public='%') {
    $sql = "SELECT tagid,descr,color FROM *PREFIX*meta_data_tags WHERE descr LIKE ? AND owner LIKE ? AND public LIKE ? ORDER BY color";
    $args = array($descr, $userid, $public);
    $query = \OCP\DB::prepare($sql);
    $output = $query->execute($args);

    $result = array();
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

    if($output->rowCount() > 0){
      while($row=$output->fetchRow()){
        $result[] = $row;
      }
      return $result;

    } else {
      return FALSE;
    }
  }

  public function searchMetadata($query,$user) {
    $sql = "SELECT fileid, tagid, keyid, value FROM *PREFIX*meta_data_docKeys WHERE value LIKE ?";
    $args = array($query);
    $query = \OCP\DB::prepare($sql);
    $output = $query->execute($args);

    if($output->rowCount() > 0){
      while($row=$output->fetchRow()){
		$result[] = $row;
      }
      return $result;

    } else {
      return FALSE;
    }
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

    $key = $this->searchKey($tagid, $key);
    return $key;
  }



  public function newTag($descr, $userid, $public, $color){
    if(trim($descr) === '') {
			\OCP\Util::writeLog('meta_data', 'Need tag name', \OC_Log::ERROR);
      return FALSE;
    }

    if(count($this->searchTag($descr,$userid)) != 0 ){
    	\OCP\Util::writeLog('meta_data', 'Tag exists: '.$descr, \OC_Log::ERROR);
      return FALSE;
    }

    $sql = "INSERT INTO *PREFIX*meta_data_tags (descr,owner,public,color) VALUES (?,?,?,?)";
    $args = array($descr,$userid,$public,$color);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);

    $tag = $this->searchTag($descr,$userid);

    return $tag;
  }


  public function alterTag($tagid, $tagname, $userid, $tagstate) {

    $sql = 'UPDATE *PREFIX*meta_data_tags SET descr=?, public=? WHERE tagid=?';
    $args = array($tagname, $tagstate, $tagid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);


    return true;
  }

  public function updateColor($tagid, $color){
    $sql = 'UPDATE *PREFIX*meta_data_tags SET color=? WHERE tagid=?';
    $args = array($color, $tagid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);


    return TRUE;

  }


  public function alterKey($tagid,$keyid, $keyname, $userid) {

    $sql = 'UPDATE *PREFIX*meta_data_keys SET descr=? WHERE tagid=? AND keyid=?';
    $args = array($keyname, $tagid,$keyid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);


    return TRUE;
  }

  public function deleteKeys($tagid, $keyid){
    $sql = 'DELETE FROM *PREFIX*meta_data_keys WHERE tagid=? AND keyid LIKE ?';
    $args = array($tagid, $keyid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);
  }

  public static function deleteTag($tagid, $userid){
    //$this->deleteKeys($tagid, '%');
    $sql = 'DELETE FROM *PREFIX*meta_data_tags WHERE tagid=? AND owner=?';
    $args = array($tagid, $userid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);

    $sql = 'DELETE FROM *PREFIX*meta_data_docTags WHERE tagid=?';
    $args = array($tagid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);

    return TRUE;
  }


  public function searchTagbyID($tagid){
    $sql = "SELECT descr,color FROM *PREFIX*meta_data_tags WHERE tagid=?";
    $args = array($tagid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);

    $result=$resRsrc->fetchRow();
    return $result;
  }

  public function searchKeybyID($keyid){
    $sql = "SELECT descr FROM *PREFIX*meta_data_keys WHERE keyid=?";
    $args = array($keyid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);

    $result=$resRsrc->fetchRow();
    return $result;
  }

  public function loadFileTags($fileid){
    $sql = "SELECT tagid FROM *PREFIX*meta_data_docTags WHERE fileid=?";
    $args = array($fileid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);

    $result = [];
    while($row=$resRsrc->fetchRow()){
      $tag = $this->searchTagbyID($row['tagid']);
      $result[] = array( 'tagid' => $row['tagid'],
                         'descr' => $tag['descr'],
                         'color' => $tag['color']);
    }

    return $result;
  }

  public function loadFileKeys($fileid,$tagid){
    $result = array();
    $sql = "SELECT keyid,value FROM *PREFIX*meta_data_docKeys WHERE fileid=? AND tagid=?";
    $args = array($fileid, $tagid);
    $query = \OCP\DB::prepare($sql);
    $resRsrc = $query->execute($args);

    while($row=$resRsrc->fetchRow()){
      $result[] = array( 'value' => $row['value'],
                         'keyid' => $row['keyid']);
    }

    return $result;
  }

  public function updateFileTags($tagid, $userid, $fileid){
    $result = array();

    $sql = 'SELECT tagid FROM *PREFIX*meta_data_docTags WHERE fileid=? AND tagid=?';
    $args = array($fileid, $tagid);
    $query = \OCP\DB::prepare($sql);
    $output = $query->execute($args);
    while($row=$output->fetchRow()){
      $result[] = $row;
    }

    if(count($result) == 0){
      $sql = 'INSERT INTO *PREFIX*meta_data_docTags (fileid,tagid) VALUES (?,?)';
      $args = array($fileid, $tagid);
      $query = \OCP\DB::prepare($sql);
      $output = $query->execute($args);
    }


    return $tag;
  }

  public static function removeFileTag($tagid,$fileid){
      $sql = 'DELETE FROM *PREFIX*meta_data_docTags WHERE fileid=? AND tagid=?';
      $args = array($fileid, $tagid);
      $query = \OCP\DB::prepare($sql);
      $output = $query->execute($args);
  }

  public function removeFileKey($tagid,$fileid,$keyid){
      $sql = 'DELETE FROM *PREFIX*meta_data_docKeys WHERE fileid LIKE ? AND tagid=? AND keyid=?';
      $args = array($fileid, $tagid, $keyid);
      $query = \OCP\DB::prepare($sql);
      $output = $query->execute($args);
  }




  public function updateFileKeys ($fileid, $tagid, $keyid, $value){
    $sql = 'SELECT id FROM *PREFIX*meta_data_docKeys WHERE fileid=? AND tagid=? and keyid=?';
    $args = array($fileid, $tagid, $keyid);
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
	  $sql = 'UPDATE *PREFIX*meta_data_docKeys SET value=? WHERE fileid=? AND keyid=? AND tagid=?';
	  $args = array($value, $fileid, $keyid, $tagid);
	  $query = \OCP\DB::prepare($sql);
	  $resRsrc = $query->execute($args);
	} else {
	  return FALSE;
	}




/*    if(is_null($value)){
	  $value="";
	}

	//$result = "";
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
 */
	}

	public function getMimeType($mtype){
	  $sql = "SELECT mimetype FROM *PREFIX*mimetypes WHERE id = ?";
	  $args = array($mtype);
	  $query = \OCP\DB::prepare($sql);
	  $output = $query->execute($args);

	  return $output->fetchRow();
	}


	}
