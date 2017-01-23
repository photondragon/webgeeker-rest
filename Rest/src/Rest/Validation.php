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
        'int' => '“{{param}}”必须是整数',
        'intGt' => '“{{param}}”必须是整数，并且大于 {{min}}',
        'intGe' => '“{{param}}”必须是整数，并且大于等于 {{min}}',
        'intLt' => '“{{param}}”必须是整数，并且小于 {{max}}',
        'intLe' => '“{{param}}”必须是整数，并且小于等于 {{max}}',
        'intGtAndLt' => '“{{param}}”必须是整数，取值大于 {{min}} 且小于 {{max}}',
        'intGeAndLe' => '“{{param}}”必须是整数，取值大于等于 {{min}} 且小于等于 {{max}}',
        'intGtAndLe' => '“{{param}}”必须是整数，取值大于 {{min}} 且小于等于 {{max}}',
        'intGeAndLt' => '“{{param}}”必须是整数，取值大于等于 {{min}} 且小于 {{max}}',
        'intIn' => '“{{param}}”必须是整数，并且只能取这些值: {{valueList}}',
        'intNotIn' => '“{{param}}”必须是整数，并且不能取这些值: {{valueList}}',

        // 浮点型（内部一律使用double来处理）
        'float' => '“{{param}}”必须是浮点数',
        'double' => '“{{param}}”必须是浮点数', // 同float
        'floatGt' => '“{{param}}”必须是浮点数，并且大于 {{min}}',
        'floatGe' => '“{{param}}”必须是浮点数，并且大于等于 {{min}}',
        'floatLt' => '“{{param}}”必须是浮点数，并且小于 {{max}}',
        'floatLe' => '“{{param}}”必须是浮点数，并且小于等于 {{max}}',
        'floatGtAndLt' => '“{{param}}”必须是浮点数，取值大于 {{min}} 且小于 {{max}}',
        'floatGeAndLe' => '“{{param}}”必须是浮点数，取值大于等于 {{min}} 且小于等于 {{max}}',
        'floatGtAndLe' => '“{{param}}”必须是浮点数，取值大于 {{min}} 且小于等于 {{max}}',
        'floatGeAndLt' => '“{{param}}”必须是浮点数，取值大于等于 {{min}} 且小于 {{max}}',

        // bool型
        'bool' => '“{{param}}”必须是bool型(true or false)', // 忽略大小写
        'boolSmart' => '“{{param}}”只能取这些值: true, false, yes, no, 1, 0（忽略大小写）',

        // 字符串
//        'string' => '',
        'length' => '“{{param}}”长度必须等于 {{length}}', // 字符串长度
        'lengthGe' => '“{{param}}”长度必须大于等于 {{length}}',
        'lengthLe' => '“{{param}}”长度必须小于等于 {{length}}',
        'lengthGeAndLe' => '“{{param}}”长度必须在 {{lengthMin}} - {{lengthMax}} 之间', // 字符串长度
        'letters' => '“{{param}}”只能包含字母',
        'alphabet' => '“{{param}}”只能包含字母', // 同letters
        'numbers' => '“{{param}}”只能是纯数字',
        'digits' => '“{{param}}”只能是纯数字', // 同numbers
        'lettersAndNumbers' => '“{{param}}”只能包含字母和数字',
        'numeric' => '“{{param}}”必须是数值', // 一般用于大数处理（超过double表示范围的数,一般会用字符串来表示）, 如果是正常范围内的数, 可以使用'int'或'float'来检测
        'variableName' => '“{{param}}”只能包含字母、数字和下划线，并且以字母或下划线开头',
        'equals' => '“{{param}}”必须等于 {{value}}',
        'in' => '“{{param}}”只能取这些值: {{valueList}}',
        'notIn' => '“{{param}}”不能取这些值: {{valueList}}',
        'inIgnoreCase' => '“{{param}}”只能取这些值: {{valueList}}（忽略大小写）',
        'notInIgnoreCase' => '“{{param}}”不能取这些值: {{valueList}}（忽略大小写）',
        'regexp' => '“{{param}}”{{reason}}', // Perl正则表达式匹配

