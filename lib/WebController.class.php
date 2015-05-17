<?php

require 'WebProject.class.php';  // Handling User Project Database
require 'WebComment.class.php';  // Handling User Project Database

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
        $ended = $project->get('ended');
    	$milestone = json_decode($project->get('milestone'), true);
    	$milestonenumber = $project->get('currentmilestone');
        $finishproject = $project->get('finishproject');
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
                        "ended" =>$ended,
                        "currentmilestone" =>$currentmilestone,
                        "milestonenumber" => $milestonenumber,
                        "milestone" =>$milestone,
                        "finishproject" => $finishproject
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
        
        return array($project->storeProjectDB($form,$firstmilestone), $projectnumber);
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
    
    public function getDocumentsfromProject($project, $idnumber, $isfinished) {
        global $Webaddr;
        
        //search document
    	$documents = new WebDocument();
    	$documentlist = $documents->findDocumentsbyproject($project);
    	
    	$documentstructure = array();
    	
        $iteration=1;
    	foreach ($documentlist as $document) {
    	    $documentnumber =  $document->get('documentnumber');
    		$documentname =  $document->get('documentname');
    		$documentcreator =  $document->get('creator');
    		$currentmilestone = $document->get('milestone')-1;
    		$documentaddress = $Webaddr."/document/".$documentnumber;
    		$signature =  $document->get('signature');
            
    		//define array if doesnt exist
    		if (!isset($documentstructure[$currentmilestone])) {
    			$documentstructure[$currentmilestone] = "";
    		}
            
            $opendocument = "";
            $actionbutton = "";
            $signaturestatus = "";
            //generate document link
            if (!$signature) {
                $signaturestatus = "Not Signed";
                $doclink =  $document->get('originalfile');
                $url = $doclink->getURL();
                $opendocument = '<a class="btn btn-sm btn-default" target="_blank" href="'.$url.'">View</a>';                
            } else {
                $signaturestatus = "Signed";
                $doclink =  $document->get('signedfile');
                $url = $doclink->getURL();
                $opendocument = '<a class="btn btn-sm btn-default" target="_blank" href="'.$url.'">View</a>';
            }
            
            $action = "";
            //show remove button if user is document creator, project is not finished, and document is not signed
            if (($idnumber == $documentcreator) && (!$isfinished) && (!$signature)) {
                $action = '<a class="btn btn-sm btn-default" href="'.$Webaddr.'/document/remove/'.$documentnumber.'">Remove</a>';
            }
            
            $commentbutton = "";
            if (!$isfinished) {
                $commentbutton = '<a class="btn btn-sm btn-default" href="'.$Webaddr.'/document/comment/'.$documentnumber.'">Add Comment</a>';
            }
            
    		//concatenate result
    		//$documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. '<a href="'.$documentaddress.'"><h4>'.$documentname.'</h4></a>';
            $documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]. 
                     '<table class="table"><tbody><tr>
                        <td>'.$iteration.'</td>
                        <td><a href="'.$documentaddress.'">'.$documentname.'</a></a></td>
                        <td>'.$signaturestatus.'</td>
                        <td>'.$opendocument.$action.'</td>
                        <td>'.$commentbutton.'</td>
                      </tr></tbody></table>';
            //search comment
        	$comments = new WebComment();
        	$commentlist = $comments->findCommentsbydocument($documentnumber);
            if ($commentlist) {
                $documentstructure[$currentmilestone] = $documentstructure[$currentmilestone]."<h4>Comment:</h4>";
                foreach ($commentlist as $comment) {
                    $poster = $comment->get('poster');
                    $modified = $comment->get('modified');
                    $commenttext = $comment->get('comment');
                    $commenttext = nl2br($commenttext); //convert new line in text to br tag in html
                    $file = $comment->get('file');
                    $filebutton = "";
                    if ($file != "") {
                        $filebutton = '<a href="'.$file->getURL().'">(View Attachment)</a>';
                    }
                    
                    $documentstructure[$currentmilestone] = $documentstructure[$currentmilestone].'<p>'.$poster.' : '.$commenttext.' '.$filebutton.' (at time '.$modified.' WIB)</p>';
                }
            }
            
            $iteration++;
    	}
    	
    	return $documentstructure;
    }
    
    public function nextMilestone($projectnumber, $milestonename) {
        $project = $this->unparsedProject($projectnumber);
        //increment milestone
    	$milestone = $project->get("currentmilestone");
    	$project->set("currentmilestone", strval($milestone+1));
        //create milestone name
        $milestonelist = json_decode($project->get("milestone"));
    	array_push($milestonelist, $milestonename);
        $project->set("milestone", json_encode($milestonelist));
        //save
        $project->save();
    }

    public function deleteMilestone($projectnumber) {
        $project = $this->unparsedProject($projectnumber);
    	$currentmilestone = $project->get("currentmilestone");
        $deletedmilestone = "";
        
        if ($currentmilestone == 1) {
            //cannot remove 1st milestone
            $result = 2;
        } else {
            //search document
            $documents = new WebDocument();
            $documentlist = $documents->findDocumentsbyproject($project);

            $documentcount=0;
            foreach ($documentlist as $document) {
                if ($document->get("milestone") == $currentmilestone) {
                    $documentcount++;
                }
            }

            if ($documentcount == 0) {
    	        $milestonelist = json_decode($project->get("milestone"));
    	        $deletedmilestone = $milestonelist[$currentmilestone-1];
                unset($milestonelist[$currentmilestone-1]);
                $milestonelist = array_values($milestonelist);
                $project->set("milestone", json_encode($milestonelist));
                $project->set("currentmilestone", strval($currentmilestone-1));
                $project->save();
                $result = 1;
            } else {
                $result =  0;
            }
        }
        
        return array($result, $deletedmilestone);
    }
    
    public function createMilestone($projectnumber, $milestonename) {
        $project = $this->unparsedProject($projectnumber);
    	$currentmilestone = $project->get("currentmilestone");
        
        if ($currentmilestone == 1) {
            //cannot remove 1st milestone
            return 2;
        } else {
            //search document
            $documents = new WebDocument();
            $documentlist = $documents->findDocumentsbyproject($project);

            $documentcount=0;
            foreach ($documentlist as $document) {
                if ($document->get("milestone") == $currentmilestone) {
                    $documentcount++;
                }
            }

            if ($documentcount == 0) {
                $project->set("currentmilestone", strval($currentmilestone-1));
                $project->save();
                return 1;
            } else {
                return 0;
            }
        }
    }
    
    public function finishProject($projectnumber) {
        $project = $this->unparsedProject($projectnumber);
        //set project status
    	$project->set("finishproject", "1");
    	
        $projectcontroller = new WebProject();
        $time = $projectcontroller->getTime();
    	$project->set("ended", $time);
        
        //save
        $project->save();
    }
    
    
    public function deleteProject($projectnumber) {
        $project = $this->unparsedProject($projectnumber);
        
        //search document
        $documents = new WebDocument();
        $documentlist = $documents->findDocumentsbyproject($project);

        $documentcount = count($documentlist);

        if ($documentcount == 0) {
            $project->destroy();
            $result = 1;
        } else {
            $result =  0;
        }
        
        return $result;
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