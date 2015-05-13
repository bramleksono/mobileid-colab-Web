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
	
	public function savelogin($idnumber, $action) {
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
}