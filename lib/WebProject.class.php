<?php

//Parse Backend
use Parse\ParseObject;
use Parse\ParseQuery;

class WebProject {
	private function searchWebDB($column, $query) {
		$web_project_que = new ParseQuery("web_project");
    	$web_project_que->equalTo($column, $query);
    	$results = $web_project_que->find();
    	return $results;
	}

	private function searchOneDB($column, $query) {
		$web_project_que = new ParseQuery("web_project");
    	$web_project_que->equalTo($column, $query);
    	$results = $web_project_que->first();
    	return $results;
	}
	
	public function fetchCreatorDB($idnumber) {
		$results = $this->searchWebDB("creator", $idnumber);
    	$this->creator = $results;
    	return count($results);
	}

	public function fetchClientDB($idnumber) {
		$results = $this->searchWebDB("client", $idnumber);
    	$this->client = $results;
    	return count($results);
	}
	
	public function fetchProjectDB($projectnumber) {
	    $results = $this->searchOneDB("projectnumber", $projectnumber);
    	if ($results) {
    		return $results;
    	} else {
    		return null;
    	}
	}
	
	public function getProject($project) {
		$query = new ParseQuery("web_project");
		try {
		  $result = $query->get($project);
		  return $result;
		} catch (ParseException $ex) {
		}
	}
	
	public function storeProjectDB($form,$currentmilestone) {
		$time = $this->getTime();
		
		$web_project_obj = new ParseObject("web_project");
		$web_project_obj->set("projectnumber", $form["projectnumber"]);
		$web_project_obj->set("creator", $form["creator"]);
		$web_project_obj->set("projectname", $form["projectname"]);
		$web_project_obj->set("client", $form["client"]);
		$web_project_obj->set("milestone", $form["milestone"]);
		$web_project_obj->set("currentmilestone", strval($currentmilestone));
		$web_project_obj->set("modified", $time);
    	
    	try {
    		$web_project_obj->save();
    		//retrieve registration code
    		$regcode = $web_project_obj->getObjectId();
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
	
	public function listCreatorProject() {
    	return $this->creator;
	}
	
	public function listClientProject() {
    	return $this->client;
	}
	
	public function getTime() {
		$current_date = new DateTime("now");
        return $current_date->format('Y-m-d H:i:s');		
	}
}