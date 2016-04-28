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
    const FieldTypeUnknown = 0; //未指定数据类型
    const FieldTypeString = 1; //字符串. 默认值为空串""
    const FieldTypeInt32 = 2; //32位整数. 默认值为0
    const FieldTypeFloat = 3; //32位浮点数. 默认值为0
    const FieldTypeDouble = 4; //64位浮点数. 默认值为0
    const FieldTypeBool = 5; //Bool. 默认值为false
    const FieldTypeList = 6; //数组. 默认值为null. (java的List, php的普通数组, js的Array, objc的NSArray)
    const FieldTypeMap = 7; //映射(键值对集合). 默认值为null. (java的Map, php的关联数据, js的对象, objc里的NSDictionary)
    const FieldTypeChars = 8; //定长字符串(不建议使用)，相当Mysql的CHAR(n). 默认值为空串""。最大255
//    const FieldTypeLongText = 9; //长文本(不建议使用)，不支持索引，相当Mysql的longtext，最大2^32-1

    use TraitNullObject;

    //以下是类唯一属性。以延迟静态绑定（static::$xxx）的方式访问，子类可以覆盖这些属性
    protected static $primaryKey = 'id'; //主键，默认'id'
    protected static $ignoredProperties; // self::getFieldNames()会自动忽略的属性列表。默认null
    protected static $fieldNames; // 所有（要存入数据库的）字段名，会自动生成（忽略static::$ignoredProperties）
    protected static $fieldTypes = ['' => self::FieldTypeInt32, ]; //字段类型的关联数组. 格式: [fieldName=>fieldType, ...]
    protected static $uniqueIndices = null; //唯一索引. 例: ['fid', 'uid']

    /**
     * 获取主键
     * @return string
     */
    final public static function getPrimaryKey()
    {
        return static::$primaryKey;
    }

    /**
     * 设置ID，会根据主键类型自动作类型转换
     * @param $id
     * @throws \Exception
     */
    public function setId($id)
    {
        $pk = static::$primaryKey;
        if($pk==null){
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 该模型没有主键");
        }

        switch ($this->getFieldType($pk)) {
            case self::FieldTypeInt32: {
                if (is_int($id)===false)
                    $id = (int)$id;
                break;
            }
            case self::FieldTypeFloat: {
                if (is_float($id)===false)
                    $id = (float)$id;
                break;
            }
            case self::FieldTypeDouble: {
                if (is_double($id)===false)
                    $id = (double)$id;
                break;
            }
            case self::FieldTypeString: {
                if (is_string($id)===false)
                    $id = (string)$id;
                break;
            }
            default: //其它类型一律当字符串处理
            {
                if (is_string($id)===false)
                    $id = (string)$id;
                break;
            }
        }
        $this->$pk = $id;
    }

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

    /**
     * 根据字段名得其数据类型
     * @param $fieldName string 字段名
     * @return int
     */
    final public static function getFieldType($fieldName)
    {
        if(isset(static::$fieldTypes[$fieldName])) //设置了类型
            return (int)static::$fieldTypes[$fieldName];
        else {
            if($fieldName==static::$primaryKey)
                return self::FieldTypeInt32;
            return self::FieldTypeUnknown;
        }
    }

    /**
     * 校正字段值的数据类型
     * @param $fieldName string 字段名
     * @param $value mixed 字段值
     * @return mixed | null 数据类型校正后的字段值
     */
    private static function correctFieldValue($fieldName, $value)
    {
        if($value===null)
            return null;
        $type = (int)@static::$fieldTypes[$fieldName];
        switch ($type) {
            case self::FieldTypeString:
            case self::FieldTypeChars:
                if(is_string($value)===false)
                    $value = (string)$value;
                break;
            case self::FieldTypeInt32:
                if(is_int($value)===false)
                    $value = (int)$value;
                break;
            case self::FieldTypeFloat:
                if(is_float($value)===false)
                    $value = (float)$value;
                break;
            case self::FieldTypeDouble:
                if(is_double($value)===false)
                    $value = (double)$value;
                break;
            case self::FieldTypeBool:
                if(is_bool($value)===false) {
                    if(is_string($value)) {
                        if(trim($value)==='true')
                            $value = true;
                        else
                            $value = false;
                    }
                    else
                        $value = (bool)$value;
                }
                break;
            case self::FieldTypeList:
                $t = gettype($value);
                if($t==='string') {
                    $value = @json_decode($value, true);
                    if($value===null)
                        break;
                    $t = gettype($value);
                }

                if ($t==='array') {
                    $isAssoc = false;
                    foreach ($value as $key => $_) {
                        if(is_int($key)===false) //不是普通数组
                        {
                            $isAssoc = true;
                            break;
                        }
                    }
                    if($isAssoc) //关联数组转普通数组
                        $value = array_values($value);
                    if(count($value)===0) {
                        $value = null;
                        break;
                    }
                }
                else
                    $value = null;
                break;
            case self::FieldTypeMap:
                $t = gettype($value);
                if($t==='string') {
                    $value = @json_decode($value, true);
                    if($value===null)
                        break;
                    $t = gettype($value);
                }

                if ($t==='array') {
                    if(count($value)===0) {
                        $value = null;
                        break;
                    }
                    $isNormalArray = false;
                    foreach ($value as $key => $_) {
                        if(is_int($key)) //是普通数组
                        {
                            $isNormalArray = true;
                            break;
                        }
                    }
                    if($isNormalArray)
                        $value = null;
                }
                elseif ($t==='object') {
                    $value = (array)$value;
                    if(count($value)===0)
                        $value = null;
                }
                else
                    $value = null;
                break;
            default:
                break;
        }
        return $value;
    }

    // 内部属性
    private $isFromDB; //从DB中查询得到的，不是外部new出来的
    private $rawData; //原始数据数组

    /**
     * 从数据库中读取的关联数组中*增量*加载数据（会自动转换数据类型）
     * **只加载 $dbRawData 中存在的键值对**
     * @param array $dbRawData 包含字段值的关联数组
     */
    private function loadFromDBRawData(Array $dbRawData) //只在内部使用，$rawData是从数据库里面读出来的原始数据
    {
        if (count($dbRawData)===0)
            return;

        foreach (self::getFieldNames() as $key) {
            $value = @$dbRawData[$key];
            if($value===null)
                continue;
            $this->$key = self::correctFieldValue($key, $value);
        }
        $this->rawData = $dbRawData;
    }

    /**
     * 从外部提供的关联数组中*增量*加载数据（会自动转换数据类型）
     * **只加载 $fieldValues 中存在的键值对**
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

        foreach ($fieldValues as $key => $value) {
            if(in_array($key, $fieldNames)===false) //非法的键值对, 忽略
                continue;
            if ($value === null)
                continue;
            $this->$key = self::correctFieldValue($key, $value);
        }
    }

    /**
     * 从外部提供的关联数组中*覆盖式*加载数据（会自动转换数据类型）
     * 对于 $fieldValues 中不存在的键, 按null进行重置
     * 只加载self::getFieldNames()返回的那些字段，并且不包括参数$excludeFields所包含的字段
     * @param array $fieldValues 包含字段值的关联数组
     */
    public function reloadFromFieldValues(array $fieldValues)
    {
        $fieldNames = self::getFieldNames();

        foreach ($fieldNames as $key) {
            $value = @$fieldValues[$key];
            if ($value === null)
                $this->$key = null;
            else
                $this->$key = self::correctFieldValue($key, $value);
        }
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

//    protected static $table; //延迟静态绑定（类唯一）
    protected static $tables = []; //延迟静态绑定不能正常工作, 原因未知. 所以只能用其它方法

    /**
     * @return Table
     */
    protected static function getTable() //子类可以重载此方法，连接非默认的数据库
    {
        $className = get_called_class();
        if (isset($tables[$className])==false) {
            $tables[$className] = new Table(Database::getDefaultDB(), get_called_class());
        }
        return $tables[$className];
    }

    /**
     * 保存指定的字段值。
     * ID字段必须要有效值，否则不能保存
     * @param array $fieldNames
     * @return int 受影响的行数。0或1
     * @throws \Exception
     */
    public function saveByFieldNames(array $fieldNames)
    {
        $pk = static::$primaryKey;
        if($pk==null) //没有主键（不是主键没有值）
        {
            if (static::$uniqueIndices === null) // 也没有唯一索引
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有主键，也没有唯一索引，无法保存");
            $wheres = $this->getFieldValues(static::$uniqueIndices);
            foreach (static::$uniqueIndices as $index) {
                if (in_array($index, $fieldNames)) // 提供的字段列表中包含唯一索引，自动剔除
                    $fieldNames = array_diff($fieldNames, [$index]);
            }
            $values = $this->getFieldValues($fieldNames);
            if (count($values) === 0) // 没有提供有效字段
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有提供有效字段，无法保存");

            if (in_array('updateTime', self::getFieldNames()))
                $values['updateTime'] = time();
            return static::getTable()->updateWhere($wheres, $values);
        }
        else // 有主键的情况
        {
            $id = @$this->$pk;
            if ($id === null) //没有设置ID，无法保存
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 主键没有值，无法保存");;

            if (in_array($pk, $fieldNames)) // 提供的字段列表中包含主键，自动剔除
                $fieldNames = array_diff($fieldNames, [$pk]);
            $values = $this->getFieldValues($fieldNames);
            if (count($values) === 0) // 没有提供有效字段
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有提供有效字段，无法保存");;

            if (in_array('updateTime', self::getFieldNames()))
                $values['updateTime'] = time();
            return static::getTable()->update($pk, $id, $values);
        }
    }

    /**
     * 保存有效的字段值（值非null）
     * 主键必须有值，否则无法保存
     * @return int 返回更新的行数
     * @throws \Exception
     */
    public function saveValidFieldValues()
    {
        $pk = static::$primaryKey;
        if($pk==null) //没有主键（不是主键没有值）
        {
            if (static::$uniqueIndices === null) // 也没有唯一索引
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有主键，也没有唯一索引，无法保存");
            $wheres = $this->getFieldValues(static::$uniqueIndices);
            $values = $this->getValidFieldValues();
            foreach (static::$uniqueIndices as $index) {
                unset($values[$index]);
            }
            if (count($values) === 0) //没有有效字段，无需保存
                return 0;

            if (in_array('updateTime', self::getFieldNames()))
                $values['updateTime'] = time();
            return static::getTable()->updateWhere($wheres, $values);
        }
        else // 有主键的情况
        {
            $values = $this->getValidFieldValues();
            unset($values[$pk]);
            if (count($values) === 0) //没有有效字段，无需保存
                return 0;
            $id = @$this->$pk;
            if ($id === null)//主键没有值，无法保存
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有设置主键的值，无法保存");;
            if (in_array('updateTime', self::getFieldNames()))
                $values['updateTime'] = time();
            return static::getTable()->update($pk, $id, $values);
        }
    }

    public function saveAllFieldValues() //保存所有字段，值为null的字段也保存（完全覆盖）
    {
        $pk = static::$primaryKey;
        if($pk==null) //没有主键（不是主键没有值）
        {
            if (static::$uniqueIndices === null) // 也没有唯一索引
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有主键，也没有唯一索引，无法保存");
            $wheres = $this->getFieldValues(static::$uniqueIndices);
            $values = $this->getAllFieldValues();
            foreach (static::$uniqueIndices as $index) {
                unset($values[$index]);
            }
            if (count($values) === 0) //没有有效字段，无需保存
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有提供有效字段，无法保存");

            if (in_array('updateTime', self::getFieldNames()))
                $values['updateTime'] = time();
            return static::getTable()->updateWhere($wheres, $values);
        }
        else // 有主键的情况
        {
            $values = $this->getAllFieldValues();
            unset($values[$pk]);
            if (count($values) === 0) //没有有效字段，无需保存
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 这是一个无效的Model，没有需要保存的字段");
            $id = @$this->$pk;
            if ($id === null)//主键没有值，无法保存
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有设置主键的值，无法保存");;
            if (in_array('updateTime', self::getFieldNames()))
                $values['updateTime'] = time();
            return static::getTable()->update($pk, $id, $values);
        }
    }

    /**
     * 保存修改过的字段值（修改是相对于从数据库里面读出来的值）
     * 如果没有任何修改，则什么也不作，也不抛出异常。
     * @return int 受影响的行数。0或1
     * @throws \Exception
     */
    public function saveModifiedFieldValues()
    {
        $pk = static::$primaryKey;
        if($pk==null) //没有主键（不是主键没有值）
        {
            if (static::$uniqueIndices === null) // 也没有唯一索引
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有主键，也没有唯一索引，无法保存");
            $wheres = $this->getFieldValues(static::$uniqueIndices);
            $values = $this->getModifiedFieldValues();
            foreach (static::$uniqueIndices as $index) {
                if (isset($values[$index])) //唯一键的值被修改
                {
                    if ($this->isFromDB) //Model来自数据库，主键的值被修改，是不能保存的
                        throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): Model来自数据库，唯一键的值被修改，不能保存（应该创建新的Model对象再保存）");
                    unset($values[$index]);
                }
            }
            if (count($values) === 0) //没有修改的字段，无需保存
                return 0;

            if (in_array('updateTime', self::getFieldNames()))
                $values['updateTime'] = time();
            return static::getTable()->updateWhere($wheres, $values);
        }
        else // 有主键的情况
        {
            $values = $this->getModifiedFieldValues();
            if (isset($values[$pk])) //主键的值被修改
            {
                if ($this->isFromDB) //Model来自数据库，主键的值被修改，是不能保存的
                    throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): Model来自数据库，主键的值被修改，不能保存（应该创建新的Model对象再保存）");
                unset($values[$pk]);
            }

            if (count($values) === 0) //没有修改的字段，无需保存
                return 0;

            $id = @$this->$pk;
            if ($id === null)//主键没有值，无法保存
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有设置主键的值，无法保存");;
            if (in_array('updateTime', self::getFieldNames()))
                $values['updateTime'] = time();
            return static::getTable()->update($pk, $id, $values);
        }
    }

    /**
     * @return string lastInsertId
     * @throws \Exception
     */
    public function insert() //向数据库插入一条新记录
    {
        $values = $this->getValidFieldValues(); //获取有效键值对
        if (count($values) === 0)
            throw new \Exception(get_class($this) . "对象属性全为null");

        $allFieldNames = self::getFieldNames();
        if (in_array('createTime', $allFieldNames))
            $values['createTime'] = time();
        if (in_array('updateTime', $allFieldNames))
            $values['updateTime'] = time();

        $id = static::getTable()->insert($values);

        $pk = static::$primaryKey;
        switch ($this->getFieldType($pk)) {
            case self::FieldTypeInt32:
                $id = (int)$id;
                break;
            case self::FieldTypeFloat:
                $id = (float)$id;
                break;
            default:
                break;
        }
        if ($pk == null) {
            return $id;
        }
        $this->$pk = $id;
        return $id;
    }

    /**
     * 如果记录不重复, 插入一条新数据; 如果重复, 则替换旧数据
     * @return string lastInsertId
     * @throws \Exception
     */
    public function insertOrReplace() //向数据库插入一条新记录
    {
        $values = $this->getValidFieldValues(); //获取有效键值对
        if (count($values) === 0)
            throw new \Exception(get_class($this) . "对象属性全为null");

        $allFieldNames = self::getFieldNames();
        if (in_array('createTime', $allFieldNames))
            $values['createTime'] = time();
        if (in_array('updateTime', $allFieldNames))
            $values['updateTime'] = time();

        $id = static::getTable()->insertOrReplace($values);

        $pk = static::$primaryKey;
        switch ($this->getFieldType($pk)) {
            case self::FieldTypeInt32:
                $id = (int)$id;
                break;
            case self::FieldTypeFloat:
                $id = (float)$id;
                break;
            default:
                break;
        }
        if($pk==null) {
            return $id;
        }
        $this->$pk = $id;
        return $id;

    }

    public function delete() //从数据库中删除
    {
        $pk = static::$primaryKey;
        if($pk==null) //没有主键（不是主键没有值）
        {
            if (static::$uniqueIndices === null) // 也没有唯一索引
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有主键，也没有唯一索引键，无法保存");
            $wheres = $this->getFieldValues(static::$uniqueIndices);
            return static::getTable()->deleteByFields($wheres);
        }
        else // 有主键的情况
        {
            $id = $this->$pk;
            if ($id === null)
                throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 主键没有值，无法删除");
            return static::getTable()->deleteByField($pk, $id);
        }
    }

    public static function deleteById($id)
    {
        $pk = static::$primaryKey;
        if($pk==null){
            throw new \Exception(get_class(new static) . '::' . __FUNCTION__ . "(): 该模型没有主键");
        }
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

    public static function deleteAll()
    {
        return static::getTable()->deleteAll();
    }

    public static function findById($id)
    {
        $pk = static::$primaryKey;
        if($pk==null){
            throw new \Exception(get_class(new static) . '::' . __FUNCTION__ . "(): 该模型没有主键");
        }

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

    /**
     * 根据ID列表查找所有记录
     * @param $ids array|string ID列表. 如果是数组,形如[1,2,3];如果是字符中,形如'1,2,3'
     * @return static[]
     * @throws \Exception
     */
    public static function findAllByIds($ids)
    {
        $pk = static::$primaryKey;
        if($pk==null){
            throw new \Exception(get_class(new static) . '::' . __FUNCTION__ . "(): 该模型没有主键");
        }

        if(is_string($ids)){
            $array = explode(',', $ids);
            $ids = [];
            foreach ($array as $id) {
                $id = trim($id);
                if(strlen($id))
                    $ids[] = self::correctFieldValue($pk, $id);
            }
        }

        if(count($ids)===0)
            throw new \Exception(get_class(new static) . '::' . __FUNCTION__ . '无效的ID列表');

        $rawDatas = static::getTable()->findAllInFieldValueList($pk, $ids);
        if(count($rawDatas)==0)
            return [];
        $models = [];
        foreach ($rawDatas as $rawData) {
            $model = new static;
            $model->loadFromDBRawData($rawData);
            $model->isFromDB = true;
            $models[] = $model;
        }
        return $models;
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
     * 根据字段查询, 并且对指定字段的值进行增加
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...];
     * @param $increaseField string 要增加的字段名
     * @param $deltaValue int 要增加的数值, 可正可负
     * @return static
     * @throws \Exception
     */
    public static function findAndIncreaseByFields($fields, $increaseField, $deltaValue)
    {
        $rawData = static::getTable()->findAndIncreaseByFields($fields, $increaseField, $deltaValue);
        if($rawData===null)
            return static::getNullObject();
        $model = new static;
        $model->loadFromDBRawData($rawData);
        $model->isFromDB = true;
        return $model;
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...];
     * @return static[]
     * @throws \Exception
     */
    public static function findAllByFields($fields)
    {
        $rawDatas = self::getTable()->findAllByFields($fields);
        if(count($rawDatas)==0)
            return [];//static::getNullObject();
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
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...];
     * @return int
     * @throws \Exception
     */
    public static function countByFields($fields)
    {
        return self::getTable()->countByFields($fields);
    }

    /**
     * @param IQuery $query
     * @return static[]
     */
    public static function findAllWithQuery(IQuery $query)
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

    public function __toString()
    {
        return (string)($this->getValidFieldValues());
    }

    /**
     * @return IQuery
     */
    public static function createQuery()
    {
        return new MysqlQuery();
    }
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
