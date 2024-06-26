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
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$db = new Database();
$db->connect();

if (empty($_POST['user_id'])) {
    $response['success'] = false;
    $response['message'] = "User ID is Empty";
    print_r(json_encode($response));
    return false;
}
if (empty($_POST['amount'])) {
    $response['success'] = false;
    $response['message'] = "Amount is Empty";
    print_r(json_encode($response));
    return false;
}
$date = date('Y-m-d');
function isBetween10AMand6PM() {
    $currentHour = date('H');
    $startTimestamp = strtotime('10:00:00');
    $endTimestamp = strtotime('18:00:00');
    return ($currentHour >= date('H', $startTimestamp)) && ($currentHour < date('H', $endTimestamp));
}

$user_id = $db->escapeString($_POST['user_id']);
$amount = $db->escapeString($_POST['amount']);
$datetime = date('Y-m-d H:i:s');
$dayOfWeek = date('w', strtotime($datetime));

$sql = "SELECT * FROM settings";
$db->sql($sql);
$settings = $db->getResult();


$sql = "SELECT * FROM settings WHERE id=1";
$db->sql($sql);
$result = $db->getResult();
$min_withdrawal = $result[0]['min_withdrawal'];
$withdrawal_status = $result[0]['withdrawal_status'];


if ($withdrawal_status == 0) {
    $response['success'] = false;
    $response['message'] = "Withdrawal Corrently Disabled";
    print_r(json_encode($response));
    return false;
}

$sql = "SELECT * FROM users WHERE id='$user_id'";
$db->sql($sql);
$res = $db->getResult();
$balance = $res[0]['balance'];
$account_num = $res[0]['account_num'];
$referred_by = $res[0]['referred_by'];
$refer_code = $res[0]['refer_code'];



if (!isBetween10AMand6PM()) {
    $response['success'] = false;
    $response['message'] = "Withdrawal time morning 10:00AM to 6PM";
    print_r(json_encode($response));
    return false;
}
$dayOfWeek = date('w');

if ($dayOfWeek == 0 || $dayOfWeek == 7) {
    $response['success'] = false;
    $response['message'] = "Withdrawal time Monday to Saturday";
    print_r(json_encode($response));
    return false;
} 

$sql = "SELECT id FROM user_plan WHERE user_id = '$user_id' AND plan_id = 7";
$db->sql($sql);
$res= $db->getResult();
$num = $db->numRows($res);
if ($num == 0) {
    $response['success'] = false;
    $response['message'] = "Start Rental Cow for withdrawal";
    echo json_encode($response);
    return;

}

$sql = "SELECT * FROM `users`u,user_plan up WHERE u.id = up.user_id AND u.referred_by = '$refer_code' AND up.plan_id = 7";
$db->sql($sql);
$res= $db->getResult();
$num = $db->numRows($res);
if ($num <  2) {
    $response['success'] = false;
    $response['message'] = "Invite 2 members in Rental Cow";
    echo json_encode($response);
    return;
}
if ($amount >= $min_withdrawal) {
    if ($amount <= $balance) {
        if ($account_num == '') {
            $response['success'] = false;
            $response['message'] = "Please Update Your Bank details";
            print_r(json_encode($response));
            return false;
        } else {

            $sql = "INSERT INTO withdrawals (`user_id`,`amount`,`balance`,`status`,`datetime`) VALUES ('$user_id','$amount',$balance,0,'$datetime')";
            $db->sql($sql);
            $sql = "UPDATE users SET balance = balance - '$amount',total_withdrawal = total_withdrawal + '$amount' WHERE id='$user_id'";
            $db->sql($sql);

            $sql = "SELECT * FROM withdrawals WHERE user_id = $user_id";
            $db->sql($sql);
            $withdrawals = $db->getResult();
    
            $sql = "SELECT * FROM users WHERE id = $user_id";
            $db->sql($sql);
            $userDetails = $db->getResult();
    
            $response['success'] = true;
            $response['message'] = "Withdrawal Requested Successfully.";
            $response['data'] = $userDetails;
            print_r(json_encode($response));
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Insufficient Balance";
        print_r(json_encode($response));
    }
} else {
    $response['success'] = false;
    $response['message'] = "Minimum Withdrawal Amount is $min_withdrawal";
    print_r(json_encode($response));
}
    ?>