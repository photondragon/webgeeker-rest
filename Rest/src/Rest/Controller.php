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

}