<?php
/*
 * Project: simpleim-php
 * File: Validation.php
 * CreateTime: 16/11/6 17:22
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Validation.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;


/**
 * @class Validation
 * @package WebGeeker\Rest
 * @brief brief description
 *
 * elaborate description
 */
class Validation
{
    /**
     * @var array 验证失败时的错误提示信息的模板
     *
     * 输入值一般为字符串
     */
    static public $errorTemplates = [
        // 整型（不提供length检测,因为负数的符号位会让人混乱, 可以用大于小于比较来做到这一点）
        'Int' => '“{{param}}”必须是整数',
        'IntGt' => '“{{param}}”必须是整数，并且大于 {{min}}',
        'IntGe' => '“{{param}}”必须是整数，并且大于等于 {{min}}',
        'IntLt' => '“{{param}}”必须是整数，并且小于 {{max}}',
        'IntLe' => '“{{param}}”必须是整数，并且小于等于 {{max}}',
        'IntGtAndLt' => '“{{param}}”必须是整数，取值大于 {{min}} 且小于 {{max}}',
        'IntGeAndLe' => '“{{param}}”必须是整数，取值大于等于 {{min}} 且小于等于 {{max}}',
        'IntGtAndLe' => '“{{param}}”必须是整数，取值大于 {{min}} 且小于等于 {{max}}',
        'IntGeAndLt' => '“{{param}}”必须是整数，取值大于等于 {{min}} 且小于 {{max}}',
        'IntIn' => '“{{param}}”必须是整数，并且只能取这些值: {{valueList}}',
        'IntNotIn' => '“{{param}}”必须是整数，并且不能取这些值: {{valueList}}',

        // 浮点型（内部一律使用double来处理）
        'Float' => '“{{param}}”必须是浮点数',
        'Double' => '“{{param}}”必须是浮点数', // 同float
        'FloatGt' => '“{{param}}”必须是浮点数，并且大于 {{min}}',
        'FloatGe' => '“{{param}}”必须是浮点数，并且大于等于 {{min}}',
        'FloatLt' => '“{{param}}”必须是浮点数，并且小于 {{max}}',
        'FloatLe' => '“{{param}}”必须是浮点数，并且小于等于 {{max}}',
        'FloatGtAndLt' => '“{{param}}”必须是浮点数，取值大于 {{min}} 且小于 {{max}}',
        'FloatGeAndLe' => '“{{param}}”必须是浮点数，取值大于等于 {{min}} 且小于等于 {{max}}',
        'FloatGtAndLe' => '“{{param}}”必须是浮点数，取值大于 {{min}} 且小于等于 {{max}}',
        'FloatGeAndLt' => '“{{param}}”必须是浮点数，取值大于等于 {{min}} 且小于 {{max}}',

        // bool型
        'Bool' => '“{{param}}”必须是bool型(true or false)', // 忽略大小写
        'BoolSmart' => '“{{param}}”只能取这些值: true, false, yes, no, 1, 0（忽略大小写）',

        // 字符串
//        'string' => '',
        'Length' => '“{{param}}”长度必须等于 {{length}}', // 字符串长度
        'LengthGe' => '“{{param}}”长度必须大于等于 {{length}}',
        'LengthLe' => '“{{param}}”长度必须小于等于 {{length}}',
        'LengthGeAndLe' => '“{{param}}”长度必须在 {{lengthMin}} - {{lengthMax}} 之间', // 字符串长度
        'Letters' => '“{{param}}”只能包含字母',
        'Alphabet' => '“{{param}}”只能包含字母', // 同Letters
        'Numbers' => '“{{param}}”只能是纯数字',
        'Digits' => '“{{param}}”只能是纯数字', // 同Numbers
        'LettersAndNumbers' => '“{{param}}”只能包含字母和数字',
        'Numeric' => '“{{param}}”必须是数值', // 一般用于大数处理（超过double表示范围的数,一般会用字符串来表示）, 如果是正常范围内的数, 可以使用'Int'或'Float'来检测
        'VariableName' => '“{{param}}”只能包含字母、数字和下划线，并且以字母或下划线开头',
        'Equals' => '“{{param}}”必须等于 {{value}}',
        'In' => '“{{param}}”只能取这些值: {{valueList}}',
        'NotIn' => '“{{param}}”不能取这些值: {{valueList}}',
        'InIgnoreCase' => '“{{param}}”只能取这些值: {{valueList}}（忽略大小写）',
        'NotInIgnoreCase' => '“{{param}}”不能取这些值: {{valueList}}（忽略大小写）',
        'Regexp' => '“{{param}}”{{reason}}', // Perl正则表达式匹配

        // 文件
        'File' => '“{{param}}”必须是文件',
        'FileSize' => '“{{param}}”必须是文件, 且大小不超过{{size}}',
        'FileExt' => '“{{param}}”必须是.{{ext}}文件',
        'FileImage' => '“{{param}}”必须是图像文件(jpg,png,bmp,gif,ico,tiff)',
        'FileVideo' => '“{{param}}”必须是视频文件(mp4,rm,mov,avi)',
        'FileAudio' => '“{{param}}”必须是音频文件(mp3,wav,aac)',

//        // 关系型（似乎没有存在的必要）
//        'or' => '', // 或关系
        'Required' => '必须提供参数{{param}}',

//        // 其它
        'Array' => '“{{param}}”必须是数组',
        'Object' => '“{{param}}”必须是对象',
        'Date' => '“{{param}}”必须符合日期格式YYYY-MM-DD',
        'Datetime' => '“{{param}}”必须符合日期时间格式YYYY-MM-DD HH:mm:ss',
        'Time' => '“{{param}}”必须符合时间格式HH:mm:ss或HH:mm',
        'Timestamp' => '“{{param}}”不是合法的时间戳',

        // 预处理（只处理字符串类型, 如果是其它类型, 则原值返回）
        'Trim' => '', // 对要检测的值先作一个trim操作, 后续的检测是针对trim后的值进行检测
        'Lowercase' => '', // 将要检测的值转为小写, 后续的检测是针对转换后的值进行检测
        'Uppercase' => '', // 将要检测的值转为大写, 后续的检测是针对转换后的值进行检测
        'ToInt' => '', // 预处理为int型
        'ToString' => '', // 预处理为string型（这个一般用不到）
    ];

