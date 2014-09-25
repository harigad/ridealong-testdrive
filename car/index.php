<?php
$tid =  $_GET["tid"];
$app_id = "374335169286433";
$title = $tid. "Infiniti G35 @ Crest Infiniti";
$url = "http://testdrive.ridealong.mobi/car/{$tid}";
$image = "http://4.bp.blogspot.com/_4-xNXQYB4JE/SrP4qR6FMmI/AAAAAAAABEg/6xfMsskh1UI/s400/3914498644_26ca1c732b_o.jpg";
$desc = '300 HP, V6 Engine ..This example Open Graph action was rejected for:Listing a website that does not use Facebook Login in App Settings. Please note that we review all platforms listed in App Settings for Facebook integration.
The "taste" action did not publish on any of the listed platforms (Website & iOS). This rejection means that the reviewer followed your instructions and screenshots, but could not publish "taste" on Facebook. Make sure to check your code and staging credentials. Only submit when you can successfully and consistently publish the Open Graph story.
In general, approved actions will have green banners, instead of the red banners. For unapproved stories or platforms, you will need to make the requested changes, click Save, and create another submission in Status and Review. You can cancel a pending submission at any time.';
?>
<html>
	<head>
		<meta property="fb:app_id" content="<?php echo $app_id ?>" ></meta>
		<meta property="og:site_name" content="Ride Along" ></meta>
		<meta property="og:title" content="<?php echo $title ?>" ></meta>
		<meta property="og:type" content="ridealongmobile:car" ></meta>
		<meta property="og:url" content="<?php echo $url ?>" ></meta>
		<meta property="og:image" content="<?php echo $image ?>" ></meta>
		<meta property="og:description" content="<?php echo $desc ?>" ></meta>
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
		<style>
			#main{
				position:absolute;
				left:0;
				top:0;
			}
			
			#left{
				left:0;display:inline-block;
				top:0;
				width:49%;
				background-color:#990000;
			}
			
			#right{
				left:0;display:inline-block;
				top:0;
				width:50%;
				background-color:#009900
			}
			
		</style>
		<script>
		function initialize() {
  			var mapOptions = {
    			zoom: 8,
            	center: new google.maps.LatLng(-34.397, 150.644)
  			};
  			
  			map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
			}

			google.maps.event.addDomListener(window, 'load', initialize);
		</script>
	</head>
	<body>
		<div id="main" >
			<div id="left" >
				
			</div>
			<div id="right" >
				
			</div>
		</div>
	</body>
</html>