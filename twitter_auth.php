<?php 

require_once __DIR__.'/common.inc.php';
require_once __DIR__.'/dbhelper.php';
require_once __DIR__.'/vendor/abraham/twitteroauth/twitteroauth/twitteroauth.php';
session_start();


if(empty($_GET['oauth_token'])){
	/* Build TwitterOAuth object with client credentials. */
	$connection = new TwitterOAuth($twitterConsumerKey, $twitterConsumerSecret);
 
	/* Get temporary credentials. */
	$callback = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$_SERVER['PHP_SELF'];
	print_r($callback);
	$request_token = $connection->getRequestToken($callback);

	/* Save temporary credentials to session. */
	$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
 
	/* If last connection failed don't display authorization link. */
	switch ($connection->http_code) {
  		case 200:
    	/* Build authorize URL and redirect user to Twitter. */
    		$url = $connection->getAuthorizeURL($token);
   	 		header('Location: ' . $url); 
    		break;
  		default:
    		/* Show notification if something went wrong. */
    		echo 'Could not connect to Twitter. Refresh the page or try again later.';
	}
} else {
	$connection = new TwitterOAuth($twitterConsumerKey, $twitterConsumerSecret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	$token_credentials = $connection->getAccessToken($_GET['oauth_verifier']);

	$dbHelper = new DBHelper();
	$dbHelper->storeTwitterToken("twitterUser", $token_credentials);
	header('Location: index.php#main');
	
}

?>
