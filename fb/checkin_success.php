<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

include_once '../services/core/dateClass.php';
include_once '../services/core/db.php';

$db = new db();
$dateObj = new dateObj();

$_tid = $_REQUEST["tid"];

$sql = "SELECT user.mobile as sender,testdrive.mobile as customer,testdrive.accepted,
make.mid,make.name as make,make.logo,model.moid,model.name as model,
car.*,testdrive.created as testdrivedate,
bus_info.* 
FROM `testdrive`
inner join car on testdrive.vin = car.vin  
inner join model on car.moid = model.moid 
inner join make on model.mid = make.mid 
inner join admin on testdrive.admin = admin.adminid 
inner join bus_info on admin.busid = bus_info.uid 
inner join user on admin.uid = user.id 
where testdrive.tid = '{$_tid}'";
//$db->debug = true;
$testdrive = $db->selectRow($sql);
if($testdrive){
	
	$update["fbpostid"] = $_REQUEST["post_id"];
	$update["accepted"] = $dateObj->mysqlDate();
	$db->update("testdrive",$update,"tid='{$_tid}'");
	
	sendConfirmationToDealer($testdrive['sender'],$testdrive['customer'],$testdrive['make'],$testdrive['model']);
}



function sendConfirmationToDealer($sender,$customer,$make,$model){
		global $db;
		require "../services/twillio/library/twilio-php-master/Services/Twilio.php";
 	
		// set your AccountSid and AuthToken from www.twilio.com/user/account
		$AccountSid = "ACfbc2ac6203e446097d822d4cb17a016a";
		$AuthToken = "c89efab591537bbc245bdcc936137482";
 
		$client = new Services_Twilio($AccountSid, $AuthToken);
 
		$message = $client->account->messages->create(array(
    	"From" => "+1469-606-3500",
    	"To" => $sender,
    	"Body" => formatPhone($customer) . " accepted your request for " . $make . " " . $model
		));
		
		$update["twilliosid"] = $message->sid;
		$db->update("testdrive",$update," tid='{$testdriveid}'");
 	
		// Display a confirmation message on the screen
		//echo "Sent message {$message->sid}";
		//send message to twillio	
	}

function formatPhone($str){
		return "(".substr($str, 0, 3).") ".substr($str, 3, 3)."-".substr($str,6);
}
	
?>