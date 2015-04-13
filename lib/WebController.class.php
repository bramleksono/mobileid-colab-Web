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
        $creatorapproval = $project->get('creatorapproval');
        $creatoridentity = $project->get('creatoridentity');
        $client = $project->get('client');
        $clientapproval = $project->get('clientapproval');
        $clientidentity = $project->get('clientidentity');
        $modified = $project->get('modified');
    	$milestone = json_decode($project->get('milestone'), true);
    	$milestonenumber = $project->get('currentmilestone');
	    $currentmilestone = $milestone[$milestonenumber-1];
	    
        $data = array(  "projectname" => $projectname,
                        "projectnumber" =>$projectnumber,
                        "creator" =>$creator,
                        "creatorapproval" =>$creatorapproval,
                        "creatoridentity" =>$creatoridentity,
                        "client" =>$client,
                        "clientapproval" =>$clientapproval,
                        "clientidentity" =>$clientidentity,
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
    
    public function getApproval($project) {
        //check if project has creator and client approval
        $creatorapproval = $project["creatorapproval"];
        $clientapproval = $project["clientapproval"];
        if (($creatorapproval) && ($clientapproval)) {
            $approved = 1;
        } else {
            //illegal user
            $approved = 0;
        }
        
        $creatoridentity = $project["creatoridentity"];   
        $clientidentity = $project["clientidentity"]; 
        return array($approved, $creatorapproval, $clientapproval, $creatoridentity, $clientidentity);
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

    public function showDocument() {
        $idnumber = $this->idnumber;
        $documentcontroller = new WebDocument();
        
        //get list as creator
        $documents = $documentcontroller->findDocumentsbysigner($idnumber);
        
        $signercount = count($documents);
        
        $signertext = '';
        if ($signercount > 0) {            
            $i = 1;
            
            foreach ($documents as $list) {
                //to be showed: creator, document name, project name, latest modified
                $documentnumber = $list->get('documentnumber');
                $creator = $list->get('creator');
                $documentname = $list->get('documentname');
                
                $projectobj = $list->get('project');
                $projectdetail = $documentcontroller->getProject($projectobj);
                $projectname = $projectdetail->get('projectname');
                
                $modified = $list->get('modified');
                
                //to change row color
                $signature = $list->get('signature');
                if (!isset($signature)) {
                    $rowcolor = "primary";
                } else {
                    $rowcolor = "success";
                }
                
                $signertext = $signertext.'<tr class="'.$rowcolor.'"><td>'.$i.'</td><td>'.$creator.'</td><td><a href="document/'.$documentnumber.'">'.$documentname.'</td><td>'.$projectname.'</td><td>'.$modified.'</td><td>';
                $i++;
            }
        }
        
        $data = array(  "signerdocument" => $signertext);
        return $data;
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
    		//$documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. '<a href="'.$documentaddress.'"><h4>'.$documentname.'</h4></a>';
            $documentstructure[$currentmilestone] = '
                      <tr>
                        <td>1</td>
                        <td><a href="http://t3box-160161.apse1.nitrousbox.com:8000/document/1f7388c22e25a800bae591dd3380980a">Michael</a></a></td>
                        <td>Original Document</td>
                        <td>Sign</td>
                      </tr>
                      <tr>
                        <td>2</td>
                        <td><a href="http://t3box-160161.apse1.nitrousbox.com:8000/document/1f7388c22e25a800bae591dd3380980a">Jake</a></td>
                        <td>Signed Document</td>
                        <td>Sign</td>
                      </tr>';
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