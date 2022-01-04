<?php

include('headers.php');
include('../db/user-class.php');

$db = new SQLite3('../db/store.db');
$user = new User($db);

switch ( $_SERVER['REQUEST_METHOD'] ) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if( isset($data["login"], $data["password"]) && !empty($data["login"]) && !empty($data["password"]) ){

            $user->login = $data["login"];
            $user->password = $data["password"];
            $connected = $user->login();
            $message = "Login or password incorrect";
            $token = '';
            $status = '';
            if($connected){
                $message = "User connected";
                $token = $user->token;
                $status = $user->status;
            }
            

            http_response_code(200);
            echo json_encode( array(
                "code" => 200,
                "success" => $connected,
                "token" =>  $token,
                "status" => $status,
                "message" => $message
            ));
    
            return;
        }
        http_response_code(400);
        echo json_encode( array(
            "code" => 400,
            "message" => "Bad request, login and password needed"
        ));
        break;
    default:
        http_response_code(405);
        echo json_encode( array(
            "code" => 405,
            "message" => "method not allowed (".$_SERVER['REQUEST_METHOD'].")"
        ));
        break;
}