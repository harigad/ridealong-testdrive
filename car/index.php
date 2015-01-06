<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '0');

include_once '../services/core/dateClass.php';
include_once '../services/core/db.php';

$db = new db();

$dateObj = new dateObj();

$app_id = "374335169286433";
$_tid = $_REQUEST["tid"];


$sql = "SELECT user.name as dealer,testdrive.accepted,
make.mid,make.name as make,make.logo,model.moid,model.name as model,
car.*,car.city as gas_city,testdrive.created as testdrivedate,
bus_info.* 
FROM `testdrive`
inner join car on testdrive.vin = car.vin  
inner join model on car.moid = model.moid 
inner join make on model.mid = make.mid 
inner join admin on testdrive.admin = admin.adminid 
inner join bus_info on admin.busid = bus_info.uid 
inner join user on admin.busid = user.id 
where testdrive.tid = '{$_tid}'";

$testdrive = $db->selectRow($sql);
if($testdrive){
	$title =  $testdrive["make"] . " " .  $testdrive["model"] . " @ " . $testdrive["dealer"];
	$_logo = $testdrive["logo"];
	$_model = $testdrive["model"];
    $_lat = $testdrive["lat"];
	$_lng = $testdrive["lng"];

	$testdrivecountObj = $db->selectRow("select count(tid) as total from testdrive where admin in (select adminid from admin where busid ='" . $testdrive["uid"] . "')");
	if($testdrivecountObj){
		$_testdrivecount = $testdrivecountObj[0];
	}else{
		$_testdrivecount = 0;
	}
	
	$dealer_vehichle_count = 0;
}

//502-267-3288
//ATT: Dependant Verification 
//Name and 2011-6787

$url = "http://testdrive.ridealong.mobi/car/{$_tid}";
$image = "http://testdrive.ridealong.mobi/assets/sample_car_image.jpg";
$desc = 'The Infiniti G convertible was produced from 2009 through 2013. It features a retractable hardtop roof, seats four people and was offered as a single G37 model. Trim levels initially consisted of base and Sport, which were later joined by the IPL (Infiniti Performance Line) for 2013.';

//Log
$db->run("update testdrive set views = (views+1) where  tid ='{$_tid}'");

$testdrive_landing_page_logs["tid"] = $_tid;
$testdrive_landing_page_logs["device"] = "PC";
$testdrive_landing_page_logs["created"] = $dateObj->mysqlDate();

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}


$testdrive_landing_page_logs["ip"] = $ip;
$_viewid = $db->insert("testdrive_landing_page_logs",$testdrive_landing_page_logs);

