<?php
namespace Ptimer;

/**
 * Created by PhpStorm.
 * User: randy
 * Date: 2016/10/19
 * Time: 17:51
 */
class task
{
    public $id;
    
    public $taskType = 0;
    /**
     * @var null 间隔的秒数
     */
    public $interval = NULL;
    
    public $command;
    
    public $last_exec_time;
    /**
     * 每周几或者每天执行时的时间H:i:s格式
     * @var string
     */
    public $HisTime;
    /**
     * 任务标题简介
     * @var string
     */
    public $title = '';
    
    public $is_persistent = true;
    
    /**
     * 任务已经被执行的次数
     * @var int
     */
    public $exec_num = 0;
    /**
     * 是否暂停task
     * @var bool
     */
    public $stop = false;
    
    /**
     * 工作日周几
     * @var string
     */
    public $weekday;
    /**
     * @var 定时脚本触发的时间戳
     */
    public $triggerTime;
    
    
    public function __construct($command, $interval, $triggerTime, $persistent = true, $title = '', $type, $weekday = '')
    {
        if (empty($command)) {
            throw new \Exception('Pls set task command !');
            return;
        }
        if (empty($title)) {
            throw new \Exception('Pls set task title!');
            return;
        }
        
        
        $this->command = $command;
        $this->title = $title;
        $this->taskType = $type;
        $this->weekday = $weekday;
        $interval <= 0 && $interval = 1;
        $this->interval = $interval;
        if (!$persistent || $type == TaskType::ONCE) {
            $this->triggerTime = $triggerTime;
            $this->is_persistent = false;
        } else {
            $this->HisTime = $triggerTime;
            $this->triggerTime <= 0 && $this->triggerTime = time();
            
        }
        if (empty($this->id)) {
            $this->id = spl_object_hash($this);
        }
        
    }
    
    public function getTaskId()
    {
        return $this->id;
    }
    
    public function setTaskId($id)
    {
        $this->id = $id;
    }
    
}

class TaskType
{
    /**
     * 每天某个时刻执行
     */
    const EVERY_DAY = 1;
    
    /**
     * 每周几的某时刻执行
     */
    const EVERY_WEEK = 2;
    
    /**
     * 每隔多少S执行
     */
    const INTERVAL = 8;
    
    /**
     * 某个具体时刻执行一次
     */
    const ONCE = 16;
    
    const DEL = 32;
    
    public static $weekdayMap = [
        1 => '一',
        2 => '二',
        3 => '三',
        4 => '四',
        5 => '五',
        6 => '六',
        0 => '日'
    ];
    
}