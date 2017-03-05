<?php
/*
 * Project: simpleim
 * File: Controller.php
 * CreateTime: 16/3/25 18:39
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Controller.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;

use \Psr\Http\Message\ResponseInterface as IResponse;
use \Psr\Http\Message\ServerRequestInterface as IRequest;

/**
 * @class Controller
 * @package WebGeeker\Rest
 * @brief brief description
 *
 * elaborate description
 */
class Controller
{
    protected $request;
    protected $response;
//    protected $result; //处理结果，作为响应的内容

    public function __construct(IRequest $request, IResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
//        $this->result = $result;
    }

    public static function dispatch($controllerName, $action, IRequest $request, IResponse $response)
    {
        if (strlen($action) === 0)
            $action = 'index';
        $actionName = lcfirst($action) . 'Action';

        ob_start(); //打开缓冲区，接收所有echo输出

        try {
            $controller = self::createController($controllerName, $request, $response);
            $controller->$actionName();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        $echo = ob_get_contents(); //获取所有echo输出
        ob_end_clean();

        $response->getBody()->write($echo);
        $response = $response->withHeader('Content-Type', 'text/plain');
        return $response;
    }

    /**
     * @param $controllerName string
     * @param IRequest $request
     * @param IResponse $response
     * @return Controller
     * @throws \Exception
     */
    private static function createController($controllerName, IRequest $request, IResponse $response)
    {
        if(strlen($controllerName)==0)
            throw new \Exception('参数controllerName无效');
        $controllerName = ucfirst($controllerName) . 'Controller';
        try {
            $controller = new $controllerName($request, $response);
        } catch (\Exception $e) {
            throw new \Exception("找不控制器$controllerName");
        }
        if($controller instanceof Controller)
            return $controller;
        throw new \Exception('无效的控制器' . $controllerName);
    }
}