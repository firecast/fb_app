<?php
require 'sdk/src/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => '490295574386191',
  'secret' => 'b2ee0df2d221fefae9e84960d3bc7d88',
));

// Get User ID
$user = $facebook->getUser();

// We may or may not have this data based on whether the user is logged in.
//
// If we have a $user id here, it means we know the user is logged into
// Facebook, but we don't know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

// Login or logout url will be needed depending on current user state.
if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl();
}

// main code goes here----------------------------------
//database essentials
$host="localhost"; // Host name
$username="root"; // Mysql username
$password="password"; // Mysql password
$db_name = "fb_amit";

mysql_connect("$host", "$username", "$password")or die("cannot connect");
mysql_select_db("$db_name")or die("cannot select DB");

//database essentials done


function set($data,$key)
{
	if (isset($data[$key]))
		return $data[$key];
	else
		return NULL;

}

$social_data = array();
$keywords = array('coca-cola');

foreach( $keywords as $key){
	$social_data[$key]=array();
	$link = "/search?q=$key&type=post";
	for($i=0;$i<2;$i++)
	{
		$data1=array();
		$data1 = $facebook->api($link);
		if(empty($data1['data']))
			break;
		foreach($data1 as $data)
		{
			foreach( $data as $number => $main_data)
			{		
				if (!strcmp($number,'previous')){ break;}
				//print_r("here");
				$social_data[$key][$j] = array();
				$social_data[$key][$j]['id']=$main_data['id'];
				$social_data[$key][$j]['from']=$main_data['from'];
				$social_data[$key][$j]['message']=$main_data['message'];
				$social_data[$key][$j]['type']=$main_data['type'];
				$social_data[$key][$j]['created_time']=$main_data['created_time'];
				$social_data[$key][$j]['updated_time']=$main_data['updated_time'];
				$social_data[$key][$j]['link']=set($main_data,'link');
				$social_data[$key][$j]['name']=set($main_data,'name');
				$social_data[$key][$j]['description']=set($main_data,'description');
				$social_data[$key][$j]['picture']=set($main_data,'picture');
				$social_data[$key][$j]['caption']=set($main_data,'caption');
				$social_data[$key][$j]['properties']=set($main_data,'properties');
				$temp = set($main_data,'shares');
				if ($temp!=NULL)
					$social_data[$key][$j]['shares']=$temp["count"];
				else
					$social_data[$key][$j]['shares']=0;
					
				$temp = set($main_data,'likes');
				if ($temp!=NULL)
					$social_data[$key][$j]['likes'] = count($temp["data"]);
				else
					$social_data[$key][$j]['likes'] = 0;
				//comments paging
				$temp = set($main_data,'comments');
				if ($temp!=NULL)
				{
					$id = $main_data['id'];
					$temp2 = "https://graph.facebook.com/$id/comments";
					do{
					$data3 = array();
					$index2 = strpos($temp2,"/$id");
					$link2 = substr($temp2,$index2,1000);
					$data3 = $facebook->api($link2);
					$comments[$id] = $data3["data"];
					$temp2 = set($data3,'next');
					}while($temp2 != null);
					
					
				}
				
				$j++;
			}
		}
		$index = strpos($data1["paging"]["next"],'/search?');
		$link = '';
		$link = substr($data1["paging"]["next"], $index,1000);
		//print_r($link);
	}
}
echo "<pre>";
print_r($comments);
echo "</pre>";

//--------------------------------ends here---------------------------
?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>php-sdk</h1>

    <?php if ($user): ?>
      <a href="<?php echo $logoutUrl; ?>">Logout</a>
    <?php else: ?>
      <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>
  </body>
</html>
