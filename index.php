<?php
require_once 'common.inc.php';

?>

<!DOCTYPE HTML>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<link rel="stylesheet"
		href="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.css" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>DynamoSocialStream</title>
	
	<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
	<script	src="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.js"></script>
	<script type="text/javascript" src="<?php echo $myWsUrl.'socket.io/socket.io.js'?>"></script>
	<script type="text/javascript">
 		myWsUrl = "<?php echo $myWsUrl?>";
		myUserId = "twitterUser";
 	</script>
	<script src="client.js"></script>
	
</head>

<body> 

<!-- Start of second page -->
<div data-role="page" id="main">

	<div data-role="header">
		<h1>Dynamo Social Stream</h1>
	</div><!-- /header -->

	<div data-role="content">	
	
        <div data-role="fieldcontain">
        	<fieldset data-role="controlgroup">
                 <label for="slider1">
                     Public Twitter Stream (%)
                 </label>
                 <input type="range" name="slider" id="pubStreamRatio" value="0" min="0" max="100" data-highlight="true" />
            </fieldset>
        </div>
        
        <ul id="feeds" style="margin-top: 10px" data-role="listview"></ul>
	</div><!-- /content -->

	<div data-role="footer">
	</div><!-- /footer -->
</div><!-- /page -->

</body>
</html>
