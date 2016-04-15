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
		$email = (isset($decoded['email']) ? $decoded['email'] : null);
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
		$image = (isset($decoded['image']) ? $decoded['image'] : null);
		$latLowerBound = (isset($decoded['latLowerBound']) ? $decoded['latLowerBound'] : null);
		$latUpperBound = (isset($decoded['latUpperBound']) ? $decoded['latUpperBound'] : null);
		$lonLowerBound = (isset($decoded['lonLowerBound']) ? $decoded['lonLowerBound'] : null);
		$lonUpperBound = (isset($decoded['lonUpperBound']) ? $decoded['lonUpperBound'] : null);

		//check for nearby sites
		$nearby = $db->nearbySiteExist($lat, $lon, $latLowerBound, $latUpperBound, $lonLowerBound, $lonUpperBound);
		$size = sizeof($nearby);
        
		if($nearby[0] == null) {
			$response["error"] = FALSE;
			$response["size"] = 0;
				
			//store site
			$site = $db->storeSite($email, $lat, $lon, $title, $description, $rating, $feature1, $feature2, $feature3, $feature4, $feature5, $feature6, $feature7, $feature8, $feature9, $feature10);
			
			$ucid = $site["unique_cid"];
			
			//link site to user
			$link = $db->linkSiteToOwner($uid, $ucid, $relat, $rating);
			
			if($image){
				$data = $db->addImage($image, $ucid);
			}
			
			if ($site && $link) {
				//site stored successfully
				$response["cid"] = $site["unique_cid"];
				$response["site"]["site_admin"] = $site["site_admin"];
				$response["site"]["lat"] = $site["latitude"];
				$response["site"]["lon"] = $site["longitude"];
				$response["site"]["title"] = $site["title"];
				$response["site"]["description"] = $site["description"];
				$response["site"]["rating"] = $link["rating"];
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
				$response["site"]["image"] = $data["image"];

				echo json_encode($response);
				
			} else if(!$site) {
				//site failed to store
				$response["error"] = TRUE;
				$response["error_msg"] = "Error occured in storing site!";
				echo json_encode($response);
			} else if (!$link) {
				//link failed to be made
				$response["error"] = TRUE;
				$response["error_msg"] = "Error occured in linking site to user!";
				echo json_encode($response);
			} else {
			
			
			}
			
			
		} else if ($size > 0) {
            // site found
            $response["error"] = TRUE;
			$response["error_msg"] = "Existing Site Nearby!";
			$response["size"] = $size;
			for($i = 0; $i<$size; $i++){
				$response["site$i"] = $nearby[$i];
			}
            echo json_encode($response);
        }
		
	} else if ($tag == 'knownSites') {
	
		//request type is fetch knownSites
		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		$relat = (isset($decoded['relat']) ? $decoded['relat'] : null);
		
		//get sites
		$known = $db->fetchSites($uid, $relat);
		$size = sizeof($known);
		
		if($known[0] == null){
			$response["error"] = FALSE;
			$response["size"] = 0;
		} else if ($known) {
            // site found
            $response["error"] = FALSE;
			$response["size"] = $size;
			for($i = 0; $i<$size; $i++){
				$ratings = $db->fetchRatings($known[$i]["unique_cid"]);
				
				$response["site$i"] = $known[$i];
				$response["site$i"]["avr_rating"] = $ratings[0];
				$response["site$i"]["no_of_raters"] = $ratings[1];
			}
        } else {
            // site not found
            // echo json with error = 1
            $response["error"] = TRUE;
            $response["error_msg"] = "Error fetching known sites!!";
            echo json_encode($response);
        }
		
		//get images
		$images = $db->fetchImages($uid);
		
		$sizeImages = sizeof($images);
		
		if ($images) {
           //site found
            $response["error"] = FALSE;
			$response["sizeImages"] = $sizeImages;
			for($i = 0; $i<$sizeImages; $i++){
				$response["image$i"] = $images[$i];
			}
            echo json_encode($response);
        } else {
            //site not found
            //echo json with error = 1
            $response["error"] = TRUE;
            $response["error_msg"] = "Error fetching Images!!";
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
        
		if($unknown[0] == null){
			$response["error"] = FALSE;
			$response["size"] = 0;
			echo json_encode($response);
		} else if ($unknown) {
            // site found
            $response["error"] = FALSE;
			$response["size"] = $size;
			for($i = 0; $i<$size; $i++){
				$pop = $db->checkPopularity($unknown[$i]['unique_cid']);
				
				$response["site$i"]["pop"] = $pop;
				$response["site$i"]["details"] = $unknown[$i];
			}
            echo json_encode($response);
        } else {
            // site not found
            // echo json with error = 1
            $response["error"] = TRUE;
            $response["error_msg"] = "Error fetching unknown sites!";
            echo json_encode($response);
        }
		
			
	} else if($tag == 'tradeRequest') {
	
		//request type is trade request
		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		$tradeStatus = (isset($decoded['tradeStatus']) ? $decoded['tradeStatus'] : null);
		$send_cid = (isset($decoded['send_cid']) ? $decoded['send_cid'] : null);
		$recieve_cid = (isset($decoded['recieve_cid']) ? $decoded['recieve_cid'] : null);
		
		//find owner of site and cid
		$reciever_data = $db->getOwnerOfSite($recieve_cid);
		$reciever_uid_fk = $reciever_data['user_fk'];
		$reciever_email = $reciever_data['email'];
		
		//find campsite of sender
		//$sender_data = $db->getOwnerOfSite($sendLat, $sendLon);
		//$send_cid_fk = $sender_data['campsite_fk'];
		
		//check for duplicate trade request
		$duplicateTrade = $db->checkForExistingTrade($uid, $tradeStatus, $reciever_uid_fk, $send_cid, $recieve_cid);

		if($duplicateTrade) {
			//error message
			$response["error"] = TRUE;
			$response["error_msg"] = "Duplicate trade!";
			echo json_encode($response);
		} else {

			//create trade record
			$tradeReq = $db->createRequest($uid, $tradeStatus, $send_cid, $recieve_cid, $reciever_uid_fk);
			if($tradeReq) {
				//trade ok
				$response["error"] = FALSE;
				$response["user contact"] = $reciever_data["email"];
				echo json_encode($response);
				
			} else {
				// trade failed
				$response["error"] = TRUE;
				$response["error_msg"] = "Error with trade!";
				echo json_encode($response);
			}
			
			// include  "class.phpmailer.php";
			// $msg="Hello! This is a test..."
			// $mail= new PHPMailer();
			// $email="dirtymuffin@live.co.uk"; //person who receives your mail
			// $mail->IsSMTP();
			// $mail->Host = "127.0.0.1:81";
			// $mail->SMTPAuth = true; 
			// $mail->Username = "dirtymuffin@hotmail.co.uk"; //your mail id
			// $mail->Password = "TH084301"; //password for your mail id
			// $mail->SetFrom('admin@example.com', 'admin'); //from address
			// $mail->AddAddress($email);
			// $mail->Subject ="Test Mail";
			// $mail->Body = $msg;
			// $mail->IsHTML(true);
			// $mail->MsgHTML($msg);
			// $mail->Send();  
			
			//send email alert to owner of site
			// $retval = mail($reciever_email, "Wild Scotland", "You have recieved a trade request!");
			// if($retval == true) {
				// echo "Message sent ok";
			// } else {
				// echo "Message not sent ok";
			// }
		}
		
	
	} else if ($tag == 'allTrades') {
	
		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		//$tradeStatus = (isset($decoded['tradeStatus']) ? $decoded['tradeStatus'] : null);
		
		$data = $db->getAllTrades($uid);
		
		$size = sizeof($data);
		
		if($data[0] == null){
			$response["error"] = FALSE;
			$response["size"] = 0;
			echo json_encode($response);
		} else if($data) {
			// trades found
            $response["error"] = FALSE;
			$response["size"] = $size;
			for($i = 0; $i<$size; $i++){
				$response["trade$i"] = $data[$i];
			}
            echo json_encode($response);
		} else {
			$response["error"] = TRUE;
			$response["error_msg"] = "Error with fetching trades!";
			echo json_encode($response);
		}
	
	} else if($tag == 'updateTrade') {
	
		$tid = (isset($decoded['tid']) ? $decoded['tid'] : null);
		$tradeStatus = (isset($decoded['tradeStatus']) ? $decoded['tradeStatus'] : null);
		$sender_uid = (isset($decoded['sender_uid']) ? $decoded['sender_uid'] : null);
		$receiver_uid = (isset($decoded['receiver_uid']) ? $decoded['receiver_uid'] : null);
		$send_cid = (isset($decoded['send_cid']) ? $decoded['send_cid'] : null);
		$receive_cid = (isset($decoded['receive_cid']) ? $decoded['receive_cid'] : null);
		
		$relat = 45;
		
		if($tradeStatus == 2){
		
			$linkSender = $db->linkSiteToOwner($sender_uid, $receive_cid, $relat);
			$linkReceiver = $db->linkSiteToOwner($receiver_uid, $send_cid, $relat);
			
			if ($linkSender) {
				//link made successfully
				$responseLinkSend["error"] = FALSE;
				$responseLinkSend["oid"] = $linkSender["unique_oid"];
				echo json_encode($responseLinkSend);
				
			} else {
				//link failed to be made
				$response["error"] = TRUE;
				$response["error_msg"] = "Error occured in linking sender";
				echo json_encode($response);
			}
			
			if ($linkReceiver) {
				//link made successfully
				$responseLinkRec["error"] = FALSE;
				$responseLinkRec["oid"] = $linkReceiver["unique_oid"];
				echo json_encode($responseLinkRec);
				
			} else {
				//link failed to be made
				$response["error"] = TRUE;
				$response["error_msg"] = "Error occured in linking sender";
				echo json_encode($response);
			}
		}
		
		$data = $db->updateTrade($tid, $tradeStatus);
		
		if($data) {
			// trades found
            $response["error"] = FALSE;

            echo json_encode($response);
		} else {
			$response["error"] = TRUE;
			$response["error_msg"] = "Error with trade update!";
			echo json_encode($response);
		}
	
	} else if ($tag == 'deleteSite'){
		
		$cid = (isset($decoded['cid']) ? $decoded['cid'] : null);
		$active = (isset($decoded['active']) ? $decoded['active'] : null);
		
		$data = $db->deleteSite($cid, $active);
		
		if($data) {
			// trades found
            $response["error"] = FALSE;

            echo json_encode($response);
		} else {
			$response["error"] = TRUE;
			$response["error_msg"] = "Error with site deletion!";
			echo json_encode($response);
		}
		
	} else if($tag == 'updateSite') {
	
		$active = (isset($decoded['active']) ? $decoded['active'] : null);
		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		$cid = (isset($decoded['cid']) ? $decoded['cid'] : null);
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
		$image = (isset($decoded['image']) ? $decoded['image'] : null);

		//update site
		$site = $db->updateSite($cid, $title, $description, $feature1, $feature2, $feature3, $feature4, $feature5, $feature6, $feature7, $feature8, $feature9, $feature10);
		
		$rate = $db->updateOwnedRating($uid, $cid, $rating);
		
		if($image){
			$data = $db->addImage($image, $cid);	
		}
		
		if ($site) {
			//site stored successfully
			$response["error"] = FALSE;
			$response["cid"] = $site["unique_cid"];
			$response["site"]["title"] = $site["title"];
			$response["site"]["description"] = $site["description"];
			$response["site"]["rating"] = $rate["rating"];
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
			$response["site"]["updated_at"] = $site["updated_at"];
			$response["site"]["image"] = $data["image"];

			echo json_encode($response);
				
		} else {
			//site failed to store
			$response["error"] = TRUE;
			$response["error_msg"] = "Error occured in updating site!";
			echo json_encode($response);
		}
		
	} else if ($tag == 'updateKnownSite') {

		$active = (isset($decoded['active']) ? $decoded['active'] : null);
		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		$cid = (isset($decoded['cid']) ? $decoded['cid'] : null);
		$rating = (isset($decoded['rating']) ? $decoded['rating'] : null);
		$image = (isset($decoded['image']) ? $decoded['image'] : null);
		$imageNum = (isset($decoded['imageNum']) ? $decoded['imageNum'] : null);
		
		//update rating
		$rating = $db->updateRating($active, $uid, $cid, $rating);
		
		$image = $db->updateImages($cid, $image, $imageNum);
		
		if ($rating) {
			//site stored successfully
			$response["error"] = FALSE;
			$response["site"]["rating"] = $rating["rating"];

			echo json_encode($response);
				
		} else {
			//site failed to store
			$response["error"] = TRUE;
			$response["error_msg"] = "Error occured in updating site!";
			echo json_encode($response);
		}
	
	} else if ($tag == 'uploadImage') {
	
		$image = (isset($decoded['image']) ? $decoded['image'] : null);
		$cid = (isset($decoded['cid']) ? $decoded['cid'] : null);
		
		$data = $db->addImage($image, $cid);
	
		if($data) {
			$response["error"] = FALSE;
			
			echo json_encode($response);
		} else {
			$response["error"] = TRUE;
			$response["error_msg"] = "Error uploading image!";
			echo json_encode($response);
		}

	
	} else if ($tag == 'questions') {
	
		$email = (isset($decoded['email']) ? $decoded['email'] : null);

	
		$data = $db->getQuestions();
		
		$ans = $db->getUserDetails($email);
		
		$size = sizeof($data);
		
		if($data[0] == null){
			$response["error"] = FALSE;
			$response["size"] = 0;
			echo json_encode($response);
		} else if($data) {
			$response["error"] = FALSE;
			$response["size"] = $size;
			$response["name"] = $ans["name"];
			$response["email"] = $ans["email"];
			$response["bio"] = $ans["bio"];						
			for($i = 0; $i<$size; $i++){
			$j = $i+1;
				$response["question$i"] = $data[$i];
				$response["question$i"]["answer"] = $ans["question$j"];
			}
			echo json_encode($response);
		} else {
			$response["error"] = TRUE;
			$response["error_msg"] = "Error fetching questions!";
			echo json_encode($response);
		}
	
	} else if ($tag == 'answers') {
	
		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		$question1 = (isset($decoded['question1']) ? $decoded['question1'] : null);
		$question2 = (isset($decoded['question2']) ? $decoded['question2'] : null);
		$question3 = (isset($decoded['question3']) ? $decoded['question3'] : null);
		$question4 = (isset($decoded['question4']) ? $decoded['question4'] : null);
		$question5 = (isset($decoded['question5']) ? $decoded['question5'] : null);


		$data = $db->updateAnswers($uid, $question1, $question2, $question3, $question4, $question5);
		
		if($data){
			$response["error"] = FALSE;
			echo json_encode($response);
		} else {
			$response["error"] = TRUE;
			$response["error_msg"] = "Error updating answers!";
			echo json_encode($response);
		}
	
	} else if ($tag == 'otherUser'){

		$email = (isset($decoded['email']) ? $decoded['email'] : null);
		
		$data = $db->getUserByEmail($email);
		
		if($data){
			$response["error"] = FALSE;
			echo json_encode($response);
		} else {
			$response["error"] = TRUE;
			$response["error_msg"] = "Error fetching user!";
			echo json_encode($response);
		}

	} else if($tag == 'updateProfile'){

		$uid = (isset($decoded['uid']) ? $decoded['uid'] : null);
		$bio = (isset($decoded['bio']) ? $decoded['bio'] : null);
		
		$data = $db->updateProfile($uid, $bio);
		
		if($data){
			$response["error"] = FALSE;
			echo json_encode($response);
		} else {
			$response["error"] = TRUE;
			$response["error_msg"] = "Error updating profile!";
			echo json_encode($response);
		}
	
	}	else {
        // request failed
        $response["error"] = TRUE;
        $response["error_msg"] = "Unknown 'tag' value.";
        echo json_encode($response);
    } 
	
} else {
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameter 'tag' is missing!";
	$response["content"] = $decoded['tag'];
    echo json_encode($response);
}
?>