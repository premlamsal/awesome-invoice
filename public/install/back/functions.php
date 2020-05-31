<?php
// Functions to filter user inputs
function filterName($field)
{
    // Sanitize user name
    $field = filter_var(trim($field), FILTER_SANITIZE_STRING);

    // Validate user name
    if (filter_var($field, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z\s]+$/")))) {
        return $field;
    } else {
        return false;
    }
}

function filterEmail($field)
{
    // Sanitize e-mail address
    $field = filter_var(trim($field), FILTER_SANITIZE_EMAIL);

    // Validate e-mail address
    if (filter_var($field, FILTER_VALIDATE_EMAIL)) {
        return $field;
    } else {
        return false;
    }
}
function filterString($field)
{
    // Sanitize string
    $field = filter_var(trim($field), FILTER_SANITIZE_STRING);
    if (!empty($field)) {
        return $field;
    } else {
        return false;
    }
}
function hasSpace($field)
{
    if (preg_match('/\s/',$field)) {
        return true;
    } else {
        return false;
    }
}

//code by prem
function connectDB($servername, $port, $dbname, $username, $password)
{

    try {
        $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return true;
    } catch (PDOException $e) {
        // return "Connection failed: " . $e->getMessage();
        return false;
    }

}

function json_response($data = null, $httpStatus = 200)
{
    header_remove();

    header("Content-Type: application/json");

    http_response_code($httpStatus);

    echo json_encode($data);

    exit();
}
