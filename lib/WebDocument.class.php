<?php

//Parse Backend
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseFile;

class WebDocument {
	private function getDocumentNumber($time,$idnumber, $documentname, $milestone) {
        $string = $time.$idnumber.$documentname.$milestone;
    	return hash('md5', $string);
    }

	private function searchWebDocumentDB($column, $query) {
		$web_project_que = new ParseQuery("web_document");
    	$web_project_que->equalTo($column, $query);
    	$results = $web_project_que->find();
    	return $results;
	}

	private function searchOneDocumentDB($column, $query) {
		$web_project_que = new ParseQuery("web_document");
    	$web_project_que->equalTo($column, $query);
    	$results = $web_project_que->first();
    	return $results;
	}

    public function getTime() {
		$current_date = new DateTime("now");
        return $current_date->format('Y-m-d H:i:s');		
	}

    private function cleanText($text) {
        $str = preg_replace('/[[:^print:]]/', '', $text);
        $str = preg_replace('/\s+/', '', $str);
        return $str;
    }
    
    public function uploadFile($content,$filename) {
        $filename = $this->cleanText($filename);
        
        $file = ParseFile::createFromData($content, $filename);
        $file->save();
        $this->document = $file;
        return $file->getURL();
    }
    
    public function createDocument($form) {
        $file = $this->document;
		$time = $this->getTime();
		$documentnumber = $this->getDocumentNumber($time,$form["creator"], $form["documentname"], $form["milestonenumber"]);
        
		$parentproject = new ParseObject("web_project", $form["project_objectid"]);
        
		$web_document_obj = new ParseObject("web_document");
		$web_document_obj->set("project", $parentproject); //parent project
		$web_document_obj->set("milestone", $form["milestonenumber"]);
		$web_document_obj->set("documentnumber", $documentnumber);
		$web_document_obj->set("creator", $form["creator"]);
		$web_document_obj->set("documentname", $form["documentname"]);
		$web_document_obj->set("description", $form["description"]);
		$web_document_obj->set("signer", $form["signer"]);
		$web_document_obj->set("originalfile",$file);
		$web_document_obj->set("originalhash", $form["filehash"]);
		$web_document_obj->set("modified", $time);
        
    	try {
    		$web_document_obj->save();
    		//retrieve registration code
    		$regcode = $web_document_obj->getObjectId();
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

    public function parseDocument($document) {
	    
        $data = array(  "project" => $document->get('project'),
                        "milestone" => $document->get('milestone'),
                        "documentnumber" =>$document->get('documentnumber'),
                        "creator" =>$document->get('creator'),
                        "documentname" =>$document->get('documentname'),
                        "description" =>$document->get('description'),
                        "originalfile" => $document->get('originalfile'),
                        "originalhash" => $document->get('originalhash'),
                        "signer" =>$document->get('signer'),
                        "signedfile" => $document->get('signedfile'),
                        "signedhash" => $document->get('signedhash'),
                        "signature" => $document->get('signature'),
                        "signedtime" => $document->get('signedtime'),
                        "modified" =>$document->get('modified')
                        );
        
        return $data;
    }
    
    public function findDocumentsbyproject($project) {
        return $this->searchWebDocumentDB("project", $project);;
    }
    
    public function fetchDocumentDB($documentnumber) {
        return $this->searchOneDocumentDB("documentnumber", $documentnumber);;
    }
    
    public function getProject($project) {
        $objectid = $project->getObjectId();
        $projectcontroller = new WebProject();
        return $result = $projectcontroller->getProject($objectid);
    }
    
    public function saveSignedDocument($form, $document) {
        $file = $this->document;
		$time = $this->getTime();
		
		$document->set("signedtime", $form["signedtime"]); //parent project
		$document->set("signedhash", $form["signedhash"]);
		$document->set("signedfile",$file);
		$document->set("signature", $form["signature"]);
        
    	try {
    		$document->save();
    		//retrieve registration code
    		$regcode = $document->getObjectId();
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
}