?>
<html ng-app="car">
	<head>
		<meta property="fb:app_id" content="<?php echo $app_id ?>" ></meta>
		<meta property="og:site_name" content="Ride Along" ></meta>
		<meta property="og:title" content="<?php echo $title ?>" ></meta>
		<meta property="og:type" content="ridealongmobi:car" ></meta>
		<meta property="og:url" content="<?php echo $url ?>" ></meta>
		<meta property="og:image" content="<?php echo $image ?>" ></meta>
		<meta property="og:description" content="<?php echo $desc ?>" ></meta>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" media='(min-width: 640px)' href="css/pc.css" />
		<link rel="stylesheet" type="text/css" media='(min-width: 100px) and (max-width: 640px)' href="css/mobile.css" />
		<script src="js/angular.min.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
		<script>
		
		(function(){
			var app = angular.module('car',[]);
			app.controller("car_details_controller",function(){
				this.car = {
					make:"<?php echo $testdrive["make"] ?>",
					model:"<?php echo  $testdrive["model"] ?>",
					logo:"<?php echo  $testdrive["logo"] ?>",
					body:"<?php echo $testdrive["body"] ?>",
					city:"<?php echo $testdrive["gas_city"] ?>",
				    highway:"<?php echo $testdrive["highway"] ?>",
					drive:"<?php echo $testdrive["drive"] ?>",
					hp:"<?php echo $testdrive["hp"] ?>",
					cylinder:"<?php echo $testdrive["cylinder"] ?>",
					seats:5
				}
			});
			
			app.controller("dealer_info_controller",function(){
				this.info = {
					name:"<?php echo $testdrive["dealer"] ?>",
					testdrive_count:<?php echo $_testdrivecount ?>,
					make:"<?php echo $testdrive["make"] ?>",
					model:"<?php echo $testdrive["model"] ?>"
				};
				
				this.vehichle_count = <?php echo $dealer_vehichle_count ?>;
			});
			
			google.maps.event.addDomListener(window, 'load', initialize);
		})();
		
		function initialize() {
			var dealer = new google.maps.LatLng("<?php echo $_lat ?>","<?php echo $_lng ?>");
  			var mapOptions = {
    			zoom: 13,
            	center: dealer
  			};
  			map = new google.maps.Map(document.getElementById('map'),mapOptions);
			
			var coordInfoWindow = new google.maps.InfoWindow();
  			coordInfoWindow.setContent(createInfoWindowContent());
  			coordInfoWindow.setPosition(dealer);
  			coordInfoWindow.open(map);		
  		}
  		
  		function createInfoWindowContent(){
  		  return document.getElementById("infoWindow_container").innerHTML;	
  		}
  		
  		
		</script>
	</head>
	<body>
		<div id="right" >
				<div id="map" ></div>
		</div>
		<div id="main" >
			<div id="left" >
				<div id="left_arrow_btn" ></div>
					<img src="../assets/white_car_large.jpg" id="photo" >
				<div id="right_arrow_btn" ></div>
				<div id="logo_and_title_container"  ng-controller="car_details_controller as car"  >
					<div>
						<div class="logo_container" >
							<img src="../assets/logos/48/{{car.car.logo}}" id="logo" />
						</div>
						<span class="title">
							{{car.car.model}}
						</span>
					</div>
					
					<div ng-show="car.car.seats" >
						<div class="icon_container" >
							<img src="../assets/carseat_60_60.jpg" class="icon" >
						</div>
						<span class="info_item">
							{{car.car.seats}} seater
						</span>
					</div>
					
					<div  ng-show="car.car.body" >
						<div class="icon_container" >
							<img src="../assets/car_body_type_60_60.jpg" class="icon" >
						</div>
						<span class="info_item">
							{{car.car.body}}
						</span>
					</div>
					
					<div  ng-show="car.car.city && car.car.highway">
						<div class="icon_container" >
							<img src="../assets/gas_60_60.jpg" class="icon" >
						</div>
						<span class="info_item">
							{{car.car.city}}/{{car.car.highway}} mpg
						</span>
					</div>
					
					<div ng-show="car.car.hp && car.car.cylinder" >
						<div class="icon_container" >
							<img src="../assets/guage_60_60.jpg" class="icon" >
						</div>
						<span class="info_item">
							{{car.car.hp}} HP, V{{car.car.cylinder}} 
						</span>
					</div>
					
					<div ng-show="car.car.drive" >
						<div class="icon_container" >
							<img src="../assets/steering_60_60.jpg" class="icon" >
						</div>
						<span class="info_item">
							{{car.car.drive}}
						</span>
					</div>
					
				</div>
			</div>
		</div>
		
		<div id="infoWindow_container" style="display:none;" ng-controller="dealer_info_controller as dealer">
			<div id="infoWindow">
				<a href="clicks.php?action=dealer&vid=<?php echo $_viewid ?>" ><div id="info_title" >{{dealer.info.name}}</div></a>
					<div id="info_testdrives" ng-show="dealer.info.testdrive_count" >{{dealer.info.testdrive_count}} testdrives</div>
				<a href="clicks.php?action=stock&vid=<?php echo $_viewid ?>" ><div id="info_schedule" ng-show="dealer.vehichle_count" >
					{{dealer.vehichle_count}} {{dealer.info.make}} {{dealer.info.model}}'s in stock 
				</div></a>
				</a>
			</div>
		</div>
		
	</body>
</html>