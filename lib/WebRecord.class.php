<?php

//Parse Backend
use Parse\ParseObject;
use Parse\ParseQuery;

class WebRecord {
    private function getTime() {
		$current_date = new DateTime("now");
        return $current_date->format('Y-m-d H:i:s');		
	}
	
	private function get_ip_address(){
	    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
	        if (array_key_exists($key, $_SERVER) === true){
	            foreach (explode(',', $_SERVER[$key]) as $ip){
	                $ip = trim($ip); // just to be safe
	
	                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
	                    return $ip;
	                }
	            }
	        }
	    }
	}
    
    private function searchRecordDB($column, $query) {
		$web_record_obj = new ParseQuery("web_record");
		$web_record_obj->limit(1000); // set limit to 1000 results (default are 100 results)
    	$web_record_obj->equalTo($column, $query);
    	$results = $web_record_obj->find();
    	return $results;
	}
    
	private function storeRecordDB($form) {
		$time = $this->getTime();
		
		$web_record_obj = new ParseObject("web_record");
		$web_record_obj->set("idnumber", $form["idnumber"]);
		$web_record_obj->set("category", $form["category"]);
		$web_record_obj->set("action", $form["action"]);
		$web_record_obj->set("message", $form["message"]);
		$web_record_obj->set("modified", $time);
    	
    	try {
    		$web_record_obj->save();
    		//retrieve registration code
    		$regcode = $web_record_obj->getObjectId();
    		//echo 'New object created with objectId: ' . $regcode;
    		$result=1;
    	} catch (ParseException $ex) {
    		// Execute any logic that should take place if the save fails.
    		// error is a ParseException object with an error code and message.
    		// echo 'Failed to create new object, with error message: ' + $ex->getMessage();
    		$result=0;
    	}
    	return $result;
	}
	
	public function sortRecordDBbyAction($idnumber) {
		$querys = $this->searchRecordDB("idnumber", $idnumber);
		
		$category = array("login", "project", "milestone", "verify", "document", "signing");
		
		//initialize output
		foreach ($category as $cat) {
			$sortedrecord[$cat] = "";
		}
		
		foreach ($querys as $query) {
			$querycategory = $query->get("category");
			$querymessage = $query->get("message");
			$querytime = $query->get("modified");
			//classify record based on category
			for ($categoryiteration = 0; $categoryiteration < count($category); $categoryiteration++) {
				if ($querycategory == $category[$categoryiteration]) {
					//match category
					$sortedrecord[$querycategory] = $sortedrecord[$querycategory]. $querymessage. " Time ". $querytime. " WIB.\n";
				}
			}
		}
		return $sortedrecord;
	}
	
	public function recordlogin($idnumber, $action) {
		$clientip = $this->get_ip_address();
	    $category = "login";
	    switch ($action) {
	        case "request":
	            $message = "Login request for user ".$idnumber.".";
	            break;
	        case "success":
	            $message = "Successful login for user ".$idnumber.".";
	            break;
	    }
	    $message = $message. " Client IP address: ".$clientip.".";
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recordproject($idnumber, $projectname, $projectnumber, $action) {
		$clientip = $this->get_ip_address();
	    $category = "project";
	    switch ($action) {
	        case "create":
	            $message = "Project ".$projectname." with project number ".$projectnumber." created.";
	            break;
	        case "delete":
	            $message = "Project ".$projectname." with project number ".$projectnumber." deleted.";
	            break;
	        case "start":
	            $message = "Project ".$projectname." with project number ".$projectnumber." started.";
	            break;
	        case "confirm":
	            $message = "User ".$idnumber." sent confirmation to project ".$projectname." with project number ".$projectnumber.".";
	            break;
	        case "finish":
	            $message = "Project ".$projectname." with project number ".$projectnumber." finished.";
	            break;
	    }
	    $message = $message. " Client IP address: ".$clientip.".";
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recordmilestone($idnumber, $milestone, $projectnumber, $action) {
		$clientip = $this->get_ip_address();
	    $category = "milestone";
	    switch ($action) {
	        case "create":
	            $message = "Milestone ".$milestone." created for project number ".$projectnumber.".";
	            break;
	        case "delete":
	            $message = "Milestone ".$milestone." deleted for project number ".$projectnumber.".";
	            break;
	        case "next":
	            $message = "Milestone ".$milestone." begin for project number ".$projectnumber.".";
	            break;
	    }
	    $message = $message. " Client IP address: ".$clientip.".";
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recordverify($idnumber, $action) {
		$clientip = $this->get_ip_address();
	    $category = "verify";
	    switch ($action) {
	        case "request":
	            $message = "Verify request for user ".$idnumber.".";
	            break;
	        case "success":
	            $message = "Successful verify request for user ".$idnumber.".";
	            break;
	        case "view":
	            $message = "Successful view identity for user ".$idnumber.".";
	            break;
	    }
	    $message = $message. " Client IP address: ".$clientip.".";
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recorddocument($idnumber, $documentnumber, $action) {
		$clientip = $this->get_ip_address();
	    $category = "document";
	    switch ($action) {
	        case "create":
	            $message = "Document with number ".$documentnumber." added.";
	            break;
	        case "delete":
	            $message = "Document with number ".$documentnumber." deleted.";
	            break;
	    }
	    $message = $message. " Client IP address: ".$clientip.".";
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recordsigning($idnumber, $documentnumber, $action) {
		$clientip = $this->get_ip_address();
	    $category = "signing";
	    switch ($action) {
	        case "request":
	            $message = "Signing request for document number ".$documentnumber.".";
	            break;
	        case "success":
	            $message = "Successful signing for document number ".$documentnumber.".";
	            break;
	    }
	    $message = $message. " Client IP address: ".$clientip.".";
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
}