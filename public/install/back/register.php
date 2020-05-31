<?php

include 'functions.php';

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $USER_NAME = $USER_PASSWORD = $USER_EMAIL = '';

    $error = array();

    if (empty($_POST["USER_NAME"])) {

        $error['USER_NAME'] = "User name can't be blank";
    } else {
        $USER_NAME = filterString($_POST["USER_NAME"]);
        if ($USER_NAME == false) {
            $error['USER_NAME'] = "Please enter valid USER_NAME";
        }
    }

    if (empty($_POST["USER_EMAIL"])) {

        $error['USER_EMAIL'] = "User email can't be blank";
    } else {
        $USER_EMAIL = filterEmail($_POST["USER_EMAIL"]);
        if ($USER_EMAIL == false) {
            $error['USER_EMAIL'] = "Please enter valid USER_EMAIL";
        }
    }
    if (empty($_POST["USER_PASSWORD"])) {

        $error['USER_PASSWORD'] = "User password can't be blank";
    } else {
        $USER_PASSWORD = filterString($_POST["USER_PASSWORD"]);
        if ($USER_PASSWORD == false) {
            $error['USER_PASSWORD'] = "Please enter valid USER_PASSWORD";
        }
    }

    if ($error == null) {
        //error is null

// getting env file location
        $location        = str_replace('\\', '/', getcwd());
        $currentLocation = explode("/", $location);
        array_pop($currentLocation);
        array_pop($currentLocation);
        array_pop($currentLocation);
        $desiredLocation = implode("/", $currentLocation);
        $envFile         = $desiredLocation . '/' . '.env';

        if (!file_exists($envFile)) {
            //error found from the validation
            $data = ['status' => 'error', 'msg' => 'env file not found'];

            json_response($data, 500);
        }

// reading env content
        $data         = file($envFile);
        $keyValueData = [];

        if ($data) {
            foreach ($data as $line) {
                $line      = preg_replace('/\s+/', '', $line);
                $rowValues = explode('=', $line);

                if (strlen($line) !== 0) {
                    $keyValueData[$rowValues[0]] = $rowValues[1];
                }
            }
        }

        $DB_HOST     = $keyValueData['DB_HOST'];
        $DB_DATABASE = $keyValueData['DB_DATABASE'];
        $DB_USERNAME = $keyValueData['DB_USERNAME'];
        $DB_PASSWORD = $keyValueData['DB_PASSWORD'];
// $APP_NAME      = $keyValueData['APP_NAME'];
        $APP_URL       = $keyValueData['APP_URL'];
        // $DB_CONNECTION = $keyValueData['DB_CONNECTION'];
        $DB_PORT = $keyValueData['DB_PORT'];

        $user_name     = $USER_NAME;
        $user_email    = $USER_EMAIL;

        try {
             $user_password = password_hash($USER_PASSWORD, PASSWORD_DEFAULT);

            $conn = new PDO("mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE", $DB_USERNAME, $DB_PASSWORD);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO users (name, email, email_verified_at, password, created_at, updated_at) VALUES ('$user_name', '$user_email', now(), '$user_password',now(), now())";
            // use exec() because no results are returned
            $conn->exec($sql);

            $data = ['status' => 'success','url'=>$APP_URL];
            json_response($data);

        } catch (PDOException $e) {
            // echo $sql . "<br>" . $e->getMessage();
            $data = ['status' => 'failed', 'msg' => $e->getMessage()];
            json_response($data, 500);
        }
        $conn = null;

        //error is  null

    } else {
        //error found from the validation
        $data = ['status' => 'error', 'error' => $error];

        json_response($data, 500);
    }

} else {
    json_response("Request method not supported", 400);
}
