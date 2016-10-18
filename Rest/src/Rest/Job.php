<?php
/*
 * Project: simpleim-php
 * File: Job.php
 * CreateTime: 16/6/20 23:06
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Job.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;


/**
 * @class Job
 * @package WebGeeker\Rest
 * @brief brief description
 *
 * elaborate description
 */
abstract class Job
{
    public $submitJobTime; // 提交任务的时间

    final public function run()
    {
        $this->perform();
    }

    protected function perform() //实际执行任务的方法, 子类需要重载
    {
    }

    /**
     * @param $message
     * @return null|Job
     */
    public static function fromMessage($message)
    {
        $taskInfo = json_decode($message, true);
        if(!$taskInfo) // 不是json
            return null;

        $taskClass = @$taskInfo['class']; // 任务类名

        if(strlen($taskClass)===0) // 不是任务消息
            return null;

        if(class_exists($taskClass)===false) //任务类不存在
            return null;
            
        $task = new $taskClass;

        if(($task instanceof Job)===false)
            return null;

//        if(method_exists($task, 'perform')===false)
//            return null;

        $info = @$taskInfo['info']; // 任务参数
        foreach ($info as $key => $value) {
            $task->$key = $value;
        }
        return $task;
    }

    public function toMessage()
    {
        if(!isset($this->submitJobTime))
            $this->submitJobTime = microtime(true);

        $msg = [
            'class' => get_called_class(),
            'info' => array_filter((array)$this), // $this转为array, 并且过滤掉null值
        ];
        return json_encode($msg, JSON_PRETTY_PRINT);
    }

}