    // 所有验证器格式示例
    static private $sampleFormats = [
        // 整型（不提供length检测,因为负数的符号位会让人混乱, 可以用大于小于比较来做到这一点）
        'Int' => 'Int',
        'IntGt' => 'IntGt:100',
        'IntGe' => 'IntGe:100',
        'IntLt' => 'IntLt:100',
        'IntLe' => 'IntLe:100',
        'IntGtAndLt' => 'IntGtAndLt:1,100',
        'IntGeAndLe' => 'IntGeAndLe:1,100',
        'IntGtAndLe' => 'IntGtAndLe:1,100',
        'IntGeAndLt' => 'IntGeAndLt:1,100',
        'IntIn' => 'IntIn:2,3,5,7,11',
        'IntNotIn' => 'IntNotIn:2,3,5,7,11',

        // 浮点型（内部一律使用double来处理）
        'Float' => 'Float',
        'Double' => 'Double', // 同float
        'FloatGt' => 'FloatGt:1.0',
        'FloatGe' => 'FloatGe:1.0',
        'FloatLt' => 'FloatLt:1.0',
        'FloatLe' => 'FloatLe:1.0',
        'FloatGtAndLt' => 'FloatGtAndLt:0,1.0',
        'FloatGeAndLe' => 'FloatGeAndLe:0,1.0',
        'FloatGtAndLe' => 'FloatGtAndLe:0,1.0',
        'FloatGeAndLt' => 'FloatGeAndLt:0,1.0',

        // bool型
        'Bool' => 'Bool', // 忽略大小写
        'BoolSmart' => 'BoolSmart',

        // 字符串
//        'string' => '',
        'Length' => 'Length:8', // 字符串长度
        'LengthGe' => 'LengthGe:8',
        'LengthLe' => 'LengthLe:8',
        'LengthGeAndLe' => 'LengthGeAndLe:6,8', // 字符串长度
        'Letters' => 'Letters',
        'Alphabet' => 'Alphabet', // 同Letters
        'Numbers' => 'Numbers',
        'Digits' => 'Digits', // 同Numbers
        'LettersAndNumbers' => 'LettersAndNumbers',
        'Numeric' => 'Numeric',
        'VariableName' => 'VariableName',
        'Equals' => 'Equals:abc',
        'In' => 'In:abc,def,g',
        'NotIn' => 'NotIn:abc,def,g',
        'InIgnoreCase' => 'InIgnoreCase:abc,def,g',
        'NotInIgnoreCase' => 'NotInIgnoreCase:abc,def,g',
        'Regexp' => 'Regexp:/^abc$/', // Perl正则表达式匹配

        'File' => 'File',
        'FileSize' => 'FileSize:100kb',
        'FileExt' => 'FileExt',
        'FileImage' => 'FileImage',
        'FileVideo' => 'FileVideo',
        'FileAudio' => 'FileAudio',

//        // 关系型（似乎没有存在的必要）
//        'or' => '', // 或关系

//        // 其它
//        'required' => '必须提供 “{{param}}”参数',
        'Array' => 'Array',
        'Object' => 'Object',
        'Date' => 'Date',
        'Datetime' => 'Datetime',
        'Time' => 'Time',
        'Timestamp' => 'Timestamp',

        // 预处理（只处理字符串类型, 如果是其它类型, 则原值返回）
        'Trim' => 'Trim',
        'Lowercase' => 'Lowercase',
        'Uppercase' => 'Uppercase',
        'ToInt' => 'ToInt',
        'ToString' => 'ToString',
    ];

