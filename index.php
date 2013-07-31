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
$db_handle = mysql_select_db("$db_name");

//database essentials done


function set($data,$key)
{
	if (isset($data[$key]))
		return $data[$key];
	else
		return NULL;

}
$keywords = array('coca-cola');

foreach( $keywords as $key){
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
				$id = $main_data['id'];
				$id_name = $main_data['from']['id'];
				$name = $main_data['from']['name'];
				$temp = set($main_data['from'],'category');
				if($temp!=NULL)
					$category = $temp;
				else
					$category = "USER";
				$message = $main_data['message'];
				$type =$main_data['type'];
				$temp =$main_data['created_time'];
				$created_time = substr($temp,0,10)." ".substr($temp,11,8);
				$link =set($main_data,'link');
				$name_link =set($main_data,'name');
				$description_link =set($main_data,'description');
				$picture_link =set($main_data,'picture');
				$caption_link =set($main_data,'caption');
				$temp = set($main_data,'shares');
				if ($temp!=NULL)
					$num_shares =$temp["count"];
				else
					$num_shares =0;
					
				$temp = set($main_data,'likes');
				if ($temp!=NULL)
					$num_likes = count($temp["data"]);
				else
					$num_likes = 0;
				//comments paging
				//---adding contents to table "data"---------------
				$sql = "INSERT INTO data (id_posts,keyword,name,id_name,message,type,link,name_link,caption_link,description_link,picture_link,num_shares,num_likes,category,created_time) VALUES(\"$id\",\"$key\",\"$name\",\"$id_name\",\"$message\",\"$type\",\"$link\",\"$name_link\",\"$caption_link\",\"$description_link\",\"$picture_link\",$num_shares,$num_likes,\"$category\",\"$created_time\")";
			
				$result = mysql_query($sql);
				//print_r($result);
				
				$temp = set($main_data,'comments');
				if ($temp!=NULL)
				{
					$temp2 = "https://graph.facebook.com/$id/comments";
					do{
					$data3 = array();
					$index2 = strpos($temp2,"/$id");
					$link2 = substr($temp2,$index2,1000);
					$data3 = $facebook->api($link2);
					$comments[$id] = $data3["data"];
					foreach ($data3['data'] as $number=>$value)
					{
						$id_comments = $value['id'];
						$name = $value['from']['name'];
						$id_name = $value['from']['id'];
						$message = $value['message'];
						$temp =$value['created_time'];
						$created_time = substr($temp,0,10)." ".substr($temp,11,8);
						$like_count = $value['like_count'];
						$sql = "INSERT INTO comments (id_posts,id_comments,keyword,name,id_name,message,created_time,like_count) VALUES(\"$id\",\"$id_comments\",\"$key\",\"$name\",\"$id_name\",\"$message\",\"$created_time\",$like_count)";
						$result = mysql_query($sql);
					}
					$temp2 = set($data3,'next');
					}while($temp2 != null);
					
					
				}
				//somments paging done
				$j++;
			}
		}
		$index = strpos($data1["paging"]["next"],'/search?');
		$link = '';
		$link = substr($data1["paging"]["next"], $index,1000);
		//print_r($link);
	}
}
//mysql_close($db_handle);
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
