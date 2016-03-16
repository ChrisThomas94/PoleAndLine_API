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
    public function storeUser($name, $email, $password) {
        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
        $result = mysqli_query($this->db->con,"INSERT INTO users(unique_uid, name, email, encrypted_password, salt, created_at) VALUES('$uuid', '$name', '$email', '$encrypted_password', '$salt', NOW())");
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
	
	public function storeSite($uid, $lat, $lon, $title, $description, $rating, $feature1, $feature2, $feature3, $feature4, $feature5, $feature6, $feature7, $feature8, $feature9, $feature10){
		$ucid = uniqid('', true);
        $result = mysqli_query($this->db->con,"INSERT INTO campsites(unique_cid, site_admin, latitude, longitude, title, description, rating, created_at, feature1, feature2, feature3, feature4, feature5, feature6, feature7, feature8, feature9, feature10, active) VALUES('$ucid', '$uid', '$lat', '$lon', '$title', '$description', '$rating', NOW(), $feature1, $feature2, $feature3, $feature4, $feature5, $feature6, $feature7, $feature8, $feature9, $feature10, '1')");
		
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
	
	public function nearbySiteExist($lat, $lon){
			
		$distLat = $lat+0.0001;
		$distLon = $lon+0.0001;
		$result = mysqli_query($this->db->con,"SELECT unique_cid from campsites WHERE (latitude >= $lat AND latitude <= '$distLat') AND (longitude >= $lon AND longitude <= '$distLon')");
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            // nearby sites exist
            return true;
        } else {
            // no nearby sites exist
            return false;
        }
	}
	
	public function linkSiteToOwner($uid, $ucid, $relat){
		$uoid = uniqid('', true);
		$JNCT = mysqli_query($this->db->con,"INSERT INTO user_has_campsites(unique_oid, user_fk, campsite_fk, relationship, created_at, active) VALUES('$uoid', '$uid', '$ucid', '$relat', NOW(), '1')");
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
	
	public function getActiveTrades($uid, $tradeStatus){
	
		$result = mysqli_query($this->db->con, "SELECT * FROM trades WHERE ((sender_uid_fk = '$uid' OR reciever_uid_fk = '$uid') AND status = '$tradeStatus')");
	
		$no_of_rows = mysqli_num_rows($result);
	
		if ($no_of_rows > 0) {
            while ($row = $result->fetch_assoc()) {
				$new_array[] = $row;
			}	
			return $new_array;           
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
	
		if($result) {
			return true;
		} else {
			return false;
		}
	
	}
}
 
?>