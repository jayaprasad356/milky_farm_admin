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

$datetime = date('Y-m-d H:i:s');
$date = date('Y-m-d');

$sql = "SELECT user_id,recharge_amount FROM `recharge` WHERE DATE(datetime) >= '2024-03-30' AND status = 1 AND recharge_amount > 250 GROUP BY user_id ORDER BY `recharge`.`recharge_amount` ASC";
$db->sql($sql);
$res= $db->getResult();
$num = $db->numRows($res);

if ($num >= 1){
    foreach ($res as $row) {
        $user_id = $row['user_id'];
        $daily_income = 50;
        $sql = "UPDATE users SET balance = balance + $daily_income, today_income = today_income + $daily_income, total_income = total_income + $daily_income WHERE id = $user_id";
        $db->sql($sql);
        
        $sql_insert_transaction = "INSERT INTO transactions (`user_id`, `amount`, `datetime`, `type`) VALUES ('$user_id', '$daily_income', '$datetime', 'join_bonus')";
        $db->sql($sql_insert_transaction);
    }
    $response['success'] = true;
    $response['message'] = "Join Bonus Updated";
    $response['data'] = $rows;
    print_r(json_encode($response));
}
else{
    $response['success'] = false;
    $response['message'] = "user Not found";
    print_r(json_encode($response));

}


?>