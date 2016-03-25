<?php
/*
 * Project: study
 * File: JsonObject.php
 * CreateTime: 16/1/31 02:25
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file JsonObject.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Utils;

/**
 * @class JsonObject
 * @brief brief description
 *
 * elaborate description
 */
final class JsonArray implements \ArrayAccess
{
    private $array;

    public static function create(array $array=null)
    {
        return new JsonArray($array);
    }

    public function __construct(array $array=null)
    {
        if(is_array($array))
            $this->array = $array;
        else
            $this->array = [];
    }

    public function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

}