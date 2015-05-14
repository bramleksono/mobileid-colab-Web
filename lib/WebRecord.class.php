<?php

//Parse Backend
use Parse\ParseObject;
use Parse\ParseQuery;

class WebRecord {
    private function getTime() {
		$current_date = new DateTime("now");
        return $current_date->format('Y-m-d H:i:s');		
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
	
	public function recordlogin($idnumber, $action) {
	    $category = "login";
	    switch ($action) {
	        case "request":
	            $message = "Login request for user ".$idnumber.".";
	            break;
	        case "success":
	            $message = "Successful login for user ".$idnumber.".";
	            break;
	    }
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recordproject($idnumber, $projectname, $projectnumber, $action) {
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
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recordmilestone($idnumber, $milestone, $projectnumber, $action) {
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
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recordverify($idnumber, $action) {
	    $category = "verify";
	    switch ($action) {
	        case "request":
	            $message = "Verify request for user ".$idnumber.".";
	            break;
	        case "success":
	            $message = "Successful verify request for user ".$idnumber.".";
	            break;
	    }
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recorddocument($idnumber, $documentnumber, $action) {
	    $category = "document";
	    switch ($action) {
	        case "create":
	            $message = "Document with number ".$documentnumber." added.";
	            break;
	        case "delete":
	            $message = "Document with number ".$documentnumber." deleted.";
	            break;
	    }
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
	
	public function recordsigning($idnumber, $documentnumber, $action) {
	    $category = "signing";
	    switch ($action) {
	        case "request":
	            $message = "Signing request for document number ".$documentnumber.".";
	            break;
	        case "success":
	            $message = "Successful signing for document number ".$documentnumber.".";
	            break;
	    }
	    
	    $form = array(  "idnumber" => $idnumber,
	                    "category" => $category,
	                    "action" => $action,
	                    "message" => $message,
                    );
	    
	    return $this->storeRecordDB($form);
	}
}