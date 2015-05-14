<?php

$app->post('/verify/request', function () use($app) {
    global $Webaddr;
    if(isset($_SESSION["idnumber"])){
        $idnumber = $_SESSION["idnumber"];
        $username = $_SESSION["name"];
    }
    else{
        header("Location: $Webaddr");
        die();
    }
    
    global $CAverify;
    global $Webverifyconfirm;
    
    $idnumber = $app->request()->post("idnumber");
    $projectid = $app->request()->post("projectid");
    
    $req = (object) array("userinfo" => (object) array("nik" => $idnumber), "callback" => $Webverifyconfirm, "projectid" => $projectid);
    $req = json_encode($req);
    $result =sendjson($req,$CAverify);
    $result = json_decode($result, true);
    if ($result["success"]) {
        $app->flash('info', 'Request sent. Check your device to confirm identity request.');
    } else {
        $app->flash('error', $result["reason"]);
    }
    
    //save to record
	$record = new WebRecord();
	$record->recordverify($idnumber, "request");
});

$app->post('/verify/confirm', function () use($app) {
    //example query : {"userinfo":{"berlaku":"23-05-2200","kewarganegaraan":"Indonesia","pekerjaan":"Mahasiswa","statperkawinan":"Belum Kawin","agama":"Islam","kecamatan":"Cibeunying Kaler","keldesa":"Cigadung","rtrw":"001/001","alamat":"Jalan Ligar Sejoli no 5","goldarah":"A","jeniskelamin":"Laki-laki","ttl":"Bandung/23 Mei 1989","nama":"Bramanto Leksono","nik":"1231230509890001"},"projectid":"6d0bacf1d26dd6fbc277cb24b3004973","PID":"58673dcfcd36ab5690d1e00fc0f54463f390fb11","success":true}
    
    $body = json_decode($app->request()->getBody(), true);
    $userinfo = json_encode($body["userinfo"]);
    $idnumber = $body["userinfo"]["nik"];
    $projectnumber = $body["projectid"];
    
    //save to record
	$record = new WebRecord();
	$record->recordverify($idnumber, "success");
    
    $controller = new WebController($idnumber);
    $project = $controller->unparsedProject($projectnumber);
    
    if ($idnumber == $project->get('creator')) {
        $project->set("creatoridentity", $userinfo);
    }
    if ($idnumber == $project->get('client')) {
        $project->set("clientidentity", $userinfo);
    }
    $project->save();
});

$app->post('/verify/view', function () use($app) {
    
    $idnumber = $app->request()->post("idnumber");
    $projectnumber = $app->request()->post("projectid");
    
    $controller = new WebController($idnumber);
    $project = $controller->unparsedProject($projectnumber);
    
    if ($idnumber == $project->get('creator')) {
        $identity = $project->get("creatoridentity");
    }
    else if ($idnumber == $project->get('client')) {
        $identity = $project->get("clientidentity");
    }
    
    $identity = json_decode($identity, true);
    
    echo "NIK : ". $identity["nik"] . "\n".
         "Nama : ". $identity["nama"] . "\n".
         "Tempat/Tgl Lahir : ". $identity["ttl"] . "\n".
         "Jenis Kelamin : ". $identity["jeniskelamin"] . "\n".
         "Gol Darah : ". $identity["goldarah"] . "\n".
         "Alamat : ". $identity["alamat"] . "\n".
         "RT/RW : ". $identity["rtrw"] . "\n".
         "Kel/Desa : ". $identity["keldesa"] . "\n".
         "Kecamatan : ". $identity["kecamatan"] . "\n".
         "Agama : ". $identity["agama"] . "\n".
         "Status Perkawinan : ". $identity["statperkawinan"] . "\n".
         "Pekerjaan : ". $identity["pekerjaan"] . "\n".
         "Kewarganegaraan : ". $identity["kewarganegaraan"] . "\n".
         "Berlaku Hingga : ". $identity["berlaku"] . "\n";
});