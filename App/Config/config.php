<?php
date_default_timezone_set('PRC');

define('APP_DIR', dirname(__DIR__));

//服务运行用户
define('run_user', 'www');

//pid文件路径
define('pid_file', APP_DIR . '/Run/ptimer.pid');

//任务数据文件
define('crontab_file', APP_DIR . '/Data/crontab.php');

//日志文件
define('log_file', APP_DIR . '/Log/error.log');

//共享内存key配置
define('shmkey', 0x12355);

//认证账号
define('login_user', '21232f297a57a5a743894a0e4a801fc3');
define('login_pwd', '21232f297a57a5a743894a0e4a801fc3');

//是否调试
define('is_debug', true);

//PHP可执行路径
define('PHP_BIN_PATH', '/usr/local/php/bin/php');

//socket服务地址
define("SOCK_ADDR", "tcp://127.0.0.1:9633");

//socket通信秘钥
define("SKEY", base64_encode("you_skey_here"));

//socket端口ip权限控制
const SOCK_ALLOW_IP = ['127.0.0.1'];

//web管理界面ip权限控制
const WEB_ALLOW_IP = ['127.0.0.1', '192.168.3.219'];
