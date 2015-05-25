<?php

require 'lib/PHPExcel/PHPExcel.php';  // Web Message Class

$app->get('/report/:projectnumber', function ($projectnumber) use ($app) {
    global $Webaddr;
    global $CAaddr;
    global $SIaddr;
    
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    $error = 0;
    
    $controller = new WebController($idnumber);
    $project = $controller->unparsedProject($projectnumber);
    if (!isset($project)) {
        //project is not exist
        $error = 1;
    }
    
    if ($error == 0) {
        //check user role
    	$result = $controller->parseProject($project);
    	$role = $controller->checkRole($result, $idnumber);    
        
    	$roletext ="";
    	switch ($role) {
    		case 1:
    			$roletext = "as Creator";
    			break;
    		case 2:
    			$roletext = "as Client";
    			break;
    		default:
    		    $error = 2;
    		    break;
    	}
    }
    
    if ($error == 0) {
        //check if project status as finished
        if (!$result["finishproject"]) {
            $error = 3;
        }
    }
    
    if ($error == 0) {
        //get all party identity
        $identity = "";
        
        $creatoridentity = json_decode($project->get("creatoridentity"), true);
        $clientidentity = json_decode($project->get("clientidentity"), true);
        
        //parse identity
        $creatoridentity = parseidentitytotext($creatoridentity);
        $clientidentity = parseidentitytotext($clientidentity);
    }
    
    if ($error == 0) {
        //search document
    	$documents = new WebDocument();
    	$documentlist = $documents->findDocumentsbyproject($project);
    	
    	$documentstructure = array();
    	foreach ($documentlist as $document) {
    	    $documentnumber =  $document->get('documentnumber');
    		$documentname =  $document->get('documentname');
    		$documentcreator =  $document->get('creator');
    		$documentsigner =  $document->get('signer');
    		$currentmilestone = $document->get('milestone')-1;
    		$originalfile =  $document->get('originalfile');
    		$signedfile =  $document->get('signedfile');
    		$originalhash =  $document->get('originalhash');
    		$signedhash =  $document->get('signedhash');
    		$createdtime =  $document->get('modified');
    		$signedtime =  $document->get('signedtime');
    		$signature =  $document->get('signature');
    		
    		//define array if doesnt exist
    		if (!isset($documentstructure[$currentmilestone])) {
    			$documentstructure[$currentmilestone] = "";
    		}
    		if (!is_array($documentstructure[$currentmilestone])) {
    			$documentstructure[$currentmilestone] = array();
    		}
    		
    		array_push($documentstructure[$currentmilestone], 
                                        "Document Name : ".$documentname,
                                        "Document Number : ".$documentnumber,
                                        "Creator : ".$documentcreator,
                                        "Signer : ".$documentsigner,
                                        "Original File URL : ".$originalfile->getURL(),
                                        "Original File Hash : ".$originalhash,
                                        "Created at : ".$createdtime." WIB");
                                        
            if ($signature) {
                array_push($documentstructure[$currentmilestone], 
                                        "Status : Signed",
                                        "Signed File URL : ".$signedfile->getURL(),
                                        "Signed File Hash : ".$signedhash,
                                        "Signature : ".$signature,
                                        "Signed at : ".$signedtime." WIB");
            } else {
                array_push($documentstructure[$currentmilestone], "Status : Not Signed");
            }
            
            //search comment
        	$comments = new WebComment();
        	$commentlist = $comments->findCommentsbydocument($documentnumber);
        	
            if ($commentlist != null) {
                array_push($documentstructure[$currentmilestone], 
                                        "Document ".$documentnumber." Comment : ");
                                        
                foreach ($commentlist as $comment) {
                    $poster = $comment->get('poster');
                    $modified = $comment->get('modified');
                    $commenttext = $comment->get('comment');
                    array_push($documentstructure[$currentmilestone], 
                                        "By ".$poster." : ".$commenttext." (at time ".$modified." WIB");
                    
                    $file = $comment->get('file');
                    if ($file != "") {
                        array_push($documentstructure[$currentmilestone], 
                                        "Comment file URL : ".$file->getURL());
                    }
                }
            }
            //create new line
            array_push($documentstructure[$currentmilestone], "");
    	}
    }
	switch ($error) {
	    case 0:
	        //create report
    	    $current_date = new DateTime("now");
        	$time = $current_date->format('Y-m-d H:i:s');
            
            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();
            
            // Set document properties
            $objPHPExcel->getProperties()->setCreator("Mobile ID Digital Signature")
            							 ->setLastModifiedBy("Mobile ID Digital Signature")
            							 ->setTitle($result["projectnumber"]." Log Report");
            
            // Add Project Information
            $disclaimer = array("Disclaimer",
                                "---------------------",
                                "This is a report of finished project.", 
                                "This document contain all information which can be used as a proof.",
                                "Please store this document in a safe place.",
                                "",
                                "Server Information",
                                "---------------------",
                                "Web Address : ".$Webaddr,
                                "CA Address : ".$CAaddr,
                                "SI Address : ".$SIaddr,
                                "",
                        );
            
            $userinformation = array(    "User Information",
                                            "---------------------",
                                            "Creator"
                                        );
            $userinformation = array_merge($userinformation, $creatoridentity);
            array_push($userinformation, "Client");
            $userinformation = array_merge($userinformation, $clientidentity);
            array_push($userinformation, "");         
            
            $projectinformation = array(    "Project Information",
                                            "---------------------",
                                            "Project Name : ".$result["projectname"],
                                            "Project Number : ".$result["projectnumber"],
                                            "Creator : ".$result["creator"],
                                            "Client : ".$result["client"],
                                            "Created at : ".$result["modified"],
                                            "Ended at : ".$result["ended"]." WIB",
                                            "",
                                        );
            
            $contents = array_merge($disclaimer, $userinformation);
            $contents = array_merge($contents, $projectinformation);
            
            $milestonelist = $result["milestone"];
            $milestonenumber = 0;
            $milestoneinformation = array();
            foreach($milestonelist as $milestone) {
                array_push($milestoneinformation, "Milestone Name : ".$milestone,"---------------------");
                if (is_array($documentstructure[$milestonenumber])) {
                    $milestoneinformation = array_merge($milestoneinformation, $documentstructure[$milestonenumber]);
                } else {
                    array_push($milestoneinformation, "Empty Milestone");
                }
                $milestonenumber++;
            }
            $contents = array_merge($contents, $milestoneinformation);

            array_push($contents, "This report is generated in ".$time." WIB as user ".$idnumber."." );                            

            $objPHPExcel->setActiveSheetIndex(0);
            $rowindex = 1;
            foreach ($contents as $content) {
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowindex, $content);
                $rowindex++;
            }
            $objPHPExcel->getActiveSheet()->setTitle('Project Information');
            
            // Add Log
            $recordcontroller = new WebRecord();

            //Begin Creator Log
            $contents = $recordcontroller->getUserRecord($result["creator"], $result["projectnumber"]);
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex(1);
            
            $objPHPExcel->getActiveSheet()->setCellValue('A1', "ID Number")
                                                ->setCellValue('B1', "Category")
                                                ->setCellValue('C1', "Action")
                                                ->setCellValue('D1', "Message")
                                                ->setCellValue('E1', "Time (WIB)");
            
            $rowindex = 2;
            foreach ($contents as $content) {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowindex, $content["idnumber"], PHPExcel_Cell_DataType::TYPE_STRING)
                                                    ->setCellValue('B'.$rowindex, $content["category"])
                                                    ->setCellValue('C'.$rowindex, $content["action"])
                                                    ->setCellValue('D'.$rowindex, $content["message"])
                                                    ->setCellValue('E'.$rowindex, $content["time"]);
                
                $rowindex++;
            }
            $objPHPExcel->getActiveSheet()->setTitle('Creator Log');

            //Begin Client Log
            $contents = $recordcontroller->getUserRecord($result["client"], $result["projectnumber"]);
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex(2);
            
            $objPHPExcel->getActiveSheet()->setCellValue('A1', "ID Number")
                                                ->setCellValue('B1', "Category")
                                                ->setCellValue('C1', "Action")
                                                ->setCellValue('D1', "Message")
                                                ->setCellValue('E1', "Time (WIB)");
            
            $rowindex = 2;
            foreach ($contents as $content) {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowindex, $content["idnumber"], PHPExcel_Cell_DataType::TYPE_STRING)
                                                    ->setCellValue('B'.$rowindex, $content["category"])
                                                    ->setCellValue('C'.$rowindex, $content["action"])
                                                    ->setCellValue('D'.$rowindex, $content["message"])
                                                    ->setCellValue('E'.$rowindex, $content["time"]);
                
                $rowindex++;
            }
            $objPHPExcel->getActiveSheet()->setTitle('Client Log');
            
            //Begin Creator Login Log
            $contents = $recordcontroller->getUserLoginRecord($result["creator"]);
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex(3);
            
            $objPHPExcel->getActiveSheet()->setCellValue('A1', "ID Number")
                                                ->setCellValue('B1', "Category")
                                                ->setCellValue('C1', "Action")
                                                ->setCellValue('D1', "Message")
                                                ->setCellValue('E1', "Time (WIB)");
            
            $rowindex = 2;
            foreach ($contents as $content) {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowindex, $content["idnumber"], PHPExcel_Cell_DataType::TYPE_STRING)
                                                    ->setCellValue('B'.$rowindex, $content["category"])
                                                    ->setCellValue('C'.$rowindex, $content["action"])
                                                    ->setCellValue('D'.$rowindex, $content["message"])
                                                    ->setCellValue('E'.$rowindex, $content["time"]);
                
                $rowindex++;
            }
            $objPHPExcel->getActiveSheet()->setTitle('Creator Login Log');
            
            //Begin Client Login Log
            $contents = $recordcontroller->getUserLoginRecord($result["client"]);
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex(4);
            
            $objPHPExcel->getActiveSheet()->setCellValue('A1', "ID Number")
                                                ->setCellValue('B1', "Category")
                                                ->setCellValue('C1', "Action")
                                                ->setCellValue('D1', "Message")
                                                ->setCellValue('E1', "Time (WIB)");
            
            $rowindex = 2;
            foreach ($contents as $content) {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowindex, $content["idnumber"], PHPExcel_Cell_DataType::TYPE_STRING)
                                                    ->setCellValue('B'.$rowindex, $content["category"])
                                                    ->setCellValue('C'.$rowindex, $content["action"])
                                                    ->setCellValue('D'.$rowindex, $content["message"])
                                                    ->setCellValue('E'.$rowindex, $content["time"]);
                
                $rowindex++;
            }
            $objPHPExcel->getActiveSheet()->setTitle('Client Login Log');
            
            $objPHPExcel->setActiveSheetIndex(0);
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="project log "'.$time.'".xlsx"');
            
	        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            $objWriter->save('php://output');
	        break;
	    case 1:
	        echo "Error : Project is not exist!";
	        die();
	        break;
	    case 2:
	        echo "Error : You are not involved in this project";
	        die();
	        break;
	    case 3:
	        echo "Error : Project is not finished";
	        die();
	        break;
	}

});


