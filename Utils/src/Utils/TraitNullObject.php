<?php
/*
 * Project: study
 * File: TraitNullObject.php
 * CreateTime: 16/1/29 02:19
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file TraitNullObject.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Utils;

/**
 * @trait TraitNullObject
 * @brief brief description
 *
 * elaborate description
 */
trait TraitNullObject
{
    protected static $nullObject; //null对象

    private static function getNullObject() //返回一个null对象。
    {
        if (static::$nullObject === null)
            static::$nullObject = new static;
        return static::$nullObject;
    }

    /**
     * 如果一个方法的返回值类型是Model或者null，则返回的对象是没有自动代码提示的，
     * 因为不知道返回的对象是Model对象还是null。
     * nullObject的作用就是取代null作为返回值，这样方法的返回值类型就是单一的Model类型。
     * 此时代码自动提示功能就可以正常工作了。
     * 不要修改null对象的属性（虽然可以修改）
     * @return bool 返回值表示当前对象是否是Null对象
     */
    final public function isNullObject()
    {
        return static::$nullObject === $this;
    }
}