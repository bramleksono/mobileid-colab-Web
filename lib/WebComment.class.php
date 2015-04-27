<?php

//Parse Backend
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseFile;

class WebComment {
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
    
    public function createComment($form) {
        $time = $this->getTime();
        
		$web_comment_obj = new ParseObject("web_comment");
		$web_comment_obj->set("documentnumber", $form["documentnumber"]);
		$web_comment_obj->set("poster", $form["poster"]);
		$web_comment_obj->set("comment", strip_tags($form["comment"]));
		$web_comment_obj->set("modified", $time);
		if (isset($this->document)) {
            $file = $this->document;
            $web_comment_obj->set("file", $file);
        }
        
    	try {
    		$web_comment_obj->save();
    		//retrieve registration code
    		$regcode = $web_comment_obj->getObjectId();
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
    
    private function searchWebCommentDB($column, $query) {
		$web_project_que = new ParseQuery("web_comment");
    	$web_project_que->equalTo($column, $query);
    	$results = $web_project_que->find();
    	return $results;
	}
	
    public function findCommentsbydocument($documentnumber) {
        return $this->searchWebCommentDB("documentnumber", $documentnumber);;
    }
}