function parseidentitytotext($identity) {
    $identity =  array( "NIK : ". $identity["nik"],
                        "Nama : ". $identity["nama"],
                        "Tempat/Tgl Lahir : ". $identity["ttl"],
                        "Jenis Kelamin : ". $identity["jeniskelamin"],
                        "Gol Darah : ". $identity["goldarah"],
                        "Alamat : ". $identity["alamat"],
                        "RT/RW : ". $identity["rtrw"],
                        "Kel/Desa : ". $identity["keldesa"],
                        "Kecamatan : ". $identity["kecamatan"],
                        "Agama : ". $identity["agama"],
                        "Status Perkawinan : ". $identity["statperkawinan"],
                        "Pekerjaan : ". $identity["pekerjaan"],
                        "Kewarganegaraan : ". $identity["kewarganegaraan"],
                        "Berlaku Hingga : ". $identity["berlaku"]
                        );
    return $identity;
}

function parserecord($action) {
    $content = "";
    $categorys = array("login", "project", "milestone", "verify", "document", "signing");
    foreach ($categorys as $category) {
        if ($action[$category] != "") {
            switch ($category) {
                case "login":
                    $content = $content. "\r\nLogin Record\r\n";
                    break;
                case "project":
                    $content = $content. "\r\nProject Record\r\n";
                    break;
                case "milestone":
                    $content = $content. "\r\nMilestone Record\r\n";
                    break;
                case "verify":
                    $content = $content. "\r\nVerify Record\r\n";
                    break;
                case "document":
                    $content = $content. "\r\nDocument Record\r\n";
                    break;
                case "signing":
                    $content = $content. "\r\nSigning Record\r\n";
                    break;
                }
            $content = $content. "----------------\r\n";
            $content = $content. $action[$category];
        }
    }
    
    return $content;
}