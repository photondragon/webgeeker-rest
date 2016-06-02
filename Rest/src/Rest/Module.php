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
use WebGeeker\Utils\SimpleCookie;

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
                else
                    $this->deleteById($id, $params);
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

    public static function dispatch($moduleName, $id, IRequest $request, IResponse $response)
    {
        $result = new Result;

        ob_start(); //打开缓冲区，接收所有echo输出
        try {
            $module = self::createModule($moduleName, $request, $response, $result);
            $module->process($id); //处理
        } catch (\Exception $e) {
            $result->error(1, $e->getMessage());
        }
        $echo = ob_get_contents(); //获取所有echo输出
        ob_end_clean();

        $host = $_SERVER['HTTP_HOST'];
        if(@$_SERVER['HTTPS']) {
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
     * @param $moduleName
     * @param IRequest $request
     * @param IResponse $response
     * @param Result $result
     * @return Module
     * @throws \Exception
     */
    private static function createModule($moduleName, IRequest $request, IResponse $response, Result $result)
    {
        if(strlen($moduleName)==0)
            throw new \Exception('参数moduleName无效');
        $className = ucfirst($moduleName) . 'Api';
        try {
            $module = new $className($request, $response, $result);
        } catch (\Exception $e) {
            throw new \Exception("找不模块$moduleName");
        }
        if($module instanceof Module)
            return $module;
        throw new \Exception('无效的模块' . $moduleName);
    }
}