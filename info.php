<?php
include_once 'core/dateClass.php';
include_once 'core/db.php';

$db = new db();
$dateObj = new dateObj();

$_id = $_POST["id"];

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
where testdrive.tid = '{$_id}'";

$testdrive = $db->selectRow($sql);
if($testdrive){
	
	
	
}else{
	showError();
}

function showError(){
	
}

?>