    /**
     * 将一条验证字符串转为验证器数组
     *
     * 例如:
     * 输入: $validation = 'Length:6,16|regex:/^[a-zA-Z0-9]+$/'
     * 输出: [
     *     ['Length', 6, 16,],
     *     ['regex', '/^[a-zA-Z0-9]+$/'],
     * ]
     *
     * @param $validation string 一条验证字符串
     * @return array 返回验证器的数组
     * @throws \Exception
     */
    private static function _parse($validation)
    {
        if (is_string($validation) === false)
            return [];
        if (strlen($validation) === 0)
            return [];

        $validators = [];

        $segments = explode('|', $validation);
        $segCount = count($segments);
        for ($i = 0; $i < $segCount;) {
            $segment = $segments[$i];
            if (stripos($segment, 'Regexp:') === 0) // 是正则表达式
            {
                if (stripos($segment, '/') !== 7) // 非法的正则表达. 合法的必须首尾加/
                    throw new \Exception("正则表达式验证器regexp格式非法. 正确的格式是 Regexp:/xxxx/");

                $pos = 8;
                $len = strlen($segment);

                $finish = false;
                do {
                    $pos2 = strripos($segment, '/'); // 反向查找字符/
                    if ($pos2 !== $len - 1 // 不是以/结尾, 说明正则表达式中包含了|分隔符, 正则表达式被explode拆成了多段
                        || $pos2 === 7
                    ) // 第1个/后面就没字符了, 说明正则表达式中包含了|分隔符, 正则表达式被explode拆成了多段
                    {
                    } else // 以/结尾, 可能是完整的正则表达式, 也可能是不完整的正则表达式
                    {
                        do {
                            $pos = stripos($segment, '\\', $pos); // 从前往后扫描转义符\
                            if ($pos === false) // 结尾的/前面没有转义符\, 正则表达式扫描完毕
                            {
                                $finish = true;
                                break;
                            } else if ($pos === $len - 1) // 不可能, $len-1这个位置是字符/
                            {
                                ;
                            } else if ($pos === $len - 2) // 结尾的/前面有转义符\, 说明/只是正则表达式内容的一部分, 正则表达式尚未结束
                            {
                                $pos += 3; // 跳过“\/|”三个字符
                                break;
                            } else {
                                $pos += 2;
                            }
                        } while (1);

                        if ($finish)
                            break;
                    }

                    $i++;
                    if ($i >= $segCount) // 后面没有segment了
                        throw new \Exception("正则表达式验证器格式错误. 正确的格式是 Regexp:/xxxx/");

                    $segment .= '|';
                    $segment .= $segments[$i]; // 拼接后面一个segment
                    $len = strlen($segment);
                    continue;

                } while (1);

                $validators[] = ['Regexp', substr($segment, 7)];
            } // end if(stripos($segment, 'Regexp:')===0)
            else {
                $pos = stripos($segment, ':');
                if ($pos === false) {
                    if ($segment === 'Required' && count($validators) > 0)
                        throw new \Exception("Required只能出现在验证器的开头");
                    $validators[] = [$segment];
                } else {
                    $validatorName = trim(substr($segment, 0, $pos));
                    $p = trim(substr($segment, $pos + 1));
                    if (strlen($validatorName)===0 || strlen($p) === 0)
                        throw new \Exception("无法识别的验证器“${segment}”");
                    switch ($validatorName) {
                        case 'IntGt':
                        case 'IntGe':
                        case 'IntLt':
                        case 'IntLe':
                            if (self::_isIntOrIntString($p) === false)
                                self::_throwFormatError($validatorName);
                            $validator = [$validatorName, intval($p)];
                            break;
                        case 'IntGtAndLt':
                        case 'IntGeAndLe':
                        case 'IntGtAndLe':
                        case 'IntGeAndLt':
                            $vals = explode(',', $p);
                            if (count($vals) !== 2)
                                self::_throwFormatError($validatorName);
                            $p1 = $vals[0];
                            $p2 = $vals[1];
                            if (self::_isIntOrIntString($p1) === false || self::_isIntOrIntString($p2) === false)
                                self::_throwFormatError($validatorName);
                            $validator = [$validatorName, intval($p1), intval($p2)];
                            break;
                        case 'IntIn':
                        case 'IntNotIn':
                            $ints = self::_parseIntArray($p);
                            if ($ints === false)
                                self::_throwFormatError($validatorName);
                            $validator = [$validatorName, $ints];
                            break;
                        case 'Length':
                        case 'LengthGe':
                        case 'LengthLe':
                            if (self::_isIntOrIntString($p) === false)
                                self::_throwFormatError($validatorName);
                            $validator = [$validatorName, intval($p)];
                            break;
                        case 'LengthGeAndLe':
                            $vals = explode(',', $p);
                            if (count($vals) !== 2)
                                self::_throwFormatError($validatorName);
                            $p1 = $vals[0];
                            $p2 = $vals[1];
                            if (self::_isIntOrIntString($p1) === false || self::_isIntOrIntString($p2) === false)
                                self::_throwFormatError($validatorName);
                            $validator = [$validatorName, intval($p1), intval($p2)];
                            break;
                        case 'equals':
                            $p = trim($p);
                            if(strlen($p) === 0)
                                self::_throwFormatError($validatorName);
                            $validator = [$validatorName, $p];
                            break;
                        case 'In':
                        case 'NotIn':
                        case 'InIgnoreCase':
                        case 'NotInIgnoreCase':
                            $strings = self::_parseStringArray($p);
                            if ($strings === false)
                                self::_throwFormatError($validatorName);
                            $validator = [$validatorName, $strings];
                            break;
                        case 'FloatGt':
                        case 'FloatGe':
                        case 'FloatLt':
                        case 'FloatLe':
                            if (is_numeric($p) === false)
                                self::_throwFormatError($validatorName);
                            $validator = [$validatorName, doubleval($p)];
                            break;
                        case 'FloatGtAndLt':
                        case 'FloatGeAndLe':
                        case 'FloatGtAndLe':
                        case 'FloatGeAndLt':
                            $vals = explode(',', $p);
                            if (count($vals) !== 2)
                                self::_throwFormatError($validatorName);
                            $p1 = $vals[0];
                            $p2 = $vals[1];
                            if (is_numeric($p1) === false || is_numeric($p2) === false)
                                self::_throwFormatError($validatorName);
                            $validator = [$validatorName, doubleval($p1), doubleval($p2)];
                            break;
                        default:
                            throw new \Exception("无法识别的验证器“${segment}”");
                    }
                    $validators[] = $validator;
                }
            }
            $i++;
        }
        return $validators;
    }

