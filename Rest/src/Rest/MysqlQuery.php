<?php
/*
 * Project: study
 * File: MysqlQuery.php
 * CreateTime: 16/1/27 00:02
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file MysqlQuery.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;

function mysqlEscapeString($string)
{
    if(is_string($string))
        return addcslashes($string, "\"'\\\r\n\t%_\0\x08");
    throw new \Exception(__FUNCTION__ . "(): 无效参数");
}
function mysqlEscapeColumnString($string)
{
    if(is_string($string))
        return addcslashes($string, "\"'\\\r\n\t\0\x08");
    throw new \Exception(__FUNCTION__ . "(): 无效参数");
}

interface IQueryCondition
{
    public function __toString();
}

class MysqlCondition implements IQueryCondition
{
    private $field;
    private $operator;
    private $value;

    public function __construct($field, $op, $value)
    {
        if (strlen($field)===0 || strlen($op)===0 || isset($value)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");
        $this->field = $field;
        $this->operator = $op;
        $this->value = $value;
    }

    public function __toString()
    {
        $field = mysqlEscapeString($this->field);
        $op = $this->operator;
        $value = $this->value;
        if (is_string($value)) {
            $value = mysqlEscapeString($value);
            $value = "'$value'";
        }
        elseif (is_object($value)) {
            $value = mysqlEscapeString($value . '');
            $value = "'$value'";
        }
        return "$field $op $value";
    }
}

class OrCondition implements IQueryCondition
{
    private $condition1;
    private $condition2;

    public function __construct(IQueryCondition $cond1, IQueryCondition $cond2)
    {
        $this->condition1 = $cond1;
        $this->condition2 = $cond2;
    }

    public function __toString()
    {
        return $this->condition1 . ' OR ' . $this->condition2;
    }
}

class AndCondition implements IQueryCondition
{
    private $conditions;

    public function addCondition(IQueryCondition $condition)
    {
        $this->conditions[] = $condition;
    }

    public function __toString()
    {
        if (count($this->conditions)===0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有有效条件");;
        return implode(' AND ', $this->conditions);
    }
}

/**
 * @class ParenthesisCondition
 * @brief brief 给已有条件加上括号，形成新条件
 *
 * elaborate 只能包住一个条件（但这个条件可以是AND或OR条件）
 */
class ParenthesisCondition implements IQueryCondition
{
    private $condition;

    public function __construct(MysqlCondition $condition)
    {
        $this->condition = $condition;
    }

    public function __toString()
    {
        return '(' . $this->condition . ')';
    }
}

/**
 * @class MysqlQuery
 * @brief brief description
 *
 * elaborate description
 */
class MysqlQuery
{
    protected $select = '*';
    protected $where;

    /**
     * @param $fields string|string[] 设置SELECT要返回的字段。默认是*
     * @throws \Exception
     */
    public function select($fields)
    {
        if (is_string($fields)) {
            if(strlen($fields)===0)
                $this->select = '*';
            else
                $this->select = mysqlEscapeColumnString($fields);
            return;
        }
        elseif (is_array($fields))
        {
            if (count($fields)===0)
            {
                $this->select = '*';
                return;
            }
            else {
                $invalidField = false;
                array_walk($fields, function (&$value) use (&$invalidField) {
                    if (is_string($value) === false)
                        $invalidField = true;
                    else
                        $value = mysqlEscapeColumnString($value);
                });
                if ($invalidField === false) {
                    $this->select = implode(',', $fields);
                    return;
                }
            }
        }

        throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数。\$fields必是字符串的数组");
    }

    public function where(IQueryCondition $condition)
    {
        $this->where = $condition;
    }

    public function getSqlString($tableName)
    {
        $sql = "SELECT $this->select FROM $tableName";
        if (isset($this->where)) {
            $where = $this->where;
            if (strlen($where)>0)
                $sql .= " WHERE $where";
        }
        return $sql;
    }

    public function getConditionString()
    {
        if (isset($this->where)) {
            $where = $this->where;
            if (strlen($where)>0)
                return (string)$where;
        }
        return '';
    }
}