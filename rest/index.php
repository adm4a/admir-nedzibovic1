<?php

require "../vendor/autoload.php";
require "./services/MidtermService.php";
require "./services/FinalService.php";
use Firebase\JWT\JWT;

Flight::register('midtermService', 'MidtermService');
Flight::register('finalService', 'FinalService');

//require './rest/routes/MidtermRoutes.php';
require './routes/FinalRoutes.php';
/** TODO
* Add middleware to protect routes rest/final/share_classes AND rest/final/share_class_categories
*/
Flight::route('*', function () {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $jwt = str_replace('Bearer ', '', $headers['Authorization']);
        try {
            $key = 'superSecretKeyAdmir'; 
            $decoded = JWT::decode($jwt, $key, array('HS256'));
            return true;
        } catch (\Exception $e) {
            Flight::json(["message" => "Unauthorized"], 401);
            Flight::stop();
        }
    } else {
        Flight::json(["message" => "Unauthorized"], 401);
        Flight::stop();
    }
});

Flight::start();
?>
