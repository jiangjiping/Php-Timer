<?php
/**
 * Created by PhpStorm.
 * User: randy
 * Date: 2016/10/19
 * Time: 17:55
 */
ini_set('display_errors', 1);
error_reporting(2047);
define("APP_PATH", dirname(__DIR__) . '/App');
require_once APP_PATH . '/Client/ptimerClient.php';
require_once APP_PATH . '/task.php';

use Ptimer\ptimerClient;
use Ptimer\TaskType;
use Ptimer\task;

$ip_list = array(
    '127.0.0.1', '110.83.28.97', '192.168.3.219'
);
if (!isset($_SERVER['REMOTE_ADDR']) || !in_array($_SERVER['REMOTE_ADDR'], $ip_list)) {
    exit('Access Deny!');
}
session_start();
if ($_SESSION['uid'] <= 0) {
    header("Location: /login.php");
    exit();
}
$ptimer_status = ptimerClient::ping();
if (isset($_POST['key'])) {
    $dataset = array();
    $key = $_POST['key'];
    switch ($key) {
        case 'save_get':
            $dataset = ptimerClient::getCrontabList();
            break;
        case 'remove_get':
            $timer_id = $_POST['timer_id'];
            empty($timer_id) || ptimerClient::remove($timer_id);
            $dataset = ptimerClient::getCrontabList();
            break;
        case 'add_get':
            $command = $_POST['command'];
            if (empty($command)) {
                exit(json_encode([
                    'code' => 1,
                    'msg'  => '请设置command！'
                ]));
            }
            $title = $_POST['title'];
            if (empty($title)) {
                exit(json_encode([
                    'code' => 1,
                    'msg'  => '请设置title！'
                ]));
            }
            $type = $_POST['task_type'];
            $interval = isset($_POST['interval']) ? intval($_POST['interval']) : 0;
            $triggerTime = empty($_POST['trigger_time']) ? 0 : strtotime($_POST['trigger_time']);
            $is_persistent = true;
            switch ($type) {
                case TaskType::EVERY_DAY:
                    $triggerTime <= 0 && exit(json_encode([
                        'code' => 1,
                        'msg'  => '请设置触发时间'
                    ]));
                    $triggerTime = date('H:i:s', $triggerTime);
                    ptimerClient::add(new task($command, $interval, $triggerTime, true, $title, $type));
                    break;
                case TaskType::EVERY_WEEK;
                    $triggerTime <= 0 && exit(json_encode([
                        'code' => 1,
                        'msg'  => '请设置触发时间'
                    ]));
                    $triggerTime = date('H:i:s', $triggerTime);
                    $weekday = $_POST['weekday'];
                    ptimerClient::add(new task($command, $interval, $triggerTime, true, $title, $type, $weekday));
                    break;
                case TaskType::INTERVAL;
                    $interval <= 0 && $interval = 1;
                    ptimerClient::add(new task($command, $interval, $triggerTime, true, $title, $type));
                    break;
                case TaskType::ONCE;
                    $is_persistent = false;
                    $triggerTime <= 0 && exit(json_encode([
                        'code' => 1,
                        'msg'  => '请设置触发时间'
                    ]));
                    ptimerClient::add(new task($command, $interval, $triggerTime, $is_persistent, $title, $type));
                    break;
            }
            $interval = isset($_POST['interval']) ? (int)$_POST['interval'] : 1;
            
            if ($is_persistent) {
                $interval <= 0 && $interval = 1;
                $time = $interval;
            }
            
            $dataset = ptimerClient::getCrontabList();
        default:
            break;
    }
    if (is_array($dataset) && !empty($dataset)) {
        exit(json_encode([
            'code'  => 0,
            'items' => $dataset
        ]));
    } else {
        exit(json_encode([
            'code'  => 0,
            'items' => NULL
        ]));
    }
    
}


include 'index.view.php';