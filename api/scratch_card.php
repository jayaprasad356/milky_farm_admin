<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kolkata');

include_once('../includes/crud.php');

$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');
include_once('../includes/functions.php');
$fn = new functions;


$date = date('Y-m-d');
if (empty($_POST['user_id'])) {
    $response['success'] = false;
    $response['message'] = "User Id is Empty";
    print_r(json_encode($response));
    return false;
}


$user_id = $db->escapeString($_POST['user_id']);

if (empty($_POST['scratch_id'])) {

    $sql = "SELECT * FROM scratch_cards WHERE user_id = '$user_id'";
    $db->sql($sql);
    $res= $db->getResult();
    $id = $res[0]['id'];

    $response['success'] = true;
    $response['amount'] = rand(20, 50);
    $response['scratch_id'] = 123;
    $response['message'] = "Scratch Card Available";
    print_r(json_encode($response));
}

else{
    $response['success'] = true;
    $response['message'] = "Scratch Card Claimed Successfully";
    print_r(json_encode($response));

}

?>