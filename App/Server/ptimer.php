<?php

namespace Ptimer;
require_once dirname(__DIR__) . '/Config/config.php';
require_once dirname(__DIR__) . '/task.php';

class ptimer
{
    public static $timer_tasks = [];
    public static $commands = ['start', 'stop', 'status'];
    
    public static $shm_id = NULL;
    
    public static $server_socket = NULL;
    
    const SOCKET_METHOD = ['remove', 'get', 'exists'];
    
    /**
     * 所有客户端socket连接
     * @var array
     */
    public static $connections = array();
    
    public static $connection_data = array();
    
    public static function start()
    {
        (new self)
            ->envCheck()
            ->commandParse()
            ->registerErrorHandler()
            ->installSignalHandler()
            ->mountServerSocket()
            ->daemonize()
            ->runAsUser()
            ->loadTasks()
            ->initShmop()
            ->beginTick()
            ->showResult()
            ->loop();
    }
    
    /**
     * @return $this
     */
    public function envCheck()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            exit("php version must newer than 5.3.0!\n");
        }
        if (!extension_loaded('pcntl')) {
            exit("pls install pcntl extension!\n");
        }
        if (!extension_loaded('posix')) {
            exit('pls install posix extension!' . "\n");
        }
        if (!extension_loaded('shmop')) {
            exit('pls install shmop extension!' . "\n");
        }
        return $this;
    }
    
    public function registerErrorHandler()
    {
        error_reporting(2047);
        ini_set('display_errors', 1);
        register_shutdown_function(function () {
            $errinfo = error_get_last();
            empty($errinfo) || $this->log(json_encode($errinfo));
            @unlink(pid_file);
            @shmop_delete(self::$shm_id);
        });
        return $this;
    }
    
    /**
     * @return $this
     */
    public function commandParse()
    {
        global $argv;
        if (!isset($argv[0]) || !isset($argv[1])) {
            exit("usage: php ptimer.php [start|stop|status]\n");
        }
        $pid = is_file(pid_file) ? (int)file_get_contents(pid_file) : 0;
        $cmd = $argv[1];
        in_array($cmd, self::$commands) || exit("the command '{$cmd}' is not support!\n");
        switch ($cmd) {
            case 'start':
                //the ptimer has booted
                if ($pid > 0 && posix_kill($pid, 0)) {
                    exit(0);
                }
                echo 'starting...' . "\n";
                break;
            case 'stop':
                @posix_kill($pid, SIGINT);
                exit(0);
                break;
            case 'status':
                posix_kill($pid, SIGUSR2);
                exit(0);
            default:
                exit(0);
                break;
        }
        return $this;
    }
    
    /**
     * @return $this
     */
    public function mountServerSocket()
    {
        self::$server_socket = stream_socket_server(SOCK_ADDR, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
        if (!self::$server_socket) {
            exit("socket error: [{$errno}], [{$errstr}]\n");
        }
        stream_set_blocking(self::$server_socket, 0);
        return $this;
    }
    
    /**
     * @return $this
     */
    public function beginTick()
    {
        pcntl_alarm(1);
        return $this;
    }
    
    /**
     * @return $this
     */
    public function showResult()
    {
        global $stdout, $stderr;
        @fclose(STDERR);
        @fclose(STDOUT);
        $stdout = fopen("/dev/null", 'a+');
        $stderr = fopen(log_file, 'a+');
        return $this;
    }
    
    /**
     * @return $this
     */
    public function daemonize()
    {
        umask(0);
        $pid = pcntl_fork();
        if ($pid < 0) {
            exit('daemonize fork error, pls try again!' . "\n");
        } elseif ($pid > 0) {
            exit(0);
        }
        if (posix_setsid() === -1) {
            exit("daemonize setsid error, pls try again!\n");
        }
        @touch(log_file);
        $pid = posix_getpid();
        if (file_put_contents(pid_file, $pid) === false) {
            exit('gen pid file error, Pls give the folder 777 mod!' . "\n");
        };
        return $this;
    }
    
    /**
     * @return $this
     */
    public function runAsUser()
    {
        $nobody = posix_getpwnam(run_user);
        @posix_setgid($nobody['gid']);
        @posix_setuid($nobody['uid']);
        @cli_set_process_title("ptimer daemon worker.");
        return $this;
    }
    
    /**
     * @return $this
     */
    public function loadTasks()
    {
        if (is_file(crontab_file)) {
            $tasks = require crontab_file;
            foreach ($tasks as $item) {
                $task = new task($item['command'], $item['interval'],
                    strtotime($item['triggerTime']), $item['is_persistent'], $item['title'], $item['task_type'], $item['weekday']);
                $task->last_exec_time = $item['last_exec_time'];
                $task->setTaskId($item['id']);
                if (!is_null($item['HisTime'])) {
                    $task->HisTime = date('H:i:s', strtotime($item['HisTime']));
                }
                self::$timer_tasks[$item['id']] = $task;
            }
        }
        return $this;
    }
    
    /**
     * @return $this
     */
    public function initShmop()
    {
        self::$shm_id = self::_getShmkey();
        return $this;
    }
    
    private static function _getShmkey()
    {
        if (is_null(self::$shm_id)) {
            self::$shm_id = shmop_open(shmkey, 'c', 0777, 1024);
        }
        return self::$shm_id;
    }
    
    /**
     * @return $this
     */
    public function installSignalHandler()
    {
        //注册闹钟处理函数
        pcntl_signal(SIGALRM, function () {
            pcntl_alarm(1);
            $time_now = time();
            $last_exec_time = date('Y-m-d H:i:s', $time_now);
            foreach (self::$timer_tasks as $timer_id => &$task) {
                if ($task->triggerTime <= $time_now) {
                    switch ($task->taskType) {
                        case TaskType::EVERY_DAY:
                            if (date('H:i:s', $time_now) != $task->HisTime) {
                                $task->triggerTime++;
                                continue 2;
                            }
                            break;
                        case TaskType::EVERY_WEEK:
                            if (date('w', $time_now) != $task->weekday || date('H:i:s', $time_now) != $task->HisTime) {
                                $task->triggerTime++;
                                continue 2;
                            }
                            break;
                        case TaskType::INTERVAL;
                            break;
                        case TaskType::ONCE:
                            break;
                    }
                    $flag = "php_timer_{$timer_id}";
                    $this->singletonCommand($flag, function () use (&$task, &$flag) {
                        $cmd = "{$task->command} {$flag} >/dev/null &";
                        if (!($task->is_persistent && $task->interval < 60) || in_array($task->taskType, [TaskType::EVERY_WEEK, TaskType::EVERY_DAY])) {
                            //周期执行，并且间隔超过1分钟的记log
                            $this->log('execute_task: ' . $cmd);
                        }
                        system($cmd);
                    });
                    $task->exec_num++;
                    $task->last_exec_time = $last_exec_time;
                    if ($task->is_persistent) {
                        $task->triggerTime += $task->interval;
                    } else {
                        unset(self::$timer_tasks[$timer_id]);
                    }
                }
            }
        });
        
        //注册共享内存可读处理函数
        pcntl_signal(SIGUSR1, function () {
            $task = trim(shmop_read(self::_getShmkey(), 0, 1024));
            $task = json_decode($task);
            if (empty($task)) {
                return;
            }
            if (isset($task->command) && $task->command == 'remove') {
                unset(self::$timer_tasks[$task->id]);
            } else {
                self::$timer_tasks[$task->id] = $task;
            }
            $this->formatCrontab();
        });
        
        //注册status命令处理函数
        pcntl_signal(SIGUSR2, function () {
            $this->formatCrontab();
        });
        
        //注册stop命令处理函数
        pcntl_signal(SIGINT, function () {
            is_file(pid_file) && unlink(pid_file);
            @shmop_delete(self::$shm_id);
            @fclose(self::$server_socket);
            exit(0);
        });
        return $this;
    }
    
    
    public function formatCrontab()
    {
        $all_task = array();
        foreach (self::$timer_tasks as $item) {
            $all_task[] = [
                'id'             => $item->id,
                'interval'       => $item->interval,
                'command'        => $item->command,
                'triggerTime'    => $item->triggerTime > 0 ? date('Y-m-d H:i:s', $item->triggerTime) : 0,
                'is_persistent'  => $item->is_persistent,
                'exec_num'       => $item->exec_num,
                'HisTime'        => $item->HisTime,
                'title'          => $item->title,
                'task_type'      => $item->taskType,
                'weekday'        => isset(TaskType::$weekdayMap[$item->weekday]) ? TaskType::$weekdayMap[$item->weekday] : '',
                'last_exec_time' => $item->last_exec_time
            ];
        }
        $all_task = var_export($all_task, true);
        file_put_contents(crontab_file, "<?php\r\n return " . $all_task . "; \r\n");
    }
    
    /**
     * 一个command只能用一个进程。防止定时脚本还没执行完，但是定时器已经触发多次，
     * 造成同一个php shell command运行N个，影响系统稳定性
     * @param $flag 自定义的command标识
     * @param Closure $func
     */
    public function singletonCommand($flag, \Closure $func)
    {
        //获取命令输出的最后一行
        $last_line = system("ps aux | grep {$flag} | grep -v grep");
        if (empty($last_line)) {
            $func();
        }
    }
    
    public function log($str)
    {
        if (empty($str)) {
            return;
        }
        $path = APP_DIR . '/Log/' . date('Y/m/d/');
        is_dir($path) || mkdir($path, 0777, true);
        $log_file = $path . 'ptimer.log';
        return file_put_contents($log_file, '[' . date('Y-m-d H:i:s') . '] ' . $str . "\n", FILE_APPEND);
    }
    
    public function loop()
    {
        while (1) {
            pcntl_signal_dispatch();
            $reads = $writes = array_merge([self::$server_socket], self::$connections);
            $except = NULL;
            if (stream_select($reads, $writes, $except, 0)) {
                foreach ($reads as $read) {
                    if ($read == self::$server_socket) {
                        //有新的连接
                        $this->accept();
                    } else {
                        //连接可读
                        $buffer = fread($read, 8192);
                        $data = json_decode($buffer, true);
                        $fd_key = (int)$read;
                        if (empty($data) || !isset($data['signature']) || !isset($data['body']['command'])
                            || empty($data['signature']) || empty($data['body']['command'])
                        ) {
                            $this->renderError($read, 'the package invalid, please check all the field is set!');
                            continue;
                        }
                        $command = trim($data['body']['command']);
                        if (in_array($command, self::SOCKET_METHOD)) {
                            //删除|查询|检查 任务
                            if (!isset($data['body']['taskId']) || empty($data['body']['taskId'])) {
                                $this->renderError($read, 'the taskId is empty!');
                                continue;
                            }
                            $task_id = trim($data['body']['taskId']);
                            if ($data['signature'] !== md5($command . $task_id . SKEY)) {
                                $this->log("{$command} task通信数据包非法!" . json_encode($data));
                                $this->renderError($read, 'the sign invalid!');
                                continue;
                            }
                            switch ($command) {
                                case "get":
                                    self::$connection_data[$fd_key] = json_encode(self::$timer_tasks[$task_id]);
                                    $this->send($read);
                                    break;
                                case "remove":
                                    self::$connection_data[$fd_key] = 0;
                                    if (isset(self::$timer_tasks[$task_id])) {
                                        unset(self::$timer_tasks[$task_id]);
                                        self::$connection_data[$fd_key] = 1;
                                    }
                                    $this->send($read);
                                    break;
                                case "exists":
                                    self::$connection_data[$fd_key] = isset(self::$timer_tasks[$task_id]) ? 1 : 0;
                                    $this->send($read);
                                    break;
                            }
                            
                        } else {
                            //新增任务
                            if (!isset($data['body']['title']) || !isset($data['body']['triggerTime'])) {
                                $this->renderError($read, 'title or triggerTime is required!');
                                continue;
                            }
                            $title = trim($data['body']['title']);
                            $triggerTime = (int)$data['body']['triggerTime'];
                            if ($triggerTime <= 0) {
                                $this->renderError($read, 'triggerTime must be integer!');
                                continue;
                            }
                            if ($data['signature'] !== md5($command . $title . $triggerTime . SKEY)) {
                                $this->log('add task通信数据包非法!' . json_encode($data));
                                $this->renderError($read, 'the sign is not match!');
                                continue;
                            }
                            try {
                                $timer_task = new task($command, 1, $triggerTime, true, $title, TaskType::ONCE);
                                self::$timer_tasks[$timer_task->getTaskId()] = $timer_task;
                                self::$connection_data[$fd_key] = $timer_task->getTaskId();
                                $this->send($read);
                            } catch (\Exception $ex) {
                                $this->_clearClientData((int)$read);
                                $this->log("socket add task error: " . $ex->getMessage());
                            }
                        }
                    }
                }
                foreach ($writes as $write) {
                    //当一次写入失败时, 可多次检查可写
                    $fd_key = (int)$write;
                    if (empty(self::$connection_data[$fd_key]) || fwrite($write, self::$connection_data[$fd_key]) !== false) {
                        $this->_clearClientData($fd_key);
                    }
                }
            }
            usleep(50000);
        }
    }
    
    public function send($fd)
    {
        $fd_key = (int)$fd;
        if (fwrite($fd, self::$connection_data[$fd_key]) !== false) {
            $this->_clearClientData($fd_key);
        } else {
            $this->_sendErrorLog($fd, self::$connection_data[$fd_key]);
        }
    }
    
    public function accept()
    {
        $client = stream_socket_accept(self::$server_socket, 0);
        if (is_resource($client)) {
            //ip deny
            socket_getpeername(socket_import_stream($client), $addr, $port);
            if (!in_array($addr, SOCK_ALLOW_IP)) {
                fclose($client);
                self::log("reject illegal request: [{$addr}:{$port}]");
                return false;
            }
            stream_set_blocking($client, 0);
            self::$connections[(int)$client] = $client;
            return true;
        }
    }
    
    public function renderError($fd, $errstr)
    {
        fwrite($fd, $errstr);
        $this->_clearClientData((int)$fd);
    }
    
    private function _clearClientData($fd_key)
    {
        fclose(self::$connections[$fd_key]);
        unset(self::$connections[$fd_key]);
        unset(self::$connection_data[$fd_key]);
    }
    
    private function _sendErrorLog($fd, $str = '')
    {
        socket_getpeername(socket_import_stream($fd), $client_addr, $client_port);
        $this->log("数据写入客户端[{$client_addr}:{$client_port}]失败, {$str}");
    }
    
}

//boot the php timer
if (PHP_SAPI == 'cli') {
    ptimer::start();
}
