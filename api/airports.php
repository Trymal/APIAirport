<?php

include('headers.php');
include('../db/airports-class.php');

$db = new SQLite3('../db/store.db');
$airports = new Airports($db);

// TODO Check 

switch ($_SERVER['REQUEST_METHOD']) {
    case "GET":
        $airpts = $airports->read();
        echo(json_encode(
            [
                'code' => 200,
                'resultLength' => sizeof($airpts),
                'data' => $airpts
            ]
        ));
        break;
    case "POST":
        $data = json_decode(file_get_contents("php://input"));
        if( isset($data->name, $data->latitude, $data->longitude)) {
            $airports->create($data);
            echo(json_encode(array(
                "code" => 200,
                "message" => "Airport added"
            )));
        } else {
            http_response_code(400);
            echo(json_encode(array(
                "code" => 400,
                "message" => "Missing parameter (name, latitude, longitude are needed)"
            )));
        }
        break;
    case "PUT":
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($_GET['id']) || !isset($data->name, $data->latitude, $data->longitude)){
            http_response_code(400);
            echo(json_encode(array(
                "code" => 400,
                "message" => "Missing parameter (id, name, latitude, longitude needed)"
            )));
        } else {
            $airports->update($_GET['id'], $data);
            echo(json_encode(array(
                "code" => 200,
                "message" => "Airport updated"
            )));
        }
        break;
    case "DELETE":
        var_dump(gettype($_GET['id']));
        if (!isset($_GET['id'])){
            http_response_code(400);
            echo(json_encode(array(
                "code" => 400,
                "message" => "Missing parameter (id (int) needed)"
            )));
        } else {
            $airports->delete($_GET['id']);
            echo(json_encode(array(
                "code" => 200,
                "message" => "Airport suppressed"
            )));
        }
        break;
    default:
        http_response_code(403);
        echo(json_encode(['message' => 'Method Not Allowed']));
        break;
}

// echo "GET Airports";