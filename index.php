<?php
//Aplikasi Mobile ID - Web untuk kolaborasi internet.

require 'vendor/autoload.php';
date_default_timezone_set("Asia/Jakarta");

//twig init
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);

//slim init
$app = new \Slim\Slim(array(
    'debug' => true
));

$app = new \Slim\Slim(array(
    'cookies.encrypt' => true,
    'cookies.secret_key' => 'mobileid-web',
    'cookies.cipher' => MCRYPT_RIJNDAEL_256,
    'cookies.cipher_mode' => MCRYPT_MODE_CBC
));

$app->add(new \Slim\Middleware\SessionCookie(array(
    'expires' => '20 minutes'
)));

$app->get('/', function () use ($app) {
	//TODO: jika sudah login, redirect ke home
    $app->redirect('/login');
});

//Config
$configfile = 'config.json';
$addressfile = 'config/address.json';
require 'config/parse.php';  // Initialize parse database

//Lib
require 'lib/addstruct.php';  // Construct client address
require 'lib/sending.php';  // Handling sending http request function
require 'lib/crypt.php';  // Handling cryptographic function
require 'lib/WebController.class.php';  // Web Controller Class
require 'lib/WebDocument.class.php';  // Web Document Class
require 'lib/WebMessage.class.php';  // Web Message Class
require 'lib/WebRecord.class.php';  // Web Message Class

//Routes
require 'routes/login.php';  // Handling login function
require 'routes/session.php';  // Handling session function
require 'routes/home.php';  // Handling main menu
require 'routes/guide.php';  // Handling guide list
require 'routes/verify.php';  // Handling verify function
require 'routes/signing.php';  // Handling document signing list
require 'routes/project.php';  // Handling project list
require 'routes/document.php';  // Handling project list
require 'routes/report.php';  // Handling project list

//Config
$addressfile = 'config/address.json';
global $Webaddr;

//set static address
$twig->addGlobal('WebAddr', $Webaddr);

$app->run();