    private static function _throwFormatError($validatorName)
    {
        $sampleFormat = @self::$sampleFormats[$validatorName];
        if($sampleFormat === null)
            throw new \Exception("验证器${validatorName}格式错误");
        throw new \Exception("验证器${validatorName}格式错误, 正确的格式是: $sampleFormat");
    }

    private static function _isIntOrIntString($value)
    {
        return (is_numeric($value) && stripos($value, '.') === false);
    }

    /**
     * 将包含int数组的字符串转为int数组
     * @param $value
     * @return int[]|bool 如果是合法的int数组, 并且至少有1个int, 返回int数组; 否则返回false
     */
    private static function _parseIntArray($value)
    {
        $vals = explode(',', $value);
        $ints = [];
        foreach ($vals as $val) {
            if(is_numeric($val) === false || stripos($val, '.') !== false)
                return false; // 检测到了非int
            $ints[] = intval($val);
        }
        if(count($ints)===0)
            return false;
        return $ints;
    }

    /**
     * 将字符串转为字符串数组（逗号分隔）
     * @param $value
     * @return string[]|bool 如果至少有1个有效字符串, 返回字符串数组; 否则返回false
     */
    private static function _parseStringArray($value)
    {
        $vals = explode(',', $value);
        $strings = [];
        foreach ($vals as $val) {
            $val = trim($val);
            if(strlen($val) === 0)
                return false; // 检测到了非int
            $strings[] = $val;
        }
        if(count($strings)===0)
            return false;
        return $strings;
    }

