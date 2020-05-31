<?php
include 'functions.php';

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $APP_NAME = $APP_ENV = $APP_URL = $DB_CONNECTION = $DB_HOST = $DB_PORT = $DB_DATABASE = $DB_USERNAME = $DB_PASSWORD = "";

    $error = array();

    if (empty($_POST["APP_NAME"])) {

        $error['APP_NAME'] = "APP_NAME can't be blank";
    } else {
        $APP_NAME = filterString($_POST["APP_NAME"]);
        if ($APP_NAME == false) {
            $error['APP_NAME'] = "Please enter valid APP_NAME";
        }
        if (hasSpace($APP_NAME)) {
            $error['APP_NAME'] = "There should be no space in App name";
        }

    }

    if (empty($_POST["APP_ENV"])) {

        $error['APP_ENV'] = "APP_ENV can't be blank";
    } else {
        $APP_ENV = filterString($_POST["APP_ENV"]);
        if ($APP_ENV == false) {
            $error['APP_ENV'] = "Please enter valid APP_ENV";
        }if (hasSpace($APP_NAME)) {
            $error['APP_ENV'] = "There should be no space in App env";
        }
    }

    if (empty($_POST["APP_URL"])) {

        $error['APP_URL'] = "APP_URL can't be blank";
    } else {
        $APP_URL = filterString($_POST["APP_URL"]);
        if ($APP_URL == false) {
            $error['APP_URL'] = "Please enter valid application APP_URL";
        }
        if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $APP_URL)) {
            $error['APP_URL'] = "Invalid URL";
        }
    }

    // if (empty($_POST["DB_CONNECTION"])) {

    //     $error['DB_CONNECTION'] = "DB_CONNECTION can't be blank";
    // } else {
    //     $DB_CONNECTION = filterString($_POST["DB_CONNECTION"]);
    //     if ($DB_CONNECTION == false) {
    //         $error['DB_CONNECTION'] = "Please enter valid application DB_CONNECTION";
    //     }
    // }

    if (empty($_POST["DB_HOST"])) {

        $error['DB_HOST'] = "Database host can't be blank";
    } else {
        $DB_HOST = filterString($_POST["DB_HOST"]);
        if ($DB_HOST == false) {
            $error['DB_HOST'] = "Please enter valid DB_HOST";
        }if (hasSpace($DB_HOST)) {
            $error['DB_HOST'] = "There should be no space in Database host";
        }
    }

    if (empty($_POST["DB_PORT"])) {

        $error['DB_PORT'] = "Database port can't be blank";
    } else {
        $DB_PORT = filterString($_POST["DB_PORT"]);
        if ($DB_PORT == false) {
            $error['DB_PORT'] = "Please enter valid DB_PORT";
        }
        if (hasSpace($DB_PORT)) {
            $error['DB_PORT'] = "There should be no space in Database ";
        }
    }
    if (empty($_POST["DB_DATABASE"])) {

        $error['DB_DATABASE'] = "Database name can't be blank";
    } else {
        $DB_DATABASE = filterString($_POST["DB_DATABASE"]);
        if ($DB_DATABASE == false) {
            $error['DB_DATABASE'] = "Please enter valid DB_DATABASE";
        }
        if (hasSpace($DB_DATABASE)) {
            $error['DB_DATABASE'] = "There should be no space in Database name";
        }
    }
    if (empty($_POST["DB_USERNAME"])) {

        $error['DB_USERNAME'] = "Database username  can't be blank";
    } else {
        $DB_USERNAME = filterString($_POST["DB_USERNAME"]);
        if ($DB_USERNAME == false) {
            $error['DB_USERNAME'] = "Please enter valid DB_USERNAME";
        }
        if (hasSpace($DB_USERNAME)) {
            $error['DB_USERNAME'] = "There should be no space in Database username";
        }
    }
 
        $DB_PASSWORD = filterString($_POST["DB_PASSWORD"]);
        // if ($DB_PASSWORD == false) {
        //     $error['DB_PASSWORD'] = "Please enter valid DB_PASSWORD";
        // }
        // if (hasSpace($DB_PASSWORD)) {
        //     $error['DB_PASSWORD'] = "There should be no space in Database password";
        // }
    

    if ($error == null) {
        // echo "No errors found";

        $checkDB = connectDB($DB_HOST, $DB_PORT, $DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);
        if ($checkDB) {
            // echo "DB connected sucessfully";

            //writing env file

            // getting env file location
            $location        = str_replace('\\', '/', getcwd());
            $currentLocation = explode("/", $location);
            array_pop($currentLocation);
            array_pop($currentLocation);
            array_pop($currentLocation);//will go to home dir of application
            $desiredLocation = implode("/", $currentLocation);
            $envFile         = $desiredLocation . '/' . '.env';

            $envExampleFile = $desiredLocation . '/' . '.env.example';

            if (!file_exists($envFile)) {
                if (file_exists($envExampleFile)) {
                    copy($envExampleFile, $envFile);
                } else {
                    touch($envFile);
                }
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
            $DB_CONNECTION = 'mysql';
            // inserting form data to empty array
            $keyValueData['DB_HOST']       = $DB_HOST;
            $keyValueData['DB_DATABASE']   = $DB_DATABASE;
            $keyValueData['DB_USERNAME']   = $DB_USERNAME;
            $keyValueData['DB_PASSWORD']   = $DB_PASSWORD;
            $keyValueData['APP_NAME']      = $APP_NAME;
            $keyValueData['APP_URL']       = $APP_URL;
            $keyValueData['DB_CONNECTION'] = $DB_CONNECTION;
            $keyValueData['DB_PORT']       = $DB_PORT;

            // making key/value pair with form-data for env
            $changedData = [];
            foreach ($keyValueData as $key => $value) {
                $changedData[] = $key . '=' . $value;
            }

            // inserting new form-data to env
            $changedData = implode(PHP_EOL, $changedData);
            if (file_put_contents($envFile, $changedData)) {
                $data = ['status' => 'success'];
                json_response($data);
            } else {
                $data = ['status' => 'failed', 'msg' => 'Error creating .env file'];
                json_response($data, 500);
            }

            // show a message of success and provide a true success variable
        } else {
            $data = ['status' => 'failed', 'msg' => 'Failed to connect to Database. Check credentials'];
            json_response($data, 500);
        }

    } else {
        //error found from the validation
        $data = ['status' => 'error', 'error' => $error];

        json_response($data, 500);
    }
} else {
    json_response("Request method not supported", 400);
}
