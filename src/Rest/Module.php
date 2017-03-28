<?php
/*
 * Project: study
 * File: Module.php
 * CreateTime: 16/1/31 00:17
 * Author: photondragon
 * Email: photondragon@163.com
 */

namespace WebGeeker\Rest;

use \Psr\Http\Message\ResponseInterface as IResponse;
use \Psr\Http\Message\ServerRequestInterface as IRequest;

/**
 * @file Module.php
 * @brief brief description
 *
 * elaborate description
 */

class Module
{
    protected $request;
    protected $response;
    protected $result; //处理结果，作为响应的内容

    public function __construct(IRequest $request, IResponse $response, Result $result)
    {
        $this->request = $request;
        $this->response = $response;
        $this->result = $result;
    }

    //region HTTP Method handlers

    /**
     * 获取model列表
     * 对应HTTP请求: GET https://example.com/api/model
     * @param $params array
     */
    public function get($params)
    {
        $this->notSupport($params);
    }

    /**
     * 根据id查询指定model
     * 对应HTTP请求: GET https://example.com/api/model/123
     * @param $id
     * @param $params array
     */
    public function getById($id, $params)
    {
        $this->notSupport($id, $params);
    }

    /**
     * 新建一条记录（id自动生成）
     * 对应HTTP请求: POST https://example.com/api/model
     * @param $params array
     */
    public function post($params)
    {
        $this->notSupport($params);
    }

    /**
     * 根据id*增量更新*指定model
     * 对应HTTP请求: POST https://example.com/api/model/123
     * @param $id
     * @param $params array
     */
    public function postToId($id, $params)
    {
        $this->notSupport($id, $params);
    }

    /**
     * 新建一条记录（id手动指定）
     * 对应HTTP请求: PUT https://example.com/api/model
     * @param $params array 参数中必须包含id字段
     */
    public function put($params)
    {
        $this->notSupport($params);
    }

    /**
     * 根据id*覆盖*指定model
     * 对应HTTP请求: PUT https://example.com/api/model/123
     * @param $id
     * @param $params array
     */
    public function putToId($id, $params)
    {
        $this->notSupport($id, $params);
    }

    /**
     * 清空或条件删除
     * 对应HTTP请求: DELETE https://example.com/api/model
     * @param $params array 可以包含删除条件
     */
    public function delete($params)
    {
        $this->notSupport($params);
    }

    /**
     * 根据id删除指定model
     * 对应HTTP请求: DELETE https://example.com/api/model/123
     * @param $id
     * @param $params array
     */
    public function deleteById($id, $params)
    {
        $this->notSupport($id, $params);
    }

    /**
     * 根据id修订指定model
     * 对应HTTP请求: PATCH https://example.com/api/model/123
     * 要求: 对于网络失败导致的重复提交要*幂等*
     * 比如model.balance=100, PATCH balance=10一次后, balance=110; 再PATCH一次, balance=120
     * @param $id
     * @param $params
     */
    public function patchById($id, $params)
    {
        $this->notSupport($id, $params);
    }

    /**
     * @param $id mixed|null
     */
    public function head($id)
    {
        $this->notSupport($id);
    }

    /**
     * @param $id mixed|null
     */
    public function trace($id)
    {
        $this->notSupport($id);
    }

    /**
     * @param $id mixed|null
     */
    public function options($id)
    {
        $this->notSupport($id);
    }

    //endregion

    final protected function notSupport()
    {
        func_get_args();
        $url = $this->request->getUri();
        $method = $this->request->getMethod();
        throw new \Exception("不支持的操作: $method $url");
    }

//    public function __call($function_name, $args)
//    {
//        $func = get_class($this) . "::$function_name()";
//        throw new \Exception("调用了不存在的方法$func");
//    }

    /**
     * 根据HTTP Method来分发请求。
     * 不同的Method对应不同的处理方法，如果现有的分发方案不满足需求，子类可以重载这个方法。
     * @param $id mixed|null
     */
    private function process($id)
    {
        $request = $this->request;
        $method = $request->getMethod();
        switch ($method) {
            case 'GET': {
                $params = $request->getQueryParams();
                if($id===null)
                    $this->get($params);
                else {
                    $m = 'get' . ucfirst($id);
                    if(method_exists($this, $m))
                        $this->$m($params);
                    else
                        $this->getById($id, $params);
                }
                break;
            }
            case 'POST': {
                $params = $request->getParsedBody();
                if($id===null)
                    $this->post($params);
                else {
                    $m = 'postTo' . ucfirst($id);
                    if(method_exists($this, $m))
                        $this->$m($params);
                    else
                        $this->postToId($id, $params);
                }
                break;
            }
            case 'PUT': {
                $params = $request->getParsedBody();
                if($id===null)
                    $this->put($params);
                else
                    $this->putToId($id, $params);
                break;
            }
            case 'DELETE': {
                $params = $request->getQueryParams();
                if($id===null)
                    $this->delete($params);
                else {
                    $m = 'delete' . ucfirst($id);
                    if(method_exists($this, $m))
                        $this->$m($params);
                    else
                        $this->deleteById($id, $params);
                }
               break;
            }
            case 'PATCH': {
                $params = $request->getParsedBody();
                if($id===null)
                    $this->notSupport();
                else
                    $this->patchById($id, $params);
                break;
            }
            case 'HEAD': {
                $this->head($id);
                break;
            }
            case 'TRACE': {
                $this->trace($id);
                break;
            }
            case 'OPTIONS': {
                $this->options($id);
                break;
            }
            default: {
                $this->notSupport();
                break;
            }
        } //end switch
    }