    /**
     * 验证一个值
     * @param $value mixed 要验证的值
     * @param $validation string|string[] 一条验证字符串, 例: 'Length:6,16|regex:/^[a-zA-Z0-9]+$/'; 或多条验证字符串的数组, 多条验证字符串之间是或的关系
     * @param string $alias 要验证的值的别名, 用于在验证不通过时生成提示字符串.
     * @return mixed 返回$value被过滤后的新值
     * @throws \Exception
     */
    public static function validate($value, $validation, $alias = 'Parameter')
    {
        if(is_array($validation)) {
            $validations = $validation;
        } else if(is_string($validation)) {
            $validations = [$validation];
        } else
            throw new \Exception(self::class . '::' . __FUNCTION__ . "(): \$validator必须是字符串或字符串数组");

        $passed = false;
        foreach ($validations as $validation) {

            $validators = self::_parse($validation);

            try {

                if($value === null) //没有提供参数
                {
                    if($validation==='Required' || stripos($validation, 'Required|') === 0) {
                        $error = self::$errorTemplates['Required'];
                        $error = str_replace('{{param}}', $alias, $error);
                        throw new \Exception($error);
                    }
                    else // 没有提供参数默认不检测, 直接通过
                    {
                        $passed = true;
                        continue;
                    }
                }

                foreach ($validators as $validator) {

                    $validatorName = $validator[0];

                    $method = 'validate' . ucfirst($validatorName);

                    if(method_exists(self::class, $method)===false)
                        throw new \Exception("找不到验证器${validatorName}的验证方法");

                    $params = [$value];
                    $paramsCount = count($validator);
                    for ($i = 1; $i < $paramsCount; $i++) {
                        $params[] = $validator[$i];
                    }
                    $params[] = $alias;

                    $value = call_user_func_array([self::class, $method], $params);

                }

                // 多个validation只需要一条验证通过即可
                $passed = true;
                break;
            } catch (\Exception $e) {
                $lastException = $e;
            }
        }
        if($passed)
            return $value;
        if(isset($lastException))
            throw $lastException;
        throw new \Exception("“${alias}”验证失败"); // 这句应该不会执行
    }

    //region validate others

    public static function validateRequired($value, $alias = 'Parameter')
    {
        if($value !== null)
            return $value;
        $error = self::$errorTemplates['Required'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    //endregion

    //region validate integer

    public static function validateInt($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1)
                return $value;
        } elseif ($type === 'integer') {
            return $value;
        }

        $error = self::$errorTemplates['Int'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    public static function validateIntGt($value, $min, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1) {
                $val = intval($value);
                if ($val > $min)
                    return $value;
            }
        } elseif ($type === 'integer') {
            if ($value > $min)
                return $value;
        }

        $error = self::$errorTemplates['IntGt'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        throw new \Exception($error);
    }

    public static function validateIntGe($value, $min, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1) {
                $val = intval($value);
                if ($val >= $min)
                    return $value;
            }
        } elseif ($type === 'integer') {
            if ($value >= $min)
                return $value;
        }

        $error = self::$errorTemplates['IntGe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        throw new \Exception($error);
    }

