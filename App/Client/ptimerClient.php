<?php

namespace Ptimer;
require_once dirname(__DIR__) . '/Config/config.php';
require_once dirname(__DIR__) . '/task.php';

class ptimerClient
{
    
    /**
     * 将数据写入共享内存
     * @param $task
     */
    public static function writeToshmop($task)
    {
        $task_data = json_encode($task);
        $shmid = shmop_open(shmkey, 'c', 0777, 1024);
        $task_data = str_pad($task_data, 1024, "\0");
        $flag = shmop_write($shmid, $task_data, 0);
        @shmop_close($shmid);
        if ($flag) {
            self::_notifyMasterWorker(SIGUSR1);
        }
    }
    
    private static function _notifyMasterWorker($signal)
    {
        $pid = (int)@file_get_contents(pid_file);
        if ($pid > 0) {
            $pk = posix_kill($pid, $signal);
            if ($pk === false) {
                self::log('send signal ' . $signal . ' to ' . $pid . ' failure!');
            }
        }
    }
    
    public static function ping()
    {
        $pid = is_file(pid_file) ? (int)file_get_contents(pid_file) : 0;
        if ($pid > 0 && posix_kill($pid, 0)) {
            return true;
        }
        return false;
    }
    
    public static function log($str)
    {
        if (empty($str)) {
            return;
        }
        $path = APP_DIR . '/Log/' . date('Y/m/d/');
        is_dir($path) || mkdir($path, 0777, true);
        $log_file = $path . 'ptimer.log';
        return file_put_contents($log_file, '[' . date('Y-m-d H:i:s') . '] ' . $str . "\n", FILE_APPEND);
    }
    
    public static function add(task $task)
    {
        $task->id = spl_object_hash($task);
        if ($task->is_persistent) {
            $task->triggerTime = time();
        }
        self::writeToshmop($task);
    }
    
    public static function remove($timer_id)
    {
        $task = new \stdClass();
        $task->id = $timer_id;
        $task->command = 'remove';
        self::writeToshmop($task);
    }
    
    public static function getCrontabList()
    {
        self::_notifyMasterWorker(SIGUSR2);
        //让ptimer进程由足够的时间更新crontab_file
        usleep(10000);
        return is_file(crontab_file) ? (require crontab_file) : false;
    }
}