<?php

$app->get('/login', function () use($app,$twig) {
	//Create login form
	$login=array(
	    'pagetitle' => 'Login - MobileID Web',
	    'heading' => 'MobileID Web Application',
		'subheading' => 'Enter ID Number Below',
		'license' => 'Mobile ID Web Application',
		'year' => '2015',
		'author' => 'Bramanto Leksono',
	);

	if (isset($_SESSION['slim.flash']['error'])) {
		$alert=array('alert' => $_SESSION['slim.flash']['error']);
		$login = array_merge($login, $alert);
	}
	
    echo $twig->render('login.html',$login);
});

$app->post('/process', function () use ($app, $twig) {
	//handling login registration to CA
    $idnumber = $app->request()->post("idnumber");
    if (!(strlen($idnumber) == 16)) {
		//invalid input
		$app->flash('error', 'Input not valid. Enter correct ID number.');
		$app->redirect('/login');
	} else {
		//process request
		global $CAlogin;
		global $Webloginconfirm;
		$req = (object) array("userinfo" => (object) array("nik" => $idnumber), "callback" => $Webloginconfirm);
		$req = json_encode($req);
        $result =sendjson($req,$CAlogin);
		if ($result) {
			$result = json_decode($result);
			$loginreq = $result->PID;
		}
		else {
			$app->flash('error', 'Cannot connect to CA');
			$app->redirect('/login');
		}
		
		$login=array(
			'pagetitle' => 'Login - MobileID Web',
			'heading' => 'Waiting CA response',
			'subheading' => 'Check your device to confirm login request (Periksa perangkat untuk mengkonfirmasi permintaan login)',
			'license' => 'Mobile ID Web Application',
			'year' => '2015',
			'author' => 'Bramanto Leksono',
			'loginsession' => $loginreq,
		);
		//create file to save login status
		$filepath = "./data/PID/".$loginreq;
		file_put_contents($filepath,"Waiting..");
		//save to record
		$record = new WebRecord();
		$record->recordlogin($idnumber, "request");
		
		echo $twig->render('wait.html',$login );
	}
});

$app->post('/process/check', function () use ($app) {
	//to poll user login status
	$loginsession = $app->request()->post("loginsession");
	$filepath = "./data/PID/".$loginsession;
	echo file_get_contents($filepath);
});

$app->post('/process/confirm', function () use ($app) {
	//receive login confirmation from CA and save result to file
	$body = $app->request()->getBody();
	$json = json_decode($body);
	$PID = $json->PID;
	
	$filepath = "./data/PID/".$PID;
	file_put_contents($filepath,$body);
});