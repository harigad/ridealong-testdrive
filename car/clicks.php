<?php
error_reporting(E_ALL);
//ini_set('display_errors', '0');

include_once '../services/core/dateClass.php';
include_once '../services/core/db.php';

$db = new db();
$dateObj = new dateObj();

$app_id = "374335169286433";
$_vid = $_REQUEST["vid"];

$sql = "SELECT bus_info.web as web ,testdrive.tid as tid
FROM `bus_info` 
inner join admin on bus_info.uid = admin.busid 
inner join testdrive on admin.adminid = testdrive.adminid 
inner join testdrive_landing_page_logs on testdrive.tid = testdrive_landing_page_logs.tid
where testdrive_landing_page_logs.id = '{$_vid}'";

$testdrive = $db->selectRow($sql);

if($testdrive){
	$db->run("update testdrive_landing_page_logs set clicked = (clicked+1) where  id ='" . $_vid. "'");
	$db->run("update testdrive set clickthrough = (clickthrough+1) where  tid ='" . $testdrive["tid"] . "'");
	header("Location: " . $testdrive["web"]);
}

?>