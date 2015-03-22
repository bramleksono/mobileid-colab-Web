a<?php

//Parse Backend
use Parse\ParseObject;
use Parse\ParseQuery;

class WebProject {
    public function WebProject($PID){
		$this->PID = $PID;
	}
	
	public function fetchProjectDB() {
	    $PID = $this->PID;
	    $si_pid_que = new ParseQuery("web_project");
    	$si_pid_que->equalTo("pid", $PID);
    	$results = $si_pid_que->find();
	    if ($results) {
	    	$this->PIDDB = $results[0];
	    }
	    else $this->PIDDB = null;
	}
	
	public function storePIDDB($PID,$data,$time,$iv) {
	    $si_pid_obj = new ParseObject("si_pid");
    	$si_pid_obj->set("pid", $PID);
    	$si_pid_obj->set("data", utf8_encode($data));
    	$si_pid_obj->set("created", $time);
    	$si_pid_obj->set("iv", utf8_encode($iv));
    	
    	try {
    		$si_pid_obj->save();
    		//retrieve registration code
    		$regcode = $si_pid_obj->getObjectId();
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

	private function decryptPIDDB() {
	    $encrypted = $this->PIDDB;
    	$data = utf8_decode($encrypted->get('data'));
    	$iv =  utf8_decode($encrypted->get('iv'));
    	$key=getkey($encrypted->get('created'));
		return json_decode(decryptdb($data,$iv,$key), true);
	}
	
	public function isExist() {
	    $result = $this->PIDDB;
	    if (empty($result)) {
	        return false;
	    } else {
	        return true;
	    }
	}
	
	public function getPID(){
        $data = $this->decryptPIDDB();
		return $data;	    
	}
}