//        // 关系型（似乎没有存在的必要）
//        'or' => '', // 或关系

//        // 其它
//        'required' => '必须提供 “{{param}}”参数',

        // 预处理（只处理字符串类型, 如果是其它类型, 则原值返回）
        'trim' => '', // 对要检测的值先作一个trim操作, 后续的检测是针对trim后的值进行检测
        'lowercase' => '', // 将要检测的值转为小写, 后续的检测是针对转换后的值进行检测
        'uppercase' => '', // 将要检测的值转为大写, 后续的检测是针对转换后的值进行检测
        'toInt' => '', // 预处理为int型
        'toString' => '', // 预处理为string型（这个一般用不到）
    ];

    //region integer

    public static function validateInt($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+$/', $value) === 1)
                return $value;
        } elseif ($type === 'integer') {
            return $value;
        }

        $error = self::$errorTemplates['int'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['intGt'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['intGe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['intLt'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['intLe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['intGtAndLt'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['intGeAndLe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['intGtAndLe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['intGeAndLt'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    //endregion

    //region float

    public static function validateFloat($value, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+\.?[0-9]*$/', $value) === 1)
                return $value;
        } elseif ($type === 'double') {
            return $value;
        }

        $error = self::$errorTemplates['float'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        throw new \Exception($error);
    }

    public static function validateFloatGt($value, $min, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+\.?[0-9]*$/', $value) === 1) {
                $val = doubleval($value);
                if ($val > $min)
                    return $value;
            }
        } elseif ($type === 'double') {
            if ($value > $min)
                return $value;
        }

        $error = self::$errorTemplates['floatGt'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        $error = str_replace('{{min}}', $min, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGe($value, $min, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+\.?[0-9]*$/', $value) === 1) {
                $val = doubleval($value);
                if ($val >= $min)
                    return $value;
            }
        } elseif ($type === 'double') {
            if ($value >= $min)
                return $value;
        }

        $error = self::$errorTemplates['floatGe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        $error = str_replace('{{min}}', $min, $error);
        throw new \Exception($error);
    }

    public static function validateFloatLt($value, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+\.?[0-9]*$/', $value) === 1) {
                $val = doubleval($value);
                if ($val < $max)
                    return $value;
            }
        } elseif ($type === 'double') {
            if ($value < $max)
                return $value;
        }

        $error = self::$errorTemplates['floatLt'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatLe($value, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+\.?[0-9]*$/', $value) === 1) {
                $val = doubleval($value);
                if ($val <= $max)
                    return $value;
            }
        } elseif ($type === 'double') {
            if ($value <= $max)
                return $value;
        }

        $error = self::$errorTemplates['floatLe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGtAndLt($value, $min, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+\.?[0-9]*$/', $value) === 1) {
                $val = doubleval($value);
                if ($val > $min && $val < $max)
                    return $value;
            }
        } elseif ($type === 'double') {
            if ($value > $min && $value < $max)
                return $value;
        }

        $error = self::$errorTemplates['floatGtAndLt'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGeAndLe($value, $min, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+\.?[0-9]*$/', $value) === 1) {
                $val = doubleval($value);
                if ($val >= $min && $val <= $max)
                    return $value;
            }
        } elseif ($type === 'double') {
            if ($value >= $min && $value <= $max)
                return $value;
        }

        $error = self::$errorTemplates['floatGeAndLe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGtAndLe($value, $min, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+\.?[0-9]*$/', $value) === 1) {
                $val = doubleval($value);
                if ($val > $min && $val <= $max)
                    return $value;
            }
        } elseif ($type === 'double') {
            if ($value > $min && $value <= $max)
                return $value;
        }

        $error = self::$errorTemplates['floatGtAndLe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        $error = str_replace('{{min}}', $min, $error);
        $error = str_replace('{{max}}', $max, $error);
        throw new \Exception($error);
    }

    public static function validateFloatGeAndLt($value, $min, $max, $alias = 'Parameter')
    {
        $type = gettype($value);
        if ($type === 'string') {
            if (preg_match('/^\-?[0-9]+\.?[0-9]*$/', $value) === 1) {
                $val = doubleval($value);
                if ($val >= $min && $val < $max)
                    return $value;
            }
        } elseif ($type === 'double') {
            if ($value >= $min && $value < $max)
                return $value;
        }

        $error = self::$errorTemplates['floatGeAndLt'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['length'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['lengthGe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['lengthLe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        $error = str_replace('{{length}}', $length, $error);
        throw new \Exception($error);
    }

    public static function validateLengthGeAndLe($value, $lengthMin, $lengthMax, $alias = 'Parameter')
    {
        if($lengthMin > $lengthMax)
            throw new \Exception("“${alias}”参数的验证模版lengthGeAndLe格式错误, lengthMin不应该大于lengthMax");

        $type = gettype($value);

        if ($type === 'string') {
            $len = strlen($value);
            if ($len >= $lengthMin && $len <= $lengthMax) {
                return $value;
            }
        }

        $error = self::$errorTemplates['lengthGeAndLe'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['letters'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能包含字母
     * 同letters
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

        $error = self::$errorTemplates['alphabet'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['numbers'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”只能是纯数字
     * 同numbers
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

        $error = self::$errorTemplates['digits'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['lettersAndNumbers'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        throw new \Exception($error);
    }

    /**
     * 验证: “{{param}}”必须是数值
     * 一般用于大数处理（超过double表示范围的数,一般会用字符串来表示）
     * 如果是正常范围内的数, 可以使用'int'或'float'来检测
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

        $error = self::$errorTemplates['numeric'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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

        $error = self::$errorTemplates['variableName'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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
        if(is_string($value) && $value === $equalsValue)
            return $value;

        $error = self::$errorTemplates['equals'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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
        if(is_array($valueList) === false || count($valueList)===0)
            throw new \Exception("“${alias}”参数的验证模版(in:)格式错误, 必须提供可取值的列表");

        if(in_array($value, $valueList, true))
            return $value;

        $error = self::$errorTemplates['in'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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
        if(is_array($valueList) === false || count($valueList)===0)
            throw new \Exception("“${alias}”参数的验证模版(notIn:)格式错误, 必须提供不可取的值的列表");

        if(in_array($value, $valueList, true) === false)
            return $value;

        $error = self::$errorTemplates['notIn'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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
            throw new \Exception("“${alias}”参数的验证模版(inIgnoreCase:)格式错误, 必须提供可取值的列表");

        $lowerValue = strtolower($value);
        foreach ($valueList as $v) {
            if (is_string($v) && strtolower($v) === $lowerValue)
                continue;
            goto VeriFailed;
        }
        return $value;

        VeriFailed:
        $error = self::$errorTemplates['inIgnoreCase'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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
            throw new \Exception("“${alias}”参数的验证模版(notInIgnoreCase:)格式错误, 必须提供不可取的值的列表");

        $lowerValue = strtolower($value);
        foreach ($valueList as $v) {
            if (is_string($v) && strtolower($v) === $lowerValue)
                continue;
            goto VeriFailed;
        }
        return $value;

        VeriFailed:
        $error = self::$errorTemplates['notInIgnoreCase'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
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
            throw new \Exception("“${alias}”参数的验证模版(regexp:)格式错误, 没有提供正则表达式");

//        $regexp = str_replace('/', '\/', $regexp);
        $result = @preg_match("/$regexp/", $value);
        if($result === 1)
            return $value;
        else if($result === false)
            throw new \Exception("“${alias}”参数的正则表达式验证失败, 请检查正则表达式是否合法");

        $error = self::$errorTemplates['regexp'];
        $error = str_replace('{{param}}', $alias = 'Parameter', $error);
        if(!$reason)
            $reason = "不匹配正则表达式“${regexp}”";
        $error = str_replace('{{reason}}', $reason, $error);
        throw new \Exception($error);
    }

    //endregion
}