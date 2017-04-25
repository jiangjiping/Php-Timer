
### Web界面化管理计划任务. 支持每周几某个时刻, 每天某个时刻, 周期执行, 某个时刻执行一次.

### 默认账号密码为admin

### 自定义的配置可以在/App/Config/config.php中修改, 建议socket端口只开内网

### 当前socket只支持添加一次性的任务

### ptimer服务停止时, 所有的任务都保存在系统硬盘中,当启动时, 会自动加载硬盘中的任务到内存中

### nginx 配置文件 ,这边假设你的项目路径为: /data/www/ptimer/

server
{
        listen 80;
        server_name ptimer.dev;
        index index.html index.htm index.php default.html default.htm default.php;
        root  /data/www/ptimer/public;

        location / {
            index  index.html index.htm index.php;
            if ( !-e $request_filename) {
                rewrite ^/(.*)$ /index.php/$1   last;
                break;
            }
        }

        location ~ .php(/?.*)$ {
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;

            #path support
            fastcgi_split_path_info ^(.+\.php)(.*)$;
            fastcgi_param PATH_INFO $fastcgi_path_info;
            fastcgi_param PATH_TRANSLATE $document_root$fastcgi_path_info;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

            include        fastcgi_params;
        }
}


### 启动方法 切换到/App/Server 目录
    php ptimer.php start
    关闭方法
    php ptimer.php stop
    强行杀死
    ps aux | grep ptimer
    然后找到进程pid执行: kill $pid

