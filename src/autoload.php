<?php
/*
 * Project: study
 * File: autoload.php
 * CreateTime: 16/1/29 21:28
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file autoload.php
 * @brief brief description
 *
 * elaborate description
 */

spl_autoload_register(function ($class) {

    // 命名空间前缀
    $prefix = 'WebGeeker\\Rest\\';

    // 命名空间前缀对应的目录
    $base_dir = __DIR__ . '/Rest/';

    // 命名空间前缀不匹配
    $len = strlen($prefix);
    if ($len && strncmp($prefix, $class, $len) !== 0)
        return;

    // 类名去掉命名空间前缀
    $relative_class = substr($class, $len);
    $relativePath = str_replace('\\', '/', $relative_class);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . $relativePath . '.php';
    if (file_exists($file)) {
        require $file;
        return;
    }
});