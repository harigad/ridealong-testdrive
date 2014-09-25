<?php 
class car{

   function __construct($id,$oid = null){
        $this->id = $id;
        $this->oid = $oid;
   }
   
   function addOwner($uid,$prime){
     global $db,$user;
     $mysqldate=new dateObj();
     	$data['cid'] = $this->id;
     	$data['uid'] = $uid;
     	
     	$currentOwnerData= $db->selectRow("select regdate,titledate from owner where uid='$user->id' and cid='$this->id'");
     	
     	if($currentOwnerData){
     		$data['regdate'] = $currentOwnerData["regdate"];
     		$data['titledate'] = $currentOwnerData["titledate"];
     	}
     	
     	$countArr = $db->selectRow("select count(cid) from owner where uid='{$uid}'");
     	if($countArr == 0){
     		$prime = true;
     	}
     	
     	$data['prime'] = $prime;
     	$data['created'] = $mysqldate->mysqlDate();
		
		$oid = $db->insert("owner",$data);	
	
		$userObj = $db->selectRow("select id,name,photo,photo_big from user where id='{$uid}'");
		return $oid;
   }
   
   function removeOwner($uid){
   		global $db;
   		$carObj = $db->selectRow("select oid from owner where uid='" . $uid . "' and cid='" . $this->id . "'");
   		if($carObj){
   			mysql_query("delete from owner where ref_id ='" . $carObj[0] . "'");
   		}
   		//delFeed($sharerObj['id']);
   }
    
   function getAll($uid){
   	 $data->radio = $this->getRadio();
   	 $data->shares = $this->getShares($uid);
   	 return $data;
   }   
   
   function getInfo(){
     global $db;
     //TODO
   }
   
   function getShares($uid){
   	 global $db;
   	 $sharesArr = $db->selectRows("select id as uid,name,photo,photo_big,plate from owner inner join user on owner.uid=user.id where owner.cid='{$this->id}' and owner.uid<>'{$uid}'"); 
		
		$shares = array();
        if (mysql_num_rows($sharesArr) > 0) {
            while ($share = mysql_fetch_object($sharesArr)) {
				array_push($shares,$share);
            }        
		} 		
		return $shares;
   
    }
    
   function getRides(){
	  global $db;
   	  $rides = array();
   	   
	  $ridesArr = $db->selectRows("SELECT user.id AS uid, user.name AS name, user.photo, user.photo_big
				FROM user
				INNER JOIN checkin ON user.id = checkin.uid 
				INNER JOIN owner on checkin.ouid = owner.uid 
				where owner.cid = '{$this->id}'   
				ORDER BY checkin.created DESC limit 6 "); 

	    if (mysql_num_rows($ridesArr) > 0) {
            while ($ride = mysql_fetch_object($ridesArr)) {
				array_push($rides,$ride);
            }        
		}     	
   	 return $rides;
   }
    
   function getRadio(){
    global $db;
   	  $radios = array();
 
	  $radiosArr = $db->selectRows("select rid,name from radio where cid='{$this->id}'"); 
		$radios = array();		
	    if (mysql_num_rows($radiosArr) > 0) {
            while ($radio = mysql_fetch_object($radiosArr)) {
				array_push($radios,$radio);
            }        
		}     	
   	 return $radios;
   }
   
} 
?>