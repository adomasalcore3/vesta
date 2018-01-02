<?php
// Init
error_reporting(NULL);
ob_start();
$TAB = 'BACKUP';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}

// List backup exclustions
exec (VESTA_CMD."v-list-user-backup-exclusions ".$user." 'json'", $output, $return_var);
check_return_code($return_var,$output);
$data = json_decode(implode('', $output), true);
unset($output);

// Parse web
$v_username = $user;
foreach ($data['WEB'] as $key => $value) {
    if (!empty($value)){
        $v_web .= $key . ":" . $value. "\n";
    } else {
        $v_web .= $key . "\n";
    }
}

// Parse dns
foreach ($data['DNS'] as $key => $value) {
    if (!empty($value)){
        $v_dns .= $key . ":" . $value. "\n";
    } else {
        $v_dns .= $key . "\n";
    }
}

// Parse mail
foreach ($data['MAIL'] as $key => $value) {
    if (!empty($value)){
        $v_mail .= $key . ":" . $value. "\n";
    } else {
        $v_mail .= $key . "\n";
    }
}

// Parse databases
foreach ($data['DB'] as $key => $value) {
    if (!empty($value)){
        $v_db .= $key . ":" . $value. "\n";
    } else {
        $v_db .= $key . "\n";
    }
}

// Parse user directories
foreach ($data['USER'] as $key => $value) {
    if (!empty($value)){
        $v_userdir .= $key . ":" . $value. "\n";
    } else {
        $v_userdir .= $key . "\n";
    }
}

// Check POST request
if (!empty($_POST['save'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    $v_web = $_POST['v_web'];
    $v_web_tmp = str_replace("\r\n", ",", $_POST['v_web']);
    $v_web_tmp = rtrim($v_web_tmp, ",");
    $v_web_tmp = "WEB=" . escapeshellarg($v_web_tmp);

    $v_dns = $_POST['v_dns'];
    $v_dns_tmp = str_replace("\r\n", ",", $_POST['v_dns']);
    $v_dns_tmp = rtrim($v_dns_tmp, ",");
    $v_dns_tmp = "DNS=" . escapeshellarg($v_dns_tmp);

    $v_mail = $_POST['v_mail'];
    $v_mail_tmp = str_replace("\r\n", ",", $_POST['v_mail']);
    $v_mail_tmp = rtrim($v_mail_tmp, ",");
    $v_mail_tmp = "MAIL=" . escapeshellarg($v_mail_tmp);

    $v_db = $_POST['v_db'];
    $v_db_tmp = str_replace("\r\n", ",", $_POST['v_db']);
    $v_db_tmp = rtrim($v_db_tmp, ",");
    $v_db_tmp = "DB=" . escapeshellarg($v_db_tmp);

    $v_cron = $_POST['v_cron'];
    $v_cron_tmp = str_replace("\r\n", ",", $_POST['v_cron']);
    $v_cron_tmp = rtrim($v_cron_tmp, ",");
    $v_cron_tmp = "CRON=" . escapeshellarg($v_cron_tmp);

    $v_userdir = $_POST['v_userdir'];
    $v_userdir_tmp = str_replace("\r\n", ",", $_POST['v_userdir']);
    $v_userdir_tmp = rtrim($v_userdir_tmp, ",");
    $v_userdir_tmp = "USER=" . escapeshellarg($v_userdir_tmp);

    // Create temporary exeption list on a filesystem
    exec ('mktemp', $mktemp_output, $return_var);
    $tmp = $mktemp_output[0];
    $fp = fopen($tmp, 'w');
    fwrite($fp, $v_web_tmp . "\n" . $v_dns_tmp . "\n" . $v_mail_tmp . "\n" .  $v_db_tmp . "\n" . $v_userdir_tmp . "\n");
    fclose($fp);
    unset($mktemp_output);

    // Save changes
    exec (VESTA_CMD."v-update-user-backup-exclusions ".$user." ".$tmp, $output, $return_var);
    check_return_code($return_var,$output);
    unset($output);

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __("Changes has been saved.");
    }
}


// Render page
render_page($user, $TAB, 'edit_backup_exclusions');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
