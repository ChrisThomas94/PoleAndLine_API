<?php
require_once 'DB_Connect.php';
error_reporting(E_ALL ^ E_DEPRECATED);

class DB_Functions {
 
    private $db;
 
    // constructor
    function __construct() {
        // connecting to database
        $this->db = new DB_Connect();
        $this->db->connect();
    }
 
    // destructor
    function __destruct() {
        
    }
 
    /**
     * Store user details
     */
    public function storeUser($name, $token, $email, $password) {
        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
        $result = mysqli_query($this->db->con,"INSERT INTO users(unique_uid, token, name, email, encrypted_password, salt, created_at) VALUES('$uuid', '$token', '$name', '$email', '$encrypted_password', '$salt', NOW())");
        // check for result
        if ($result) {
            // gettig the details
            $uid = mysqli_insert_id($this->db->con); // last inserted id
            $result = mysqli_query($this->db->con,"SELECT * FROM users WHERE uid = $uid");
            // return details
            return mysqli_fetch_array($result);
        } else {
            return false;
        }
    }
 
    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) {
        $result = mysqli_query($this->db->con,"SELECT * FROM users WHERE email = '$email'") or die(mysqli_connect_errno());
        // check for result 
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            $result = mysqli_fetch_array($result);
            $salt = $result['salt'];
            $encrypted_password = $result['encrypted_password'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password
            if ($encrypted_password == $hash) {
                return $result;
            }
        } else {
            return false;
        }
    }
 
    /**
     * Check user is exist or not
     */
    public function isUserExisted($email) {
        $result = mysqli_query($this->db->con,"SELECT email from users WHERE email = '$email'");
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            // user exis
            return true;
        } else {
            // user not exist
            return false;
        }
    }
 
    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {
 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 
    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {
 
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
        return $hash;
    }
	
	public function storeSite($email, $lat, $lon, $title, $description, $rating, $permission, $distant, $nearby, $immediate, $feature1, $feature2, $feature3, $feature4, $feature5, $feature6, $feature7, $feature8, $feature9, $feature10){
		$ucid = uniqid('', true);
        $result = mysqli_query($this->db->con,"INSERT INTO campsites(unique_cid, site_admin, latitude, longitude, title, description, rating, created_at, permission, distantTerrain, nearbyTerrain, immediateTerrain, feature1, feature2, feature3, feature4, feature5, feature6, feature7, feature8, feature9, feature10, active) VALUES('$ucid', '$email', '$lat', '$lon', '$title', '$description', '$rating', NOW(), $permission,'$distant','$nearby', '$immediate', $feature1, $feature2, $feature3, $feature4, $feature5, $feature6, $feature7, $feature8, $feature9, $feature10, '1')");
		
        // check for result
        if ($result) {
            // gettig the details
            $cid = mysqli_insert_id($this->db->con); // last inserted id
            $result = mysqli_query($this->db->con,"SELECT * FROM campsites WHERE cid = $cid");
            
			// return details
            return mysqli_fetch_array($result);
        } else {
            return false;
        }
	}
	
	public function nearbySiteExist($lat, $lon, $latLowerBound, $latUpperBound, $lonLowerBound, $lonUpperBound){
			
		$distLatUp = $lat+0.001;
		$distLonRight = $lon+0.001;
		$distLatDown = $lat-0.001;
		$distLonLeft = $lon-0.001;
		$result = mysqli_query($this->db->con, "SELECT unique_cid from campsites WHERE ( latitude >= '$latLowerBound' AND latitude <= '$latUpperBound') AND ( longitude <= '$lonUpperBound' AND longitude >= '$lonLowerBound') AND active = '1'");
        $no_of_rows = mysqli_num_rows($result);
		
		if ($no_of_rows > 0) {
            while ($row = $result->fetch_assoc()) {
				$new_array[] = $row;
			}	
			return $new_array;
        } else {
            return true;
        }
	}
	
	public function linkSiteToOwner($uid, $ucid, $relat, $rating){
		$uoid = uniqid('', true);
		
		if($rating == NULL){
		
				$JNCT = mysqli_query($this->db->con,"INSERT INTO user_has_campsites(unique_oid, user_fk, campsite_fk, relationship, created_at, active) VALUES('$uoid', '$uid', '$ucid', '$relat', NOW(), 1)");
		} else {		
			
			$JNCT = mysqli_query($this->db->con,"INSERT INTO user_has_campsites(unique_oid, user_fk, campsite_fk, relationship, created_at, active, rating) VALUES('$uoid', '$uid', '$ucid', '$relat', NOW(), 1, '$rating')");
		}
		
		// check for result
        if ($JNCT) {
            // gettig the details
            $oid = mysqli_insert_id($this->db->con); // last inserted id
            $result = mysqli_query($this->db->con,"SELECT * FROM user_has_campsites WHERE oid = $oid");
            
			// return details
            return mysqli_fetch_array($result);
        } else {
            return false;
        }
	}
	
	public function checkForExistingLink($receiver_uid, $send_cid, $relat){
	
		$result = mysqli_query($this->db->con,"SELECT * FROM user_has_campsites WHERE user_fk = '$receiver_uid' AND campsite_fk = '$send_cid' AND relationship = '$relat'");

		$no_of_rows = mysqli_num_rows($result);
		
		if ($no_of_rows > 0) {
            	
			return false;
        } else {
            return true;
        }
	}
	
	public function fetchSites($uid, $relat){
		
		$result = mysqli_query($this->db->con, "SELECT * FROM campsites INNER JOIN user_has_campsites ON user_has_campsites.campsite_fk = campsites.unique_cid INNER JOIN users ON users.unique_uid = user_has_campsites.user_fk WHERE user_has_campsites.user_fk = '$uid' AND user_has_campsites.active = '1' AND campsites.active = '1'");
		
        // check for result 
        $no_of_rows = mysqli_num_rows($result);
		
		//echo mysqli_errno($this->db->con);
		//echo mysqli_error($this->db->con);
		
		//echo $no_of_rows;
		
        if ($no_of_rows > 0) {
            while ($row = $result->fetch_assoc()) {
			//print_r($row);
			$new_array[] = $row;
			}	
			return $new_array;
			//return mysqli_fetch_array($result);
        } else if ($no_of_rows == 0) {
			return true;
		} else {
            return false;
        }
	}
	
	public function fetchUnknownSites($uid, $relatOwn, $relatTrade){
		
		$result = mysqli_query($this->db->con, "SELECT * FROM campsites INNER JOIN user_has_campsites ON user_has_campsites.campsite_fk = campsites.unique_cid INNER JOIN users ON users.unique_uid = user_has_campsites.user_fk WHERE ((user_has_campsites.user_fk != '$uid' AND user_has_campsites.relationship = '90') OR (user_has_campsites.user_fk != '$uid' AND user_has_campsites.relationship != '45')) AND user_has_campsites.active = '1' AND campsites.active = '1'");
		
        // check for result 
        $no_of_rows = mysqli_num_rows($result);
		
        if ($no_of_rows > 0) {
		
            while ($row = $result->fetch_assoc()) {
				$new_array[] = $row;
			}	
			return $new_array;
        } else if ($no_of_rows == 0){
			return true;
		} else {
            return false;
        }
	}
	
	public function fetchToken($cid){
	
		$result = mysqli_query($this->db->con, "SELECT token FROM users INNER JOIN user_has_campsites ON user_has_campsites.campsite_fk = campsites.unique_cid INNER JOIN users ON users.unique_uid = user_has_campsites.user_fk WHERE ((user_has_campsites.campsite_fk != '$cid' AND user_has_campsites.relationship = '90') OR (user_has_campsites.campsite_fk != '$cid' AND user_has_campsites.relationship != '45')) AND user_has_campsites.active = '1' AND campsites.active = '1'");
		
		if($result){
			return true;
        } else {
            return false;
        }
	}
	
	public function createRequest($uid, $tradeStatus, $send_fk, $recieve_fk, $reciever_fk){
	
		$utid = uniqid('', true);
		
		$result = mysqli_query($this->db->con, "INSERT INTO trades (unique_tid, status, sender_uid_fk, reciever_uid_fk, send_cid_fk, recieve_cid_fk, created_at) VALUES ('$utid', '$tradeStatus', '$uid', '$reciever_fk', '$send_fk', '$recieve_fk', NOW())");
	
		if ($result) {
            
            return true;
        } else {
            return false;
        }
	
	}
	
	public function getOwnerOfSite($cid){
	
		//get user id using latlng and relationship
		$result = mysqli_query($this->db->con, "SELECT user_fk, email FROM user_has_campsites INNER JOIN users ON user_has_campsites.user_fk = users.unique_uid INNER JOIN campsites ON user_has_campsites.campsite_fk = campsites.unique_cid WHERE (campsites.unique_cid = '$cid' AND user_has_campsites.relationship = '90')");
	
		if ($result) {
            return mysqli_fetch_array($result);
        } else {
            return false;
        }
	
	}
	
	public function checkForExistingTrade($uid, $tradeStatus, $reciever_uid_fk, $send_cid_fk, $recieve_cid_fk){
	
		$result = mysqli_query($this->db->con, "SELECT 'unique_tid' FROM trades WHERE (status = '$tradeStatus' AND sender_uid_fk = '$uid' AND reciever_uid_fk = '$reciever_uid_fk' AND send_cid_fk = '$send_cid_fk' AND recieve_cid_fk = '$recieve_cid_fk')");

		if ($result) {    
            return mysqli_fetch_array($result);
        } else {
            return false;
        }
	
	}
	
	public function getAllTrades($uid){
	
		//$result = mysqli_query($this->db->con, "SELECT * FROM trades INNER JOIN campsites ON trades.send_cid_fk = campsites.unique_cid AND trades.recieve_cid_fk = campsites.unique_cid WHERE(sender_uid_fk = '$uid' OR reciever_uid_fk = '$uid') AND campsites.active '1'");
		
		$result = mysqli_query($this->db->con, "SELECT * FROM trades WHERE(sender_uid_fk = '$uid' OR reciever_uid_fk = '$uid')");
	
		$no_of_rows = mysqli_num_rows($result);
	
		if ($no_of_rows > 0) {
            while ($row = $result->fetch_assoc()) {
				$new_array[] = $row;
			}	
			return $new_array;           
        } else if ($no_of_rows == 0) {
			return true;
		} else {
            return false;
        }
	} 
	
	public function updateTrade($tid, $tradeStatus){
	
		$result = mysqli_query($this->db->con, "UPDATE trades SET status = '$tradeStatus' WHERE unique_tid = '$tid'");
		
		if($result) {
			return true;
		} else {
			return false;
		}
	}
	
	public function deleteSite($cid, $active){
	
		$result = mysqli_query($this->db->con, "UPDATE campsites SET active = '$active' WHERE unique_cid = '$cid'");
		
		$userHas = mysqli_query($this->db->con, "UPDATE user_has_campsites SET active = '0' WHERE campsite_fk = '$cid'");
	
		if($result) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public function addImages($image1, $image2, $image3, $cid){
	
		$uiid = uniqid('', true);
	
		$result = mysqli_query($this->db->con, "INSERT INTO images_of_campsites (active, unique_id, image1, image2, image3, campsite_fk, uploaded_at) VALUES ('1', '$uiid', '$image1', '$image2', '$image3', '$cid', NOW())");
	
		if($result) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public function fetchImages($unique_cid){

		$result = mysqli_query($this->db->con, "SELECT iid, image1, image2, image3 FROM images_of_campsites INNER JOIN user_has_campsites ON user_has_campsites.campsite_fk = images_of_campsites.campsite_fk WHERE images_of_campsites.campsite_fk = '$unique_cid' AND images_of_campsites.active = '1'");
	
		// check for result 
        $no_of_rows = mysqli_num_rows($result);
		
        if ($no_of_rows > 0) {
		
            while ($row = $result->fetch_assoc()) {
				$new_array[] = $row;
			}	
			return $new_array;
        } else if ($no_of_rows == 0){
			return true;
		} else {
            return false;
        }
	
	}
	
	public function updateImages($cid, $image, $imageNum){
	
		$result = mysqli_query($this->db->con, "UPDATE images_of_campsites SET image+'$imageNum' = '$image' WHERE campsite_fk = '$cid'");
		
		if($result) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public function fetchImagesCid($cid){

		$result = mysqli_query($this->db->con, "SELECT * FROM images_of_campsites WHERE campsite_fk = '$cid'");
	
		// check for result 
        $no_of_rows = mysqli_num_rows($result);
		
        if ($no_of_rows > 0) {
		
            while ($row = $result->fetch_assoc()) {
				$new_array[] = $row;
			}	
			return $new_array;
        } else if ($no_of_rows == 0){
			return true;
		} else {
            return false;
        }
	
	}
	
	public function updateSite($cid, $title, $description, $feature1, $feature2, $feature3, $feature4, $feature5, $feature6, $feature7, $feature8, $feature9, $feature10){
	
		$result = mysqli_query($this->db->con, "UPDATE campsites SET title = '$title', description = '$description', updated_at = NOW(), feature1 = $feature1, feature2 = $feature2, feature3 = $feature3, feature4 = $feature4, feature5 = $feature5, feature6 = $feature6, feature7 = $feature7, feature8 = $feature8, feature9 = $feature9, feature10 = $feature10  WHERE unique_cid = '$cid'");
	
		if($result) {
			return true;
		} else {
			return false;
		}
	}
	
	public function updateOwnedRating($uid, $cid, $rating){
	
		$result = mysqli_query($this->db->con, "UPDATE user_has_campsites SET rating = '$rating', updated_at = NOW() WHERE user_fk = '$uid' AND campsite_fk = '$cid'");
		
		if($result) {
			return true;
		} else {
			return false;
		}
	}
	
	public function checkPopularity($cid){
	
		$result = mysqli_query($this->db->con, "SELECT * FROM user_has_campsites WHERE campsite_fk = '$cid' AND active = '1'");
	
		// check for result 
        $no_of_rows = mysqli_num_rows($result);
		
        if ($no_of_rows > 0) {
	
			return $no_of_rows;
        } else {
            return false;
        }
	}
	
	public function fetchRatings($unique_cid){
	
		$result = mysqli_query($this->db->con, "SELECT rating FROM user_has_campsites WHERE campsite_fk = '$unique_cid' AND active = '1' AND rating IS NOT NULL");
	
		$no_of_rows = mysqli_num_rows($result);
		
        if ($no_of_rows > 0) {
		
			//echo "no of rows ".$no_of_rows."\n";

			while ($row = mysqli_fetch_assoc($result)){ 
				//echo $row['rating'];
				$array[] = $row['rating'];
			}
						
			$totalRating = array_sum($array);
			
			//echo "total rating ".$totalRating."\n";
			
			$avrRating = ($totalRating/$no_of_rows);
			
			//echo "avr rating ".$avrRating."\n";
			
			$result_array[0] = $avrRating;
			$result_array[1] = $no_of_rows;
			
			return $result_array;
        } else {
            return false;
        }
	
	}
	
	public function updateRating($active, $uid, $cid, $rating){
	
		$result = mysqli_query($this->db->con, "UPDATE user_has_campsites SET rating = '$rating', updated_at = NOW() WHERE user_fk = '$uid' AND campsite_fk = '$cid'");  
	
		if($result) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getQuestions(){
	
		$result = mysqli_query($this->db->con, "SELECT question, answer1, answer2, answer3, answer4 FROM questions WHERE active = '1'");

		// check for result 
        $no_of_rows = mysqli_num_rows($result);
		
        if ($no_of_rows > 0) {
		
            while ($row = $result->fetch_assoc()) {
				$new_array[] = $row;
			}	
			return $new_array;
        } else if ($no_of_rows == 0){
			return true;
		} else {
            return false;
        }
	}
	
	public function getUserDetails($email){
	
		$result = mysqli_query($this->db->con, "SELECT name, email, bio, profile_pic, question1, question2, question3, question4, question5, question6, question7, question8, question9 FROM users WHERE email = '$email'");

		if($result){
			return mysqli_fetch_array($result);
		} else {
			return false;
		}
	}
	
	public function updateAnswers($uid, $question1, $question2, $question3, $question4, $question5, $question6, $question7, $question8, $question9){
	
		$result = mysqli_query($this->db->con, "UPDATE users SET question1 = '$question1', question2 = '$question2', question3 = '$question3', question4 = '$question4', question5 = '$question5', question6 = '$question6', question7 = '$question7', question8 = '$question8', question9 = '$question9', updated_at = NOW() WHERE unique_uid = '$uid'");

		if($result){
			return true;
		} else {
			return false;
		}
	}
	
	public function getUserByEmail($email){
	
		$result = mysqli_query($this->db->con, "SELECT name, email, bio, profile_pic FROM users WHERE email = '$email'");

		if($result){
			return mysqli_fetch_array($result);
		} else {
			return false;
		}
	}
	
	public function updateProfile($uid, $bio, $profile_pic){
	
		$result = mysqli_query($this->db->con, "UPDATE users SET bio = '$bio', profile_pic = '$profile_pic', updated_at = NOW() WHERE unique_uid = '$uid'");

		if($result){
			return true;
		} else {
			return false;
		}
	
	}
	
	public function deleteTrades($cid){
	
		$result = mysqli_query($this->db->con, "UPDATE trades SET status = '3' WHERE send_cid_fk = '$cid' OR recieve_cid_fk = '$cid'");

		if($result){
			return true;
		} else {
			return false;
		}	
	}
}
 
?>