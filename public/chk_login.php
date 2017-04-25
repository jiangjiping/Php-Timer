<?php
require dirname(__DIR__) . '/App/Config/config.php';
if (!isset($_SERVER['REMOTE_ADDR']) || !in_array($_SERVER['REMOTE_ADDR'], WEB_ALLOW_IP)) {
    exit('Access Deny!');
}
session_start();
//check logout
if (isset($_POST['logout']) && $_POST['logout'] == 1) {
    if ($_SESSION['uid'] == 1) {
        $_SESSION['uid'] = NULL;
        unset($_SESSION['uid']);
        session_destroy();
    }
} else {
    isset($_SESSION['uid']) && $_SESSION['uid'] > 0 && exit();
    if (isset($_POST['username']) && md5($_POST['username']) == login_user
        && isset($_POST['password']) && md5($_POST['password']) == login_pwd
    ) {
        $_SESSION['uid'] = 1;
        exit(json_encode([
            'code' => 0,
            'msg'  => 'success'
        ]));
    } else {
        ob_end_clean();
        exit(json_encode([
            'code' => 1,
            'msg'  => '账户不存在!'
        ]));
    }
}
?>