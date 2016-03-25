<?php
/*
 * Project: study
 * File: Model.php
 * CreateTime: 16/1/22 19:41
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Model.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;

use \WebGeeker\Utils\TraitNullObject;

/**
 * @class Model
 * @brief brief description
 *
 * elaborate description
 */
class Model
{
    use TraitNullObject;

    // 主键数据类型
    const PrimaryKeyTypeInt = null;
    const PrimaryKeyTypeString = 1;
    const PrimaryKeyTypeFloat = 2;

    //以下是类唯一属性。以延迟静态绑定（static::$xxx）的方式访问，子类可以覆盖这些属性
    protected static $primaryKey = 'id'; //主键，默认'id'
    protected static $primaryKeyType; //主键数据类型，默认int
    protected static $ignoredProperties; // self::getFieldNames()会自动忽略的属性列表。默认null

    final public static function getPrimaryKey()
    {
        return static::$primaryKey;
    }

    protected static $table; //延迟静态绑定（类唯一）

    /**
     * @return Table
     */
    protected static function getTable() //子类可以重载此方法，连接非默认的数据库
    {
        if (isset(static::$table)==false) {
            static::$table = new Table(Database::getDefaultDB(), get_called_class());
        }
        return static::$table;
    }

    protected static $fieldNames; // 所有（要存入数据库的）字段名，会自动生成（忽略static::$ignoredProperties）
    /**
     * @return array 获取数据模型的字段（只有public属性才可能返回），不包括static::$ignoredProperties
     */
    final public static function getFieldNames()
    {
        if(static::$fieldNames===null)
        {
            $reflectClass = new \ReflectionClass(get_called_class());
            $properties = $reflectClass->getProperties(\ReflectionProperty::IS_PUBLIC);
            $fieldNames = array();
            foreach ($properties as $property) {
                if($property->isStatic()===false) {
                    if(static::$ignoredProperties && in_array($property->name, static::$ignoredProperties))
                        continue;
                    $fieldNames[] = $property->name;
                }
            }
            static::$fieldNames = $fieldNames;
        }
        return static::$fieldNames;
    }

    // 内部属性
    private $isFromDB; //从DB中查询得到的，不是外部new出来的
    private $rawData; //原始数据数组

    /**
     * 设置ID，会根据主键类型自动作类型转换
     * @param $id
     */
    public function setId($id)
    {
        switch (static::$primaryKeyType) {
            case Model::PrimaryKeyTypeString: {
                if (is_string($id)===false)
                    $id = (string)$id;
                break;
            }
            case Model::PrimaryKeyTypeInt: {
                if (is_int($id)===false)
                    $id = (int)$id;
                break;
            }
            case Model::PrimaryKeyTypeFloat: {
                if (is_float($id)===false)
                    $id = (float)$id;
                break;
            }
            default:
                break;
        }
        $pk = static::$primaryKey;
        $this->$pk = $id;
    }

    private function loadFromDBRawData(Array $dbRawData) //只在内部使用，$rawData是从数据库里面读出来的原始数据
    {
        if (count($dbRawData)===0)
            return;
        foreach (self::getFieldNames() as $key) {
            $this->$key = @$dbRawData[$key];
        }
        $this->rawData = $dbRawData;
    }

    /**
     * 从外部提供的关联数组中加载数据
     * 只加载self::getFieldNames()返回的那些字段，并且不包括参数$excludeFields所包含的字段
     * @param array $fieldValues 包含字段值的关联数组
     * @param array|null $excludeFields 要排除的字段。例：['password','phone']
     */
    public function loadFromFieldValues(array $fieldValues, array $excludeFields=null)
    {
        if (count($fieldValues)===0)
            return;
        $fieldNames = self::getFieldNames();
        if ($excludeFields!==null) {
            if(is_array($excludeFields)===false)
                $excludeFields = null;
            else
                $fieldNames = array_diff($fieldNames, $excludeFields);
        }
        foreach ($fieldNames as $key) {
            if(isset($fieldValues[$key])) {
                $this->$key = $fieldValues[$key];
            }
        }
    }

    /**
     * 保存指定的字段值。
     * ID字段必须要有效值，否则不能保存
     * @param array $fieldNames
     * @return int 受影响的行数。0或1
     * @throws \Exception
     */
    public function saveByFields(array $fieldNames)
    {
        $pk = static::$primaryKey;
        $id = @$this->$pk;
        if($id===null) //没有设置ID，无法保存
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有设置主键，无法保存");;

        if(in_array($pk, $fieldNames)) // 提供的字段列表中包含主键，自动剔除
            $fieldNames = array_diff($fieldNames, [$pk]);
        $values = $this->getFieldValues($fieldNames);
        if(count($values)===0) // 提供了非法字段
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 提供了非法字段，无法保存");;

        return static::getTable()->update($id, $values);
    }