    public static function validateIntLt($value, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1) {
                $val = intval($value);
                if ($val < $max)
                    return $value;
            }
        } elseif ($type === 'integer') {
            if ($value < $max)
                return $value;
        }

        $error = self::$errorTemplates['IntLt'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateIntLe($value, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1) {
                $val = intval($value);
                if ($val <= $max)
                    return $value;
            }
        } elseif ($type === 'integer') {
            if ($value <= $max)
                return $value;
        }

        $error = self::$errorTemplates['IntLe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateIntGtAndLt($value, $min, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1) {
                $val = intval($value);
                if ($val > $min && $val < $max)
                    return $value;
            }
        } elseif ($type === 'integer') {
            if ($value > $min && $value < $max)
                return $value;
        }

        $error = self::$errorTemplates['IntGtAndLt'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateIntGeAndLe($value, $min, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1) {
                $val = intval($value);
                if ($val >= $min && $val <= $max)
                    return $value;
            }
        } elseif ($type === 'integer') {
            if ($value >= $min && $value <= $max)
                return $value;
        }

        $error = self::$errorTemplates['IntGeAndLe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateIntGtAndLe($value, $min, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1) {
                $val = intval($value);
                if ($val > $min && $val <= $max)
                    return $value;
            }
        } elseif ($type === 'integer') {
            if ($value > $min && $value <= $max)
                return $value;
        }

        $error = self::$errorTemplates['IntGtAndLe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateIntGeAndLt($value, $min, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1) {
                $val = intval($value);
                if ($val >= $min && $val < $max)
                    return $value;
            }
        } elseif ($type === 'integer') {
            if ($value >= $min && $value < $max)
                return $value;
        }

        $error = self::$errorTemplates['IntGeAndLt'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    /**
     * 验证IntIn: “{{param}}”只能取这些值: {{valueList}}
     * IntIn与in的区别:
     * 0123 -> IntIn:123 通过; 0123 -> In:123 不通过
     * @param $value string|int 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @param $valueList string[] 可取值的列表
     * @return string
     * @throws \Exception
     */
    public static function validateIntIn($value, $valueList, $alias = 'Parameter')
    {
        if (is_array($valueList) === false || count($valueList) === 0)
            throw new \Exception("“${alias}”参数的验证模版(IntIn:)格式错误, 必须提供可取值的列表");

        $type = gettype($value);
        if ($type === 'string') {
            if (is_numeric($value) && stripos($value, '.') === false) // 是数字并且没有小数点
            {
                $intValue = intval($value);
                if (in_array($intValue, $valueList, true))
                    return $value;
            }
        } else if ($type === 'integer') {
            if (in_array($value, $valueList, true))
                return $value;
        }

        $error = self::$errorTemplates['IntIn'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{valueList}}', implode(', ', $valueList), $error);
        throw new \Exception($error);
    }

    /**
     * 验证intNotIn: “{{param}}”不能取这些值: {{valueList}}
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @param $valueList array 不可取的值的列表
     * @return mixed
     * @throws \Exception
     */
    public static function validateIntNotIn($value, $valueList, $alias = 'Parameter')
    {
        if (is_array($valueList) === false || count($valueList) === 0)
            throw new \Exception("“${alias}”参数的验证模版(intNotIn:)格式错误, 必须提供可取值的列表");

        $type = gettype($value);
        if ($type === 'string') {
            if (is_numeric($value) && stripos($value, '.') === false) // 是数字并且没有小数点
            {
                $intValue = intval($value);
                if (in_array($intValue, $valueList, true) === false)
                    return $value;
            }
        } else if ($type === 'integer') {
            if (in_array($value, $valueList, true) === false)
                return $value;
        }

        $error = self::$errorTemplates['IntNotIn'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{valueList}}', implode(', ', $valueList), $error);
        throw new \Exception($error);
    }

    //endregion

    //region float

    public static function validateFloat($value, $alias = 'Parameter')
    {
        if (is_numeric($value))
            return $value;
        $error = self::$errorTemplates['Float'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGt($value, $min, $alias = 'Parameter')
    {
        if (is_numeric($value)) {
            $f = floatval($value);
            if ($f > $min)
                return $value;
        }
        $error = self::$errorTemplates['FloatGt'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGe($value, $min, $alias = 'Parameter')
    {
        if (is_numeric($value)) {
            $f = floatval($value);
            if ($f >= $min)
                return $value;
        }
        $error = self::$errorTemplates['FloatGe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        throw new \Exception($error);
    }

    public static function validateFloatLt($value, $max, $alias = 'Parameter')
    {
        if (is_numeric($value)) {
            $f = floatval($value);
            if ($f < $max)
                return $value;
        }
        $error = self::$errorTemplates['FloatLt'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatLe($value, $max, $alias = 'Parameter')
    {
        if (is_numeric($value)) {
            $f = floatval($value);
            if ($f <= $max)
                return $value;
        }
        $error = self::$errorTemplates['FloatLe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGtAndLt($value, $min, $max, $alias = 'Parameter')
    {
        if (is_numeric($value)) {
            $f = floatval($value);
            if ($f > $min && $f < $max)
                return $value;
        }
        $error = self::$errorTemplates['FloatGtAndLt'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGeAndLe($value, $min, $max, $alias = 'Parameter')
    {
        if (is_numeric($value)) {
            $f = floatval($value);
            if ($f >= $min && $f <= $max)
                return $value;
        }
        $error = self::$errorTemplates['FloatGeAndLe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGtAndLe($value, $min, $max, $alias = 'Parameter')
    {
        if (is_numeric($value)) {
            $f = floatval($value);
            if ($f > $min && $f <= $max)
                return $value;
        }
        $error = self::$errorTemplates['FloatGtAndLe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGeAndLt($value, $min, $max, $alias = 'Parameter')
    {
        if (is_numeric($value)) {
            $f = floatval($value);
            if ($f >= $min && $f < $max)
                return $value;
        }
        $error = self::$errorTemplates['FloatGeAndLt'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    //endregion

    //region string

    public static function validateLength($value, $length, $alias = 'Parameter')
    {
        $type = gettype($value);

        if ($type === 'string') {
            if (strlen($value) === $length) {
                return $value;
            }
        }

        $error = self::$errorTemplates['Length'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{length}}', $length, $error);
        throw new \Exception($error);
    }

    public static function validateLengthGe($value, $length, $alias = 'Parameter')
    {
        $type = gettype($value);

        if ($type === 'string') {
            if (strlen($value) >= $length) {
                return $value;
            }
        }

        $error = self::$errorTemplates['LengthGe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{length}}', $length, $error);
        throw new \Exception($error);
    }

    public static function validateLengthLe($value, $length, $alias = 'Parameter')
    {
        $type = gettype($value);

        if ($type === 'string') {
            if (strlen($value) <= $length) {
                return $value;
            }
        }

        $error = self::$errorTemplates['LengthLe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{length}}', $length, $error);
        throw new \Exception($error);
    }

    public static function validateLengthGeAndLe($value, $lengthMin, $lengthMax, $alias = 'Parameter')
    {
        if ($lengthMin > $lengthMax)
            throw new \Exception("“${alias}”参数的验证模版lengthGeAndLe格式错误, lengthMin不应该大于lengthMax");

        $type = gettype($value);

        if ($type === 'string') {
            $len = strlen($value);
            if ($len >= $lengthMin && $len <= $lengthMax) {
                return $value;
            }
        }

        $error = self::$errorTemplates['LengthGeAndLe'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{lengthMin}}', $lengthMin, $error);
        $error = str_replace('{{lengthMax}}', $lengthMax, $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能包含字母
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @return mixed
     * @throws \Exception
     */
    public static function validateLetters($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^[a-zA-Z]+$/', $value) === 1)
                return $value;
        }

        $error = self::$errorTemplates['Letters'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能包含字母
     * 同Letters
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @return mixed
     * @throws \Exception
     */
    public static function validateAlphabet($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^[a-zA-Z]+$/', $value) === 1)
                return $value;
        }

        $error = self::$errorTemplates['Alphabet'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能是纯数字
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @return mixed
     * @throws \Exception
     */
    public static function validateNumbers($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^[0-9]+$/', $value) === 1)
                return $value;
        }

        $error = self::$errorTemplates['Numbers'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能是纯数字
     * 同Numbers
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @return mixed
     * @throws \Exception
     */
    public static function validateDigits($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^[0-9]+$/', $value) === 1)
                return $value;
        }

        $error = self::$errorTemplates['Digits'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能包含字母和数字
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @return mixed
     * @throws \Exception
     */
    public static function validateLettersAndNumbers($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^[a-zA-Z0-9]+$/', $value) === 1)
                return $value;
        }

        $error = self::$errorTemplates['LettersAndNumbers'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”必须是数值
     * 一般用于大数处理（超过double表示范围的数,一般会用字符串来表示）
     * 如果是正常范围内的数, 可以使用'Int'或'Float'来检测
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @return mixed
     * @throws \Exception
     */
    public static function validateNumeric($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9.]+$/', $value) === 1) {
                $count = 0;
                str_replace('.', '.', $value, $count);
                if ($count <= 1)
                    return $value;
            }
        }

        $error = self::$errorTemplates['Numeric'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能包含字母、数字和下划线，并且以字母或下划线开头
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @return mixed
     * @throws \Exception
     */
    public static function validateVariableName($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', $value) === 1)
                return $value;
        }

        $error = self::$errorTemplates['VariableName'];
        $error = str_replace('{{param}}', $alias, $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”必须等于 {{equalsValue}}
     * @param $value string 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @param $equalsValue string 可取值的列表
     * @return mixed
     * @throws \Exception
     */
    public static function validateEquals($value, $equalsValue, $alias = 'Parameter')
    {
        if (is_string($value) && $value === $equalsValue)
            return $value;

        $error = self::$errorTemplates['Equals'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{value}}', $equalsValue, $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能取这些值: {{valueList}}
     * @param $value string 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @param $valueList string[] 可取值的列表
     * @return string
     * @throws \Exception
     */
    public static function validateIn($value, $valueList, $alias = 'Parameter')
    {
        if (is_array($valueList) === false || count($valueList) === 0)
            throw new \Exception("“${alias}”参数的验证模版(In:)格式错误, 必须提供可取值的列表");

        if (in_array($value, $valueList, true))
            return $value;

        $error = self::$errorTemplates['In'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{valueList}}', implode(', ', $valueList), $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”不能取这些值: {{valueList}}
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @param $valueList array 不可取的值的列表
     * @return mixed
     * @throws \Exception
     */
    public static function validateNotIn($value, $valueList, $alias = 'Parameter')
    {
        if (is_array($valueList) === false || count($valueList) === 0)
            throw new \Exception("“${alias}”参数的验证模版(NotIn:)格式错误, 必须提供不可取的值的列表");

        if (in_array($value, $valueList, true) === false)
            return $value;

        $error = self::$errorTemplates['NotIn'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{valueList}}', implode(', ', $valueList), $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能取这些值: {{valueList}}（忽略大小写）
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @param $valueList array 可取值的列表
     * @return mixed
     * @throws \Exception
     */
    public static function validateInIgnoreCase($value, $valueList, $alias = 'Parameter')
    {
        if (is_array($valueList) === false || count($valueList) === 0)
            throw new \Exception("“${alias}”参数的验证模版(InIgnoreCase:)格式错误, 必须提供可取值的列表");

        $lowerValue = strtolower($value);
        foreach ($valueList as $v) {
            if (is_string($v) && strtolower($v) === $lowerValue)
                continue;
            goto VeriFailed;
        }
        return $value;

        VeriFailed:
        $error = self::$errorTemplates['InIgnoreCase'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{valueList}}', implode(', ', $valueList), $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”不能取这些值: {{valueList}}（忽略大小写）
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @param $valueList array 不可取的值的列表
     * @return mixed
     * @throws \Exception
     */
    public static function validateNotInIgnoreCase($value, $valueList, $alias = 'Parameter')
    {
        if (is_array($valueList) === false || count($valueList) === 0)
            throw new \Exception("“${alias}”参数的验证模版(NotInIgnoreCase:)格式错误, 必须提供不可取的值的列表");

        $lowerValue = strtolower($value);
        foreach ($valueList as $v) {
            if (is_string($v) && strtolower($v) === $lowerValue)
                continue;
            goto VeriFailed;
        }
        return $value;

        VeriFailed:
        $error = self::$errorTemplates['NotInIgnoreCase'];
        $error = str_replace('{{param}}', $alias, $error);
        $error = str_replace('{{valueList}}', implode(', ', $valueList), $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能取这些值: yes, on, 1, true, y（忽略大小写）
     * @param $value mixed 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @return mixed
     * @throws \Exception
     */
    public static function validateAccepted($value, $alias = 'Parameter')
    {
        return self::validateInIgnoreCase($value, ['yes', 'on', '1', 'true', 'y'], $alias = 'Parameter');
    }

    /**
     * Perl正则表达式验证
     * @param $value string 参数值
     * @param $alias string 参数别名, 用于错误提示
     * @param $regexp string Perl正则表达式. 正则表达式内的特殊字符需要转义（包括/）. 首尾无需加/
     * @param $reason null|string 原因（当不匹配时用于错误提示）. 如果为null, 当不匹配时会提示 “${alias}”不匹配正则表达式$regexp
     * @return mixed
     * @throws \Exception
     */
    public static function validateRegexp($value, $regexp, $reason = null, $alias = 'Parameter')
    {
        if (is_string($regexp) === false || $regexp === '')
            throw new \Exception("“${alias}”参数的验证模版(Regexp:)格式错误, 没有提供正则表达式");

        $result = @preg_match($regexp, $value);
        if ($result === 1)
            return $value;
        else if ($result === false)
            throw new \Exception("“${alias}”参数的正则表达式验证失败, 请检查正则表达式是否合法");

        $error = self::$errorTemplates['Regexp'];
        $error = str_replace('{{param}}', $alias, $error);
        if (!$reason)
            $reason = "不匹配正则表达式“${regexp}”";
        $error = str_replace('{{reason}}', $reason, $error);
        throw new \Exception($error);
    }

    //endregion
}