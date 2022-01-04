<?php

include('headers.php');
include('../db/airports-class.php');

$db = new SQLite3('../db/store.db');
$airports = new Airports($db);

// TODO Check 

switch ($_SERVER['REQUEST_METHOD']) {
    /**
     * Get all airports and write result in JSON format
     */
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
        /**
         * If all properties needed are present, call the API to add an aiport.
         * Else write an error
         */
    case "POST":
        $data = json_decode(file_get_contents("php://input"));
        if( isset($data->name, $data->latitude, $data->longitude)) {
            $airports->name = $data->name;
            $airports->latitude = $data->latitude;
            $airports->longitude = $data->longitude;
            $airpts = $airports->create();
            // echo(json_encode(array(
            //     "code" => 200,
            //     "message" => "Airport added"
            // )));
            echo(json_encode(
                [
                    'code' => 200,
                    'resultLength' => sizeof($airpts),
                    'data' => $airpts
                ]
            ));
        } else {
            http_response_code(400);
            echo(json_encode(array(
                "code" => 400,
                "message" => "Missing parameter (name, latitude, longitude are needed)"
            )));
        }
        break;
        /**
         * If all properties needed are present, call the API to update an aiport.
         * Else write an error
         */
    case "PUT":
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($_GET['id']) || !isset($data->name, $data->latitude, $data->longitude)){
            http_response_code(400);
            echo(json_encode(array(
                "code" => 400,
                "message" => "Missing parameter (id, name, latitude, longitude needed)"
            )));
        } else {
            $airports->id = $_GET['id'];
            $airports->name = $data->name;
            $airports->latitude = $data->latitude;
            $airports->longitude = $data->longitude;
            $airpts = $airports->update();
            // echo(json_encode(array(
            //     "code" => 200,
            //     "message" => "Airport updated"
            // )));
            echo(json_encode(
                [
                    'code' => 200,
                    'resultLength' => sizeof($airpts),
                    'data' => $airpts
                ]
            ));
        }
        break;
        /**
         * If id property needed is present, call the API to add an aiport.
         * Else write an error
         */
    case "DELETE":
        if (!isset($_GET['id'])){
            http_response_code(400);
            echo(json_encode(array(
                "code" => 400,
                "message" => "Missing parameter (id (int) needed)"
            )));
        } else {
            $airports->id = $_GET['id'];
            $airpts = $airports->delete();
            // echo(json_encode(array(
            //     "code" => 200,
            //     "message" => "Airport suppressed"
            // )));
            echo(json_encode(
                [
                    'code' => 200,
                    'resultLength' => sizeof($airpts),
                    'data' => $airpts
                ]
            ));
        }
        break;
    default:
        http_response_code(403);
        echo(json_encode(['message' => 'Method Not Allowed']));
        break;
}