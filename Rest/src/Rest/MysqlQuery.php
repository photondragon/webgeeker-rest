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

/**
 * @class MysqlQuery
 * @brief brief description
 *
 * elaborate description
 */
class MysqlQuery implements IQuery
{
    protected $select = '*';
    protected $where;
    protected $orderBys = [];
    protected $offset = 0;
    protected $count = 0; //0表示全部

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

    /**
     * 设置排序字段. 可以分多次设置多个排序字段
     * 相当于Mysql中的ORDER BY
     * @param $fieldName
     * @param string $order 三种取值: 'asc'-升序, 'desc'-降序, ''-无
     */
    public function orderBy($fieldName, $order = '')
    {
        if(strlen($fieldName)){
            $o = '';
            if(strtolower($order)==='asc')
                $o = ' ASC';
            elseif(strtolower($order)==='desc')
                $o = ' DESC';

            $this->orderBys[] = "`$fieldName`$o";
        }
    }

    /**
     * 设置截取结果集中的一段
     * 相当于Mysql中的LIMIT
     * $offset>0 && $count==0 这种情况是无效的. 只有$count>0 才会分段
     * @param $count int 个数. 0表示全部
     * @param $offset int 起始位置
     */
    public function segment($count, $offset = 0)
    {
        if($offset<0)
            $offset = 0;
        if($count<0)
            $count = 0;
        $this->offset = $offset;
        $this->count = $count;
    }

    public function getQueryString($tableName)
    {
        $sql = "SELECT $this->select FROM `$tableName`";
        if (isset($this->where)) {
            $where = $this->where;
            if (strlen($where)>0)
                $sql .= " WHERE $where";
        }
        if(count($this->orderBys)){
            $sql .= ' ORDER BY ' . implode(',', $this->orderBys);
        }
        if($this->offset && $this->count)
            $sql .= " LIMIT {$this->offset},$this->count";
        else if($this->count)
            $sql .= " LIMIT $this->count";
        return $sql;
    }

    /**
     * @return IQueryConditionAnd
     */
    public function createConditionAnd()
    {
        $andCond = new MysqlConditionAnd();

        if(func_num_args()>0) // 参数个数
        {
            $args = func_get_args(); //获取所有参数的数组
            foreach ($args as $arg) {
                if($arg instanceof IQueryCondition)
                    $andCond->addCondition($arg);
            }
        }

        return $andCond;
    }

    /**
     * @param IQueryCondition $cond1
     * @param IQueryCondition $cond2
     * @return IQueryConditionOr
     */
    public function createConditionOr(IQueryCondition $cond1, IQueryCondition $cond2)
    {
        return new MysqlConditionOr($cond1, $cond2);
    }

    /**
     * @param $field
     * @param $op
     * @param $value
     * @return IQueryConditionCompare
     */
    public function createConditionCompare($field, $op, $value)
    {
        return new MysqlConditionCompare($field, $op, $value);
    }

    /**
     * @param IQueryCondition $condition
     * @return IQueryConditionParenthesis
     */
    public function createConditionParenthesis(IQueryCondition $condition)
    {
        return new MysqlConditionParenthesis($condition);
    }
}

class MysqlConditionCompare implements IQueryConditionCompare
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
        $field = mysqlEscapeColumnString($this->field);
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
        return "`$field` $op $value";
    }
}

class MysqlConditionOr implements IQueryConditionOr
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

class MysqlConditionAnd implements IQueryConditionAnd
{
    private $conditions;

    public function addCondition(IQueryCondition $condition)
    {
        $this->conditions[] = $condition;
    }

    public function __toString()
    {
        if (count($this->conditions)===0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有有效条件");
        return (string)implode(' AND ', $this->conditions);
    }
}

/**
 * @class MysqlConditionParenthesis
 * @brief brief 给已有条件加上括号，形成新条件
 *
 * elaborate 只能包住一个条件（但这个条件可以是AND或OR条件）
 */
class MysqlConditionParenthesis implements IQueryConditionParenthesis
{
    private $condition;
    public function __construct(IQueryCondition $condition)

    {
        $this->condition = $condition;
    }

    public function __toString()
    {
        return '(' . $this->condition . ')';
    }
}

