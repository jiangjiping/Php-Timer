<?php
define("SKEY", 'your skey here');

function package($cmd = '', $taskId = '')
{
    $data = [];
    switch ($cmd) {
        case "get":
            $data['body'] = array(
                'command' => 'get',
                'taskId'  => $taskId
            );
            $data['signature'] = md5($data['body']['command'] . $data['body']['taskId'] . SKEY);
            break;
        case "exists":
            $data['body'] = array(
                'command' => 'exists',
                'taskId'  => $taskId
            );
            $data['signature'] = md5($data['body']['command'] . $data['body']['taskId'] . SKEY);
            break;
        case "remove":
            $data['body'] = array(
                'command' => 'remove',
                'taskId'  => $taskId
            );
            $data['signature'] = md5($data['body']['command'] . $data['body']['taskId'] . SKEY);
            break;
        default:
            $data = array(
                'body' => [
                    'command'     => '/usr/local/php/bin/php index.php yourfunc',
                    'title'       => '检查...',
                    'triggerTime' => time() + 100, //任务触发时间戳
                ]
            );
            $data['signature'] = md5($data['body']['command'] . $data['body']['title'] . $data['body']['triggerTime'] . SKEY);
            break;
    }
    return $data;
}

function add()
{
    $data = package();
    $client = stream_socket_client('tcp://127.0.0.1:9633', $errno, $errstr);
    if (!$client) {
        exit($errno . $errstr);
    }
    $flag = fwrite($client, json_encode($data));
    if ($flag) {
        echo "send OK!<br/>";
    }
    $result = fread($client, 1024);
    echo $result;
    fclose($client);
}


function get($taskId)
{
    $data = package("get", $taskId);
    $client = stream_socket_client('tcp://127.0.0.1:9633', $errno, $errstr);
    if (!$client) {
        exit($errno . $errstr);
    }
    $flag = fwrite($client, json_encode($data));
    if (!$flag) {
        echo "send failure!<br/>";
    }
    $result = fread($client, 1024);
    echo $result;
    fclose($client);
}

function remove($taskId)
{
    $data = package('remove', $taskId);
    $client = stream_socket_client('tcp://127.0.0.1:9633', $errno, $errstr);
    if (!$client) {
        exit($errno . $errstr);
    }
    $flag = fwrite($client, json_encode($data));
    if ($flag) {
        echo "send OK!<br/>";
    }
    $result = fread($client, 1024);
    echo $result;
    fclose($client);
}

function exists($taskId)
{
    $data = package('exists', $taskId);
    $client = stream_socket_client('tcp://127.0.0.1:9633', $errno, $errstr);
    if (!$client) {
        exit($errno . $errstr);
    }
    $flag = fwrite($client, json_encode($data));
    if ($flag) {
        echo "send OK!<br/>";
    }
    $result = fread($client, 1024);
    var_dump($result);
    fclose($client);
}


//新增任务
//add();

//删除任务
//remove('000000004e3b596a000056482441ded9');

//get('000000004e3b5968000056482441ded9');

//exists('000000004e3b5968000056482441ded9');

?>