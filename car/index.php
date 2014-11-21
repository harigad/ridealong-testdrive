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
	}
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
		<meta property="og:type" content="ridealongmobile:car" ></meta>
		<meta property="og:url" content="<?php echo $url ?>" ></meta>
		<meta property="og:image" content="<?php echo $image ?>" ></meta>
		<meta property="og:description" content="<?php echo $desc ?>" ></meta>
		<script src="js/angular.min.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
		<style>
		   a{
		   	text-decoration:none;
		   }
		    body{
		    	height:100%;margin-top:0px;margin-left:0px;margin-right:0px;
		    	font-family:Tahoma;
				letter-spacing:1.5px;
		    }
			
			#left{
				left:0;display:relative;
				top:0;
				width:49%;height:auto;
				background-color:#fff;
			}
			
			#right{
				right:0;position:fixed;top:0;
				width:50%;height:100%;
				background-color:#eee
			}
			#map{
				width:100%;height:100%;position:relative;
			}
			#infoWindow{
				height:auto;width:240px;
				background-color:#fff;
				border-radius:8px;
			}
			#info_title{
				font-size:18px;
				color:#4590D1;
			}
			#info_testdrives{
				font-size:12px;
				color:#999;
			}
			#info_schedule{
				font-size:12px;
				color:#4590D1;
			}
			.logo_container{
				width:68px;height:68px;display:inline-block;
				background-color:#333;border-radius:38;
				border:#666;
				border-style: solid;
    			border-width: 4px;vertical-align: middle;
			}
			.title{
				font-size:80px;
				color:#555;vertical-align: middle;margin-left:10px;padding-top:0px;padding-bottom:0px;margin-top:0px;margin-bottom:0px;
			}
			
			.icon_container{
				width:76px;height:76px;display:inline-block;
				background-color:#fff;
				vertical-align: middle;
			}
			
			.icon{
				width:48px;height:48px;margin:14px;vertical-align: middle;
			}
			
			.info_item{
				font-size:48px;font-family:"Tahoma";font-weight:50;
				color:#cecece;vertical-align: middle;margin-left:10px;padding-top:0px;padding-bottom:0px;margin-top:0px;margin-bottom:0px;
			}
			
		</style>
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
					testdrive_count:28,
					vehichle_count:15,
					make:"<?php echo $testdrive["make"] ?>",
					model:"<?php echo $testdrive["model"] ?>"
				};
				
				this.vehichle_count = 18;
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
				<div style="width:10%;display:inline-block;" ></div>
					<img src="../assets/white_car_large.jpg" style="width:80%;" >
				<div style="width:10%;display:inline-block;" ></div>
				<div style="width:10%;" ></div>
				<div id="logo_and_title_container" style="padding-left:10px;padding-right:10px;" ng-controller="car_details_controller as car"  >
					<div>
						<div class="logo_container" >
							<img src="../assets/logos/48/{{car.car.logo}}" style="width:48px;height:48px;margin:10px;">
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
				<a href="clicks.php?action=stock&vid=<?php echo $_viewid ?>" ><div id="info_schedule" ng-show="dealer.vehichle_count" >{{dealer.vehichle_count}} {{dealer.info..make}} {{dealer.info.model}}'s in stock</div></a>
				</a>
			</div>
		</div>
		
	</body>
</html>