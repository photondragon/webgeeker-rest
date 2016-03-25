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

    /**
     * 获取列表。对应GET without ID
     * @param $params array
     */
    public function get($params)
    {
        $this->notSupport($params);
    }

    /**
     * 根据ID查询一条记录。对应GET with ID
     * @param $id
     * @param $params
     */
    public function getById($id, $params)
    {
        $this->notSupport($id, $params);
    }

    /**
     * 新建一条记录。对应POST without ID
     * @param $params array
     */
    public function post($params)
    {
        $this->notSupport($params);
    }

    /**
     * 增量更新。对应POST with ID
     * @param $id
     * @param $params
     */
    public function postById($id, $params)
    {
        $this->notSupport($id, $params);
    }

    /**
     * 如果$id指定的记录存在，则覆盖更新；如果不存在，则新建一条记录
     * 对应PUT with ID
     * @param $id
     * @param $params
     */
    public function putById($id, $params)
    {
        $this->notSupport($id, $params);
    }

    /**
     * 条件删除或清空。对应DELETE without ID
     * @param $params
     * @throws \Exception
     */
    public function delete($params)
    {
        $this->notSupport($params);
    }

    /**
     * 删除$id指定的记录。对应DELETE with ID
     * @param $id
     */
    public function deleteById($id)
    {
        $this->notSupport($id);
    }

    /**
     * 部分更新$id指定的记录
     * 对应PUT with ID
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

    protected function notSupport()
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
    public function process($id)
    {
        $request = $this->request;
        $method = $request->getMethod();
        switch ($method) {
            case 'GET': {
                $params = $request->getQueryParams();
                if($id===null)
                    $this->get($params);
                else
                    $this->getById($id, $params);
                break;
            }
            case 'POST': {
                $params = $request->getParsedBody();
                if($id===null)
                    $this->post($params);
                else
                    $this->postById($id, $params);
                break;
            }
            case 'PUT': {
                $params = $request->getParsedBody();
                if($id===null)
                    $this->notSupport();
                else
                    $this->putById($id, $params);
                break;
            }
            case 'DELETE': {
                if($id===null)
                    $this->delete($request->getParsedBody());
                else
                    $this->deleteById($id);
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
}