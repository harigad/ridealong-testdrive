<?php header('Access-Control-Allow-Origin: *');
      header("Access-Control-Allow-Headers: Origin, X-Requested-With,X-Titanium-Id, Content-Type, Accept");

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

session_start();

$edmundsAPI = "u8jx3m9zrd4rbfw32k799qfv";
include_once '../core/dateClass.php';
include_once '../core/db.php';
include_once "../google/GoogleUrlApi.php";

$db = new db();
$dateObj = new dateObj();

$_from = $_REQUEST["From"];
$_from = str_replace("+1", "", $_from);
$_from = cleanString($_from);
$_message = $_REQUEST["Body"];
$_email = "";
$strs = explode(",",$_message);

	$admin = $db->selectRow("select adminid,busid from admin inner join user on admin.uid = user.id where user.mobile='{$_from}'");
	if($admin){
		if(($admin["busid"] === "1" || $admin["busid"] === 1) && sizeof($strs) === 3){
			include_once 'superadmin.php'; 
			exit();
		}//else proceed
	}else{
		exit(0);//logError();
	}

if(sizeof($strs) === 2){
	$strs[0] = cleanString($strs[0]);
	$strs[1] = cleanString($strs[1]);
	
	if(strlen($strs[0]) === 10){
		$_mobile = $strs[0];
		$_vin = $strs[1];
	}else if(strlen($strs[1]) === 10){
		$_mobile = $strs[1];
		$_vin = $strs[0];
	}else{
		logError("error: 10 digit customer mobile# was missing");
	}
}else{
	logError("error: 10 digit customer mobile# and the vin# must be separated by a comma");
}

$cid = getCar($_vin);
createAndSendPost($admin["adminid"],$cid,$_vin,$_mobile,$_email,$_message);
	
	function createAndSendPost($admin,$cid,$_vin,$_mobile,$_email,$_message){
		global $db,$dateObj;
		$data["admin"] = $admin;
		$data["vin"] = $_vin;
		$data["mobile"] = $_mobile;
		$data["email"] = $_email;
		$data["message"] = $_message;
		$data["created"] = $dateObj->mysqlDate();
		
		$testdriveid = $db->insert("testdrive",$data);
		$shortUrl = getShortUrl($testdriveid);
		$update["shorturl"] = $shortUrl;
		
		$db->update("testdrive",$update,"tid ='{$testdriveid}'");
		sendPost($shortUrl,$_mobile,$cid,$testdriveid);
	}
	
	function sendTwillioNewMessage($to,$body){
		require "library/twilio-php-master/Services/Twilio.php";
		$AccountSid = "ACfbc2ac6203e446097d822d4cb17a016a";
		$AuthToken = "c89efab591537bbc245bdcc936137482";
 
		$client = new Services_Twilio($AccountSid, $AuthToken);
 
		$message = $client->account->messages->create(array(
    	"From" => "+1469-606-3500",
    	"To" => "$to",
    	"Body" => $body
		));
	}
	
	
	function sendPost($shortUrl,$_mobile,$cid,$testdriveid){
		global $db;
		$carDetails = getCarDetails($cid);
 
 		sendTwillioNewMessage($_mobile,"Check-In to the " . $carDetails["make"] . " " . $carDetails["model"] . " @ {$shortUrl}");
 
				
		$update["twilliosid"] = $message->sid;
		$db->update("testdrive",$update," tid='{$testdriveid}'");
 
 		printResponse("check-in request for " .  $carDetails["make"] . " " . $carDetails["model"] . " sent to " . formatPhone($_mobile));
 
		// Display a confirmation message on the screen
		//echo "Sent message {$message->sid}";
		//send message to twillio	
	}
	
	function getShortUrl($testdriveid){
		$googer = new GoogleUrlApi();

		// Test: Shorten a URL
		$shortDWName = $googer->shorten("http://testdrive.ridealong.mobi/fb/checkin/" . $testdriveid);
		return $shortDWName; // returns http://goo.gl/i002

		// Test: Expand a URL
		//$longDWName = $googer->expand($shortDWName);
		//echo $longDWName; // returns http://davidwalsh.name
	}
	
	function getCarDetails($cid){
			global $db;
			$cardetails = $db->selectRow("select car.year as year,model.name as 'model',make.name as 'make',make.logo from car 
			inner join model on car.moid = model.moid 
			inner join make on model.mid = make.mid 
			where car.cid='" . $cid. "'");
			return $cardetails;
	}
	
	function getCar($vin){
			global $db;
			$cidObj = $db->selectRow("select car.cid from car where car.vin='" . $vin. "'");
			
			if($cidObj){
				return $cidObj["cid"];
			  //   $cardetails["colors"] = loadColors($data);
			  //  sleep(1);
			  //  $cardetails["colorsInterior"] = loadColorsInterior($data);
			  //  $cardetails["status"] = 1;
			  // echo json_encode($cardetails);
			  // exit();
			}else{
				return createCar($vin);
			}
	}

	function createCar($vin){
		global $db,$dateObj;
			$edmundsAPI = "u8jx3m9zrd4rbfw32k799qfv";
			$data = edmundsVinLookup($vin);
			$newCarData['vin']  = $vin;
			$newCarData['year'] = $data->years[0]->year;
			$newCarData['moid'] = createMakeAndModel($data->make->name,$data->model->name);
			$newCarData['body'] = $data->categories->vehicleStyle;
			$newCarData['doors'] = $data->numOfDoors;
			$newCarData['highway'] = $data->MPG->highway;
			$newCarData['city'] = $data->MPG->city;
			$newCarData['drive'] = $data->drivenWheels;
			
			$newCarData['type'] = $data->engine->type;
			$newCarData['enginesize'] = $data->engine->size;
			$newCarData['cylinder'] = $data->engine->cylinder;
			$newCarData['hp'] = $data->engine->horsepower;
			$newCarData['torque'] = $data->engine->torque;
			
			$newCardata['created'] = $dateObj->mysqlDate();
			$newCarId = $db->insert("car",$newCarData);
			return $newCarId;
	}

	function edmundsVinLookup($vin){
   	global $edmundsAPI;
   	$request = "https://api.edmunds.com/api/vehicle/v2/vins/{$vin}?&fmt=json&api_key=" . $edmundsAPI;
   	$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$detailstring = curl_exec($ch);

	curl_close($ch);
	$detailJSON = json_decode($detailstring);
    return $detailJSON;
	}

	function createMakeAndModel($make,$model){
	global $db;
		$mIdArray = $db->selectRow("select mid from make where code='" . strtolower($make) . "' limit 1");
			if($mIdArray){
					$mId = $mIdArray['mid']; 
					return createNewModel($mId,$model);
			}else{
					$newMake['code'] = strtolower($make);
					$newMake['name'] = ucwords(strtolower($make));
					$newMake['logo'] = ucwords(strtolower($make)) . ".png";
					$mId = $db->insert("make",$newMake);
				 	return createNewModel($mId,$model);
			}
	}
	
	function createNewModel($mId,$model){
	global $db;
		$moIdArray = $db->selectRow("select moid from model where mid={$mId} and code='" . strtolower($model) . "' limit 1");
			if($moIdArray){
					return $moIdArray['moid'];
			}else{
					$newModel['mid'] = $mId;
					$newModel['code'] = strtolower($model);
					$newModel['name'] = strtoupper($model);
				 	return $db->insert("model",$newModel);				 
			}
	}
	
	function cleanString($string) {
   		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
		return $string;
	}
	
	function formatPhone($str){
		return "(".substr($str, 0, 3).") ".substr($str, 3, 3)."-".substr($str,6);
	}
	
	function logError($e){
	//	echo $e;
		printResponse($e);
		exit(0);
	}
	
	function printResponse($e) { 
		header('Content-type: text/xml');
		?>
		<Response>
    	<Message><?php echo $e ?></Message>
		</Response>
<?php }
	
?>