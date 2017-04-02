<?php
/*
 * Project: webgeeker-rest
 * File: ValidationTest.php
 * CreateTime: 2017/4/2 11:51
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file ValidationTest.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\RestTest;

use PHPUnit\Framework\TestCase;
use \WebGeeker\Rest\Validation;

/**
 * @class ValidationTest
 * @brief brief description
 *
 * elaborate description
 */
class ValidationTest extends TestCase
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
    
    public function testValidateIntXXX()
    {
        // Int
        $this->assertNotNull(Validation::validateInt('-1'));
        $this->assertNotNull(Validation::validateInt('0'));
        $this->assertNotNull(Validation::validateInt('1'));
        $this->assertNotNull(Validation::validateInt(-1));
        $this->assertNotNull(Validation::validateInt(0));
        $this->assertNotNull(Validation::validateInt(1));
        $this->_assertThrowExpection(function () {
            Validation::validateInt(true);
        }, 'line ' . __LINE__ . ": Validation::validateInt(true)应该抛出异常");
        $this->_assertThrowExpection(function () {
            Validation::validateInt([]);
        }, 'line ' . __LINE__ . ": Validation::validateInt([])应该抛出异常");
        $this->_assertThrowExpection(function () {
            Validation::validateInt(0.0);
        }, 'line ' . __LINE__ . ": Validation::validateInt(0.0)应该抛出异常");
        $this->_assertThrowExpection(function () {
            Validation::validateInt('abc');
        }, 'line ' . __LINE__ . ": Validation::validateInt('abc')应该抛出异常");

        // IntGt
        $this->assertNotNull(Validation::validateIntGt('1', 0));
        $this->_assertThrowExpection(function () {
            Validation::validateIntGt('0', 0);
        }, 'line ' . __LINE__ . ": Validation::validateIntGt('0', 0)应该抛出异常");
        $this->assertNotNull(Validation::validateIntGt(1, 0));
        $this->_assertThrowExpection(function () {
            Validation::validateIntGt(0, 0);
        }, 'line: ' . __LINE__ . ": Validation::validateIntGt(0, 0)应该抛出异常");
        $this->_assertThrowExpection(function () {
            Validation::validateIntGt(false, 0);
        }, 'line: ' . __LINE__ . ": Validation::validateIntGt(false, 0)应该抛出异常");

        // IntGe
        $this->assertNotNull(Validation::validateIntGe('1', 0));
        $this->assertNotNull(Validation::validateIntGe('0', 0));
        $this->assertNotNull(Validation::validateIntGe(1, 0));
        $this->assertNotNull(Validation::validateIntGe(0, 0));

        // IntLt
        $this->assertNotNull(Validation::validateIntLt('-1', 0));
        $this->_assertThrowExpection(function () {
            Validation::validateIntLt('0', 0);
        }, 'line ' . __LINE__ . ": Validation::validateIntLt('0', 0)应该抛出异常");
        $this->assertNotNull(Validation::validateIntLt(-1, 0));
        $this->_assertThrowExpection(function () {
            Validation::validateIntLt(0, 0);
        }, 'line: ' . __LINE__ . ": Validation::validateIntLt(0, 0)应该抛出异常");
        $this->_assertThrowExpection(function () {
            Validation::validateIntLt(false, 0);
        }, 'line: ' . __LINE__ . ": Validation::validateIntLt(false, 0)应该抛出异常");

        // IntLe
        $this->assertNotNull(Validation::validateIntLe('-1', 0));
        $this->assertNotNull(Validation::validateIntLe(-1, 0));
        $this->assertNotNull(Validation::validateIntLe('0', 0));
        $this->assertNotNull(Validation::validateIntLe(0, 0));

        // IntGeAndLe
        $this->assertNotNull(Validation::validateIntGeAndLe('0', 0, 0));
        $this->assertNotNull(Validation::validateIntGeAndLe(0, 0, 0));
        $this->assertNotNull(Validation::validateIntGeAndLe('11', -100, 100));
        $this->assertNotNull(Validation::validateIntGeAndLe(11, -100, 100));
        $this->assertNotNull(Validation::validateIntGeAndLe('00123', 123, 123));

        // IntGtAndLt
        $this->assertNotNull(Validation::validateIntGtAndLt('0', -1, 1));
        $this->assertNotNull(Validation::validateIntGtAndLt(0, -1, 1));

        // IntGtAndLe
        $this->assertNotNull(Validation::validateIntGtAndLe('0', -1, 0));
        $this->assertNotNull(Validation::validateIntGtAndLe(0, -1, 0));

        // IntGeAndLt
        $this->assertNotNull(Validation::validateIntGeAndLt('0', 0, 1));
        $this->assertNotNull(Validation::validateIntGeAndLt(0, 0, 1));

        // IntIn
        $this->assertNotNull(Validation::validateIntIn('0', [0, 1]));
        $this->assertNotNull(Validation::validateIntIn(0, [0, 1]));

        // IntNotIn
        $this->assertNotNull(Validation::validateIntNotIn('-1', [0, 1]));
        $this->assertNotNull(Validation::validateIntNotIn(-1, [0, 1]));

    }

    public function testValidateFloatXXX()
    {
        $this->assertNotNull(Validation::validateFloat('-12311112311111'));
        $this->assertNotNull(Validation::validateFloatGtAndLt('10.', -100, 100));
    }

    public function testValidateString()
    {
        $this->assertNotNull(Validation::validateFloat('-12311112311111'));
        $this->assertNotNull(Validation::validateFloatGtAndLt('10.', -100, 100));
    }

    public function testValidateRegexp()
    {
        $this->assertNotNull(Validation::validateRegexp('10.', '/^[0-9.]+$/', '这是原因'));
        $this->assertNotNull(Validation::validateRegexp('10/abcd', '/^[0-9]+\/abcd$/'));
    }

    public function testValidateArray()
    {
        $this->assertNotNull(Validation::validateArray([]));
        $this->assertNotNull(Validation::validateArray([1,2,3]));
        $this->_assertThrowExpection(function () {
            Validation::validateArray(1);
        }, 'line ' . __LINE__ . ": Validation::validateArray(1)应该抛出异常");
        $this->_assertThrowExpection(function () {
            Validation::validateArray(['a'=>1]);
        }, 'line ' . __LINE__ . ": Validation::validateArray(['a'=>1])应该抛出异常");
    }

    public function testValidateObject()
    {
        $this->assertNotNull(Validation::validateObject([]));
        $this->assertNotNull(Validation::validateObject(['a'=>1]));
        $this->_assertThrowExpection(function () {
            Validation::validateObject(1.23);
        }, 'line ' . __LINE__ . ": Validation::validateObject(1.23)应该抛出异常");
        $this->_assertThrowExpection(function () {
            Validation::validateObject([1,2,3]);
        }, 'line ' . __LINE__ . ": Validation::validateObject([1,2,3])应该抛出异常");
    }

    public function testValidateFile()
    {
    }

    public function testValidateOthers()
    {
    }

}