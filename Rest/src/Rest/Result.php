<?php
/*
 * Project: study
 * File: Result.php
 * CreateTime: 16/1/31 18:49
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Result.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;

/**
 * 表示处理结果。最终作为Json返回给客户端
 * @class Result
 * @brief brief description
 *
 * elaborate description
 */
class Result
{
    public $content;

    public function error($errorCode, $errorString)
    {
        if($errorCode==0)
            return $this;
        $this->content['e'] = (int)$errorCode;
        $this->content['error'] = "$errorString";
        return $this;
    }

    public function data($data)
    {
        $this->content['data'] = $data;
        return $this;
    }

    public function warning($warning)
    {
        if(strlen($warning)===0)
            return $this;
        $old = @$this->content['warning'];
        $this->content['warning'] = $old . $warning;
        return $this;
    }

    public function debug($debugString)
    {
        if(strlen($debugString)===0)
            return $this;
        $old = @$this->content['debug'];
        $this->content['debug'] = $old . $debugString;
        return $this;
    }

    public function getJsonString()
    {
        if($this->content===null)
            return '{}';
        return json_encode($this->content, JSON_PRETTY_PRINT);
    }
}