    public static function dispatch($component, $modelName, $id, IRequest $request, IResponse $response)
    {
        $result = new Result;

        ob_start(); //打开缓冲区，接收所有echo输出
        try {
            $module = self::createModule($component, $modelName, $request, $response, $result);
            $module->process($id); //处理
        } catch (\Exception $e) {
            $result->debug($e->getTraceAsString());
            $result->error(1, $e->getMessage());
        }
        $echo = ob_get_contents(); //获取所有echo输出
        ob_end_clean();

        if(@$_SERVER['HTTPS']) {
            $host = $_SERVER['HTTP_HOST'];
            $response = $response->withHeader('Content-Type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', "http://$host")
                ->withHeader('Access-Control-Allow-Credentials', "true");
        }
        else{
            $response = $response->withHeader('Content-Type', 'application/json');
        }

        $result->debug($echo); //将所有echo输出作为debug信息返回
//        $result->debug(var_export(SimpleCookie::getCookies(), true));
        $response->getBody()->write($result->getJsonString());
        return $response;
    }

    /**
     * @param $component string 组件名
     * @param $modelName string 模型名
     * @param IRequest $request
     * @param IResponse $response
     * @param Result $result
     * @return Module
     * @throws \Exception
     */
    private static function createModule($component, $modelName, IRequest $request, IResponse $response, Result $result)
    {
        if(strlen($modelName)==0)
            throw new \Exception('参数moduleName无效');
        $component = ucfirst($component);
        $modelName = ucfirst($modelName);
        $className = "Api\\Components\\$component\\$modelName\\${modelName}Api";

        if(class_exists($className)===false) //任务类不存在
            throw new \Exception("找不到$className");

        try {
            $module = new $className($request, $response, $result);
        } catch (\Exception $e) {
            throw new \Exception("创建模块${modelName}失败");
        }
        if($module instanceof Module)
            return $module;
        throw new \Exception('无效的模块' . $modelName);
    }

    /**
     * 验证输入参数
     * @param $params array 包含输入参数的数组
     * @param $validators array 包含验证字符串的数组
     * @throws \Exception 验证不通过会抛出异常
     */
    public function validate($params, $validators)
    {
        if(is_array($params) === false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): \$params必须是数组");

        foreach ($validators as $name => $validator) {
            $keys = explode('.', $name);
            if(count($keys)===0)
                throw new \Exception("validators数组中包含空的字段名");
            self::_validateUnit($params, $keys, $validator);
        }
    }

    private static function _validateUnit($params, $keys, $validator, $keyPrefix = '')
    {
        $value = $params;
        $keysCount = count($keys);
        for ($n = 0; $n < $keysCount; $n++) {
            $key = $keys[$n];
            if($key === '*'){
                Validation::validateArray($value, $keyPrefix);
                $c = count($value);
                for ($i = 0; $i < $c; $i++) {
                    $element = $value[$i];
                    self::_validateUnit($element, array_slice($keys, $n+1), $validator, $keyPrefix."[$i]");
                }
                return;
            } else {
                $value = @$value[$key];
                if($keyPrefix === '')
                    $keyPrefix = $key;
                else
                    $keyPrefix .= ".$key";
            }
            if($value === null)
                break;
        }
        Validation::validate($value, $validator, $keyPrefix);
    }
//    //region 输出结果
//
//    /**
//     * @param $errorCode
//     * @param $errorString
//     */
//    protected function error($errorCode, $errorString)
//    {
//        $this->result->error($errorCode, $errorString);
//    }
//
//    /**
//     * @param $message
//     */
//    protected function warning($message)
//    {
//        $this->result->warning($message);
//    }
//
//    /**
//     * @param $message
//     */
//    protected function debug($message)
//    {
//        $this->result->debug($message);
//    }
//    //endregion
}