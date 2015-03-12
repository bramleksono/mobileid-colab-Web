<?php
//Aplikasi Mobile ID - Web untuk kolaborasi internet.

require 'vendor/autoload.php';

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

//Lib
require 'lib/addstruct.php';  // Construct client address
require 'lib/sending.php';  // Handling sending http request function

//Routes
require 'routes/login.php';  // Handling login function
require 'routes/session.php';  // Handling login function
require 'routes/home.php';  // Handling login function

//Config
$addressfile = 'config/address.json';

$app->run();