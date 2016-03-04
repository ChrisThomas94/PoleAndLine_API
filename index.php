<?php
 
/**
 * File to handle all API requests
 * Accepts GET and POST
 * 
 * Each request will be identified by TAG
 * Response will be JSON data
 
  /**
 * check for POST request 
 */
// Request type is Register new user
// include DB_function
require_once 'include/DB_Functions.php';
$db = new DB_Functions();

$inputJSON = file_get_contents('php://input');

$filename="test.txt";
//file_put_contents($filename,json_decode(file_get_contents('php://input'), true));
//file_put_contents($filename, $inputJSON);

//die(json_decode($inputJSON, true));

$decoded = json_decode(file_get_contents('php://input'), true);

if (isset($decoded['tag']) && !empty($decoded['tag'])) {
    // get tag
    $tag = $decoded['tag'];
 
    // response Array
    $response = array("tag" => $tag, "error" => FALSE);
 
    // checking tag
    if ($tag == 'login') {
        // Request type is check Login
        $email = (isset($decoded['email']) ? $decoded['email'] : null);
		$password = (isset($decoded['password']) ? $decoded['password'] : null);
 
        // check for user
        $user = $db->getUserByEmailAndPassword($email, $password);
        if ($user != false) {
            // user found
            $response["error"] = FALSE;
            $response["uid"] = $user["unique_uid"];
            $response["user"]["name"] = $user["name"];
            $response["user"]["email"] = $user["email"];
            $response["user"]["created_at"] = $user["created_at"];
            $response["user"]["updated_at"] = $user["updated_at"];
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = TRUE;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
    } else if ($tag == 'register') {
        // Request type is Register new user
		$name = (isset($decoded['name']) ? $decoded['name'] : null);
		$email = (isset($decoded['email']) ? $decoded['email'] : null);
		$password = (isset($decoded['password']) ? $decoded['password'] : null);
 
        // check if user is already existed
        if ($db->isUserExisted($email)) {
            // user is already existed - error response
            $response["error"] = TRUE;
            $response["error_msg"] = "User already existed";
            echo json_encode($response);
        } else {
            // store user
            $user = $db->storeUser($name, $email, $password);
            if ($user) {
                // user stored successfully
                $response["error"] = FALSE;
                $response["uid"] = $user["unique_uid"];
                $response["user"]["name"] = $user["name"];
                $response["user"]["email"] = $user["email"];
                $response["user"]["created_at"] = $user["created_at"];
                $response["user"]["updated_at"] = $user["updated_at"];
                echo json_encode($response);
            } else {
                // user failed to store
                $response["error"] = TRUE;
                $response["error_msg"] = "Error occured in Registartion";
                echo json_encode($response);
            }
        }
    } else if ($tag == 'addSite') {
	
		//request type is add site
		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		$relat = (isset($decoded['relat']) ? $decoded['relat'] : null);
		$lat = (isset($decoded['lat']) ? $decoded['lat'] : null);
		$lon = (isset($decoded['lon']) ? $decoded['lon'] : null);
		$title = (isset($decoded['title']) ? $decoded['title'] : null);
		$description = (isset($decoded['description']) ? $decoded['description'] : null);
		$rating = (isset($decoded['rating']) ? $decoded['rating'] : null);
		$feature1 = (isset($decoded['feature1']) ? $decoded['feature1'] : null);
		$feature2 = (isset($decoded['feature2']) ? $decoded['feature2'] : null);
		$feature3 = (isset($decoded['feature3']) ? $decoded['feature3'] : null);
		$feature4 = (isset($decoded['feature4']) ? $decoded['feature4'] : null);
		$feature5 = (isset($decoded['feature5']) ? $decoded['feature5'] : null);
		$feature6 = (isset($decoded['feature6']) ? $decoded['feature6'] : null);
		$feature7 = (isset($decoded['feature7']) ? $decoded['feature7'] : null);
		$feature8 = (isset($decoded['feature8']) ? $decoded['feature8'] : null);
		$feature9 = (isset($decoded['feature9']) ? $decoded['feature9'] : null);
		$feature10 = (isset($decoded['feature10']) ? $decoded['feature10'] : null);
		
		//checks go here
		if ($db->nearbySiteExist($lat, $lon)) {
            // user is already existed - error response
            $response["error"] = TRUE;
            $response["error_msg"] = "Nearby sites exist";
            echo json_encode($response);
        } else {
			//store site
			$site = $db->storeSite($lat, $lon, $title, $description, $rating, $feature1, $feature2, $feature3, $feature4, $feature5, $feature6, $feature7, $feature8, $feature9, $feature10);
			
			$ucid = $site["unique_cid"];
			
			if ($site) {
				//site stored successfully
				$response["error"] = FALSE;
				$response["cid"] = $site["unique_cid"];
				$response["site"]["lat"] = $site["latitude"];
				$response["site"]["lon"] = $site["longitude"];
				$response["site"]["title"] = $site["title"];
				$response["site"]["description"] = $site["description"];
				$response["site"]["rating"] = $site["rating"];
				$response["site"]["feature1"] = $site["feature1"];
				$response["site"]["feature2"] = $site["feature2"];
				$response["site"]["feature3"] = $site["feature3"];
				$response["site"]["feature4"] = $site["feature4"];
				$response["site"]["feature5"] = $site["feature5"];
				$response["site"]["feature6"] = $site["feature6"];
				$response["site"]["feature7"] = $site["feature7"];
				$response["site"]["feature8"] = $site["feature8"];
				$response["site"]["feature9"] = $site["feature9"];
				$response["site"]["feature10"] = $site["feature10"];
				$response["site"]["created_at"] = $site["created_at"];
				$response["site"]["updated_at"] = $site["updated_at"];
				echo json_encode($response);
				
			} else {
				//site failed to store
				$response["error"] = TRUE;
				$response["error_msg"] = "Error occured in Registartion";
				echo json_encode($response);
			}
			
			//link site to user
			$link = $db->linkSiteToOwner($uid, $ucid, $relat);
			
			if ($link) {
				//link made successfully
				$responseLink["error"] = FALSE;
				$responseLink["oid"] = $link["unique_oid"];
				echo json_encode($responseLink);
			
			} else {
				//link failed to be made
				$response["error"] = TRUE;
				$response["error_msg"] = "Error occured in linking";
				echo json_encode($response);
			}
			
		}
		
	} else if ($tag == 'knownSites') {
	
		//request type is fetch knownSites
		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		$relat = (isset($decoded['relat']) ? $decoded['relat'] : null);
		
		//get sites
		$known = $db->fetchSites($uid, $relat);
		$size = sizeof($known);
        if ($known != false) {
            // site found
            $response["error"] = FALSE;
			$response["size"] = $size;
			for($i = 0; $i<$size; $i++){
				$response["site$i"] = $known[$i];
			}
            echo json_encode($response);
        } else {
            // site not found
            // echo json with error = 1
            $response["error"] = TRUE;
            $response["error_msg"] = "Error fetching known sites!!";
            echo json_encode($response);
        }
		
			
	} else if ($tag == 'unknownSites') {
	
		//request type is fetch knownSites
		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		$relatOwn = (isset($decoded['relatOwn']) ? $decoded['relatOwn'] : null);
		$relatTrade = (isset($decoded['relatTrade']) ? $decoded['relatTrade'] : null);
		
		//get sites
		$unknown = $db->fetchUnknownSites($uid, $relatOwn, $relatTrade);
		$size = sizeof($unknown);
        if ($unknown != false) {
            // site found
            $response["error"] = FALSE;
			$response["size"] = $size;
			for($i = 0; $i<$size; $i++){
				$response["site$i"] = $unknown[$i];
			}
            echo json_encode($response);
        } else {
            // site not found
            // echo json with error = 1
            $response["error"] = TRUE;
            $response["error_msg"] = "Error fetching unknown sites!";
            echo json_encode($response);
        }
		
			
	} else {
        // request failed
        $response["error"] = TRUE;
        $response["error_msg"] = "Unknown 'tag' value.";
        echo json_encode($response);
    } 
	
} else {
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameter 'tag' is missing!";
    echo json_encode($response);
}
?>