    /**
     * 保存有效的字段值（值非null）
     * 主键必须有值，否则无法保存
     * @return int 返回更新的行数
     * @throws \Exception
     */
    public function saveValidFieldValues()
    {
        $values = $this->getValidFieldValues();
        $pk = static::$primaryKey;
        unset($values[$pk]);
        if(count($values)===0) //没有有效字段，无需保存
            return 0;
        $id = @$this->$pk;
        if($id===null)//主键没有值，无法保存
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有设置主键的值，无法保存");;
        return static::getTable()->update($id, $values);
    }

    public function saveAllFieldValues() //保存所有字段，值为null的字段也保存（完全覆盖）
    {
        $values = $this->getAllFieldValues();
        $pk = static::$primaryKey;
        unset($values[$pk]);
        if(count($values)===0) //没有有效字段，无需保存
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 这是一个无效的Model，没有需要保存的字段");
        $id = @$this->$pk;
        if($id===null)//主键没有值，无法保存
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有设置主键的值，无法保存");;
        return static::getTable()->update($id, $values);
    }

    /**
     * 保存修改过的字段值（修改是相对于从数据库里面读出来的值）
     * 如果没有任何修改，则什么也不作，也不抛出异常。
     * @return int 受影响的行数。0或1
     * @throws \Exception
     */
    public function saveModifiedFieldValues()
    {
        $values = $this->getModifiedFieldValues();
        $pk = static::$primaryKey;
        if(isset($values[$pk])) //主键的值被修改
        {
            if($this->isFromDB) //Model来自数据库，主键的值被修改，是不能保存的
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): Model来自数据库，主键的值被修改，不能保存（应该创建新的Model对象再保存）");
            unset($values[$pk]);
        }

        if(count($values)===0) //没有修改的字段，无需保存
            return 0;

        $id = @$this->$pk;
        if($id===null)//主键没有值，无法保存
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有设置主键的值，无法保存");;
        return static::getTable()->update($id, $values);
    }

    /**
     * @return string lastInsertId
     * @throws \Exception
     */
    public function insert() //向数据库插入一条新记录
    {
        $fields = $this->getValidFieldValues(); //获取有效键值对
        if (count($fields)===0)
            throw new \Exception(get_class($this) . "对象属性全为null");

        $id = static::getTable()->insert($fields);
        switch (static::$primaryKeyType) {
            case Model::PrimaryKeyTypeInt:
                $id = (int)$id;break;
            case Model::PrimaryKeyTypeFloat:
                $id = (float)$id;break;
            default:
                break;
        }
        $pk = static::$primaryKey;
        $this->$pk = $id;
        return $id;
    }

    public function delete() //从数据库中删除
    {
        $pk = static::$primaryKey;
        $id = $this->$pk;
        if($id===null)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有ID，无法删除");
        return static::getTable()->deleteByField($pk, $id);
    }

    public static function deleteById($id)
    {
        $pk = static::$primaryKey;
        if($id===null)
            throw new \Exception(get_called_class() . '::' . __FUNCTION__ . "(): ID不可为null");
        return static::getTable()->deleteByField($pk, $id);
    }

    public static function deleteByField($field, $value)
    {
        return static::getTable()->deleteByField($field, $value);
    }

    public static function deleteByFields($fields)
    {
        return static::getTable()->deleteByFields($fields);
    }

    public static function deleteByCondition($condition)
    {
        return static::getTable()->deleteByCondition($condition);
    }

    public static function findById($id)
    {
        $pk = static::$primaryKey;
        if($id===null)
            throw new \Exception(get_called_class() . '::' . __FUNCTION__ . "(): ID不可为null");
        $rawData = static::getTable()->findByField($pk, $id);
        if($rawData===null)
            return static::getNullObject();
        $model = new static;
        $model->loadFromDBRawData($rawData);
        $model->isFromDB = true;
        return $model;
    }

    public static function findByField($field, $value)
    {
        $rawData = static::getTable()->findByField($field, $value);
        if($rawData===null)
            return static::getNullObject();
        $model = new static;
        $model->loadFromDBRawData($rawData);
        $model->isFromDB = true;
        return $model;
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...];
     * @return static
     * @throws \Exception
     */
    public static function findByFields($fields)
    {
        $rawData = static::getTable()->findByFields($fields);
        if($rawData===null)
            return static::getNullObject();
        $model = new static;
        $model->loadFromDBRawData($rawData);
        $model->isFromDB = true;
        return $model;
    }

    /**
     * @param MysqlQuery $query
     * @return static[]
     */
    public static function findAllWithQuery(MysqlQuery $query)
    {
        $rawDatas = static::getTable()->findAllWithQuery($query);
        $models = [];
        foreach ($rawDatas as $rawData) {
            $model = new static;
            $model->loadFromDBRawData($rawData);
            $model->isFromDB = true;
            $models[] = $model;
        }
        return $models;
    }

    /**
     * 获取指定的字段值，不存在的字段自动设置为null
     * @param $fieldNames array 要获取的字段的名字的数组
     * @return array
     */
    public function getFieldValues(array $fieldNames) //
    {
        if(count($fieldNames)==0)
            return [];
        $values = array();
        $allowedFieldName = self::getFieldNames();
        foreach ($fieldNames as $fieldName) {
            if(in_array($fieldName, $allowedFieldName)==false)
                continue;
            $values[$fieldName] = @$this->$fieldName;
        }
        return $values;
    }

    /**
     * 获取所有的字段值，不存在的字段自动设置为null
     * @return array
     */
    public function getAllFieldValues()
    {
        $fields = array();
        foreach (self::getFieldNames() as $fieldName) {
            $fields[$fieldName] = @$this->$fieldName;
        }
        return $fields;
    }

    /**
     *  获取有效的字段值（值非null的字段）
     * @return array
     */
    public function getValidFieldValues()
    {
        $values = array();
        foreach (self::getFieldNames() as $fieldName) {
            $value = @$this->$fieldName;
            if($value!==null)
                $values[$fieldName] = $value;
        }
        return $values;
    }

    /**
     * 获取被修改过的字段值
     * 修改是相对于从数据库里面读出来的值
     * @return array
     */
    protected function getModifiedFieldValues() //获取被修改过的fields
    {
        $values = array();
        if (count($this->rawData) > 0)
            $rawData = $this->rawData;
        else
            $rawData = null;
        foreach (self::getFieldNames() as $fieldName) {
            if ($rawData !== null) //有原始数据
            {
                if (isset($this->$fieldName)) {
                    if (isset($rawData[$fieldName])) {
                        if ($this->$fieldName === $rawData[$fieldName]) //没修改
                            continue;
                    }
                } else {
                    if (isset($rawData[$fieldName]) === false) // 没修改
                        continue;
                }
            } else //没有原始数据
            {
                if (isset($this->$fieldName) === false)
                    continue;
            }
            $values[$fieldName] = @$this->$fieldName;
        }
        return $values;
    }

    public function __toString()
    {
        return (string)($this->getValidFieldValues());
    }

//    function markNew() {
//        ModelRegistry::addNew( $this );
//    }
//
//    function markDeleted() {
//        ModelRegistry::addDelete( $this );
//    }
//
//    function markDirty() {
//        ModelRegistry::addDirty( $this );
//    }
//
//    function markClean() {
//        ModelRegistry::addClean( $this );
//    }
}

class ModelRegistry
{
//    private $all = array();
//    private $dirty = array();
//    private $new = array();
//    private $delete = array();
//
//    private function __construct() { }
//
//    private static $instance; //单例对象
//    public static function instance() {
//        if ( ! self::$instance ) {
//            self::$instance = new ModelRegistry();
//        }
//        return self::$instance;
//    }
//
//    // 辅助方法，生成对象的全局唯一Key
//    public static function globalKey( Model $obj ) {
//        $key = get_class( $obj ).".".$obj->getIdentifier();
//        return $key;
//    }
//
//    // 注册（记录）对象
//    public static function register( Model $obj ) {
//        $inst = self::instance();
//        $inst->all[$inst->globalKey( $obj )] = $obj;
//    }
//
//    // 检测指定ID的对象是否存在
//    public static function exists( $classname, $id ) {
//        $inst = self::instance();
//        $key = "$classname.$id";
//        if ( isset( $inst->all[$key] ) ) {
//            return $inst->all[$key];
//        }
//        return null;
//    }
//
//    public static function addDelete( Model $obj ) {
//        $self = self::instance();
//        $self->delete[$self->globalKey( $obj )] = $obj;
//    }
//
//
//    public static function addDirty( Model $obj ) {
//        $inst = self::instance();
//        if ( ! in_array( $obj, $inst->new, true ) ) {
//            $inst->dirty[$inst->globalKey( $obj )] = $obj;
//        }
//    }
//
//    public static function addNew( Model $obj ) {
//        $inst = self::instance();
//        $inst->new[] = $obj;
//    }
//
//    public static function addClean(Model $obj ) {
//        $inst = self::instance();
//        unset( $inst->delete[$inst->globalKey( $obj )] );
//        unset( $inst->dirty[$inst->globalKey( $obj )] );
//
//        $inst->new = array_filter( $inst->new,
//            function( $a ) use ( $obj ) { return !( $a === $obj ); }
//        );
//    }
//
//    public function performOperations() {
////        foreach ( $this->dirty as $key=>$obj ) {
////            $obj->finder()->update( $obj );
////        }
////        foreach ( $this->new as $key=>$obj ) {
////            $obj->finder()->insert( $obj );
////        }
//        $this->dirty = array();
//        $this->new = array();
//    }
}
