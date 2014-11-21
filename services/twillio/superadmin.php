<?php
    $func = cleanString($strs[0]);
	$_mobile = cleanString($strs[1]);
	$_pass = cleanString($strs[2]);	

if($func === "add"){
$checkUserExists = $db->selectRow("select id from user where mobile='{$_mobile}'");	

if($checkUserExists){
	$new_uid = $checkUserExists["id"];
}else{
	$new_account["mobile"] = $_mobile;
	$new_account["pass"] = $_pass;
	$new_uid = $db->insert("user",$new_account);
}
//----------------------------------------------
$new_bus_account["name"] = "New Business";
$new_bus_uid = $db->insert("user",$new_bus_account);

$new_bus["uid"] = $new_bus_uid;
$new_busid = $db->insert("bus_info",$new_bus);

$new_admin["busid"] = $new_busid;
$new_admin["uid"] = $new_uid;
$new_adminid = $db->insert("admin",$new_admin);

sendTwillioNewMessage($_mobile,"Welcome to TestDrive http://dealer.ridealong.mobi");
printResponse("New Account Created for " . $_mobile);




}
?>