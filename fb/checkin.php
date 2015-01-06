<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');


include_once '../services/core/dateClass.php';
include_once '../services/core/db.php';

$db = new db();
$dateObj = new dateObj();

$_tid = $_REQUEST["tid"];
//$_place = 'https://foursquare.com/v/park-place-lexus-plano/4b647487f964a52086b42ae3';

$sql = "SELECT 
make.mid,make.name,make.logo,model.moid,model.name,
car.*,testdrive.created as testdrivedate,
bus_info.* 
FROM `testdrive`
inner join car on testdrive.vin = car.vin  
inner join model on car.moid = model.moid 
inner join make on model.mid = make.mid 
inner join admin on testdrive.admin = admin.adminid 
inner join bus_info on admin.busid = bus_info.uid 
where testdrive.tid = '{$_tid}'";

$testdrive = $db->selectRow($sql);

if($testdrive){
	$url = 'https://www.facebook.com/dialog/share_open_graph?app_id=374335169286433
	&display=touch&action_type=ridealongmobi:test_drive&
	&redirect_uri=http://testdrive.ridealong.mobi/fb/checkin_success/' . $_tid . '&
	place=' . $_place . '&
	action_properties={"car":"http://testdrive.ridealong.mobi/car/' . $_tid . '"}';
	//echo $url;
	header('Location: ' . $url );
}else{
	showError();
}

function showError(){
	
}

?>

