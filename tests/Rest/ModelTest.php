<?php
/*
 * Project: webgeeker-rest
 * File: ModelTest.php
 * CreateTime: 2017/4/2 12:27
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file ModelTest.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\RestTest;

use PHPUnit\Framework\TestCase;

/**
 * @class ModelTest
 * @brief brief description
 *
 * elaborate description
 */
class ModelTest extends TestCase
{
    // $callback必须抛出异常
    private function _assertThrowExpection(callable $callback, $message = '')
    {
        if(is_callable($callback) === false)
            throw new \Exception("\$callback不是可执行函数");
        try {
            $callback();
            $ret = true;
        } catch (\Exception $e) {
            $ret = false;
        }
        $this->assertFalse($ret, $message);
    }

    public function testAAA()
    {
        $this->assertTrue(true);
    }
}