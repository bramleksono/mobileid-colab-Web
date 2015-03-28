<?php

require 'WebProject.class.php';  // Handling User Project Database

class WebController {
	private function getProjectNumber($time,$idnumber) {
        $string = $time.$idnumber;
    	return hash('md5', $string);
    }
    
    public function parseProject($project) {
     	$projectname = $project->get('projectname');
        $projectnumber = $project->get('projectnumber');
        $creator = $project->get('creator');
        $client = $project->get('client');
        $modified = $project->get('modified');
    	$milestone = json_decode($project->get('milestone'), true);
    	$milestonenumber = $project->get('currentmilestone');
	    $currentmilestone = $milestone[$milestonenumber-1];
	    
        $data = array(  "projectname" => $projectname,
                        "projectnumber" =>$projectnumber,
                        "creator" =>$creator,
                        "client" =>$client,
                        "modified" =>$modified,
                        "currentmilestone" =>$currentmilestone,
                        "milestonenumber" => $milestonenumber,
                        "milestone" =>$milestone,
                        );
        return $data;
    }
    
    public function WebController($idnumber) {
        $this->idnumber = $idnumber;
    }
    
    public function getInitial($idnumber) {
    	global $CAuserinitial;
    	
    	if ($idnumber == "") {
    		echo "Failed. Reason : You must provide ID Number";
    		die();
    	}
    	
    	$form = array ("userinfo" => array ("nik" => $idnumber));
    	$form = json_encode($form);
    	
    	$result = sendjson($form,$CAuserinitial);
    	$result = json_decode($result, true);
    	if ($result) {
    		if ($result["success"]) {
    			echo "CA Initial Check".PHP_EOL;
    			echo "ID Number " . $idnumber . " with initial " . $result["initial"];			
    		} else {
    			echo "CA Initial Check".PHP_EOL;
    			echo "Failed. Reason : ".$result["reason"];
    		}
    	} else 
    		echo "Failed. Reason : Cannot connect to CA";        
    }
    
    public function createProject($form) {
        $project = new WebProject();
        $time = $project->getTime();
        $idnumber = $this->idnumber;
        
        $projectnumber = $this->getProjectNumber($time,$idnumber);
        $form["projectnumber"] = $projectnumber;
        
		$firstmilestone = 1;
        
        return $project->storeProjectDB($form,$firstmilestone);
    }
    
    public function showProject() {
        $idnumber = $this->idnumber;
        $project = new WebProject();
        
        //get list as creator
        $creatorcount = $project->fetchCreatorDB($idnumber);
        $creatortext = '';
        if ($creatorcount > 0) {
            $creatorlist = $project->listCreatorProject();
            
            $i = 1;
            
            foreach ($creatorlist as $list) {
                $data = $this->parseProject($list);
                $creatortext = $creatortext.'<tr class="primary"><td>'.$i.'</td><td>'.$data["client"].'</td><td><a href="project/'.$data["projectnumber"].'">'.$data["projectname"].'</td><td>'.$data["currentmilestone"].'</td><td>'.$data["modified"].'</td><td>';
                $i++;
            }
        }
        
        //get list as client
        $clientcount = $project->fetchClientDB($idnumber);
        $clienttext = '';
        if ($clientcount > 0) {
            $clientlist = $project->listClientProject();
            
            $i = 1;
            foreach ($clientlist as $list) {
                $data = $this->parseProject($list);             
                $clienttext = $clienttext.'<tr class="primary"><td>'.$i.'</td><td>'.$data["creator"].'</td><td><a href="project/'.$data["projectnumber"].'">'.$data["projectname"].'</td><td>'.$data["currentmilestone"].'</td><td>'.$data["modified"].'</td><td>';
                $i++;
            }
        }
        
        $data = array(  "creatortext" => $creatortext,
                        "clienttext" =>$clienttext);
        return $data;
    }
    
    public function detailProject($projectnumber) {
        $idnumber = $this->idnumber;
        $project = new WebProject();
        $result =  $project->fetchProjectDB($projectnumber);
        if ($result) {
            $data = $this->parseProject($result);
            return $data;            
        } else {
            return null;
        }
    }
    
    public function unparsedProject($projectnumber) {
        $idnumber = $this->idnumber;
        $project = new WebProject();
        $result =  $project->fetchProjectDB($projectnumber);
        if ($result) {
            return $result;            
        } else {
            return null;
        }        
    }
    
    public function checkRole($project, $idnumber) {
        //check if user is involved in project
        $creatorid = $project["creator"];
        $clientid = $project["client"];
        if ($idnumber == $creatorid) {
            return 1;
        } else if ($idnumber == $clientid) {
            return 2;
        } else {
            //illegal user
            return 0;
        }        
    }
    
    public function setIDNumberbyRole($project, $role) {
        $project = $this->parseProject($project);
        
        //check if user is involved in project
        $creatorid = $project["creator"];
        $clientid = $project["client"];
        if ($role == "creator") {
            return $creatorid;
        } else if ($role == "client") {
            return $clientid;
        } else {
            //illegal user
            return 0;
        }        
    }    
    
    public function getDocumentsfromProject($project) {
        global $Webaddr;
        
        //search document
    	$documents = new WebDocument();
    	$documentlist = $documents->findDocumentsbyproject($project);
    	
    	$documentstructure = array();
    	
    	foreach ($documentlist as $document) {
    	    $documentnumber =  $document->get('documentnumber');
    		$documentname =  $document->get('documentname');
    		$currentmilestone = $document->get('milestone')-1;
    		$documentaddress = $Webaddr."/document/".$documentnumber;
    		
    		//define array if doesnt exist
    		if (!isset($documentstructure[$currentmilestone])) {
    			$documentstructure[$currentmilestone] = "";
    		}
    		//concatenate result
    		$documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. '<a href="'.$documentaddress.'"><h4>'.$documentname.'</h4></a>';
    	}
    	
    	return $documentstructure;
    }
    
    public function nextMilestone($projectnumber) {
        $project = $this->unparsedProject($projectnumber);
    	$milestone = $project->get("currentmilestone");
    	$project->set("currentmilestone", strval($milestone+1));
    	$project->save();
    }
    
    public function verifySignature($request) {
    	global $CAdocumentverify;
    	
    	$idnumber = $request["signerid"];
    	$signedhash = $request["signedhash"];
    	$signedtime = $request["signedtime"];
    	$signature = $request["signature"];
    	
    	if ($idnumber == "") {
    		echo "Failed. Reason : You must provide ID Number";
    		die();
    	}
    	
    	$form = array ("idnumber" => $idnumber, "signedhash" => $signedhash, "signedtime" => $signedtime, "signature" => $signature);
    	$form = json_encode($form);
    	
    	$result = sendjson($form,$CAdocumentverify);
    	
    	$result = json_decode($result, true);
    	if ($result) {
    		if ($result["success"]) {
    			echo $result["result"];	
    		} else {
    			echo $result["reason"];	
    		}
    	} else 
    		echo "Failed. Reason : Cannot connect to CA";
    }
}