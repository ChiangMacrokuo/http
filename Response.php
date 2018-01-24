<?php
/**********************************************************************
 * JUST LOVE EIPHP
 ***********************************************************************
 * Copyright (c) 2017 http://www.eiphp.com All rights reserved.
 ***********************************************************************
 * Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 ***********************************************************************
 * Author: ChiangMacrokuo <420921698@qq.com>
 ***********************************************************************/
namespace Kernel\Http;

/**
 * 响应
 */
class Response
{
    
    private static $instance = null;
    
    public function __construct(){}
    
    public static function instanced(){
        if (is_null(self::$instance) || !self::$instance instanceof self)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 响应
     *
     * @param  mixed $response 响应内容
     * @return json
     */
    public function response($response)
    {
        header('Content-Type:Application/json; Charset=utf-8');
        die(json_encode(
            $response,
            JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * REST风格 成功响应
     *
     * @param  mixed $response 响应内容
     * @return json
     */
    public function restSuccess($response)
    {
        header('Content-Type:Application/json; Charset=utf-8');
        die(json_encode([
            'code'    => 200,
            'message' => 'OK',
            'result'  => $response
        ],JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * REST风格 失败响应
     *
     * @param  mixed $response 响应内容
     * @return json
     */
    public function restFail($response,$code = 500,$message = 'Internet Server Error')
    {
        header('Content-Type:Application/json; Charset=utf-8');
        die(json_encode([
            'code'    => $code,
            'message' => $message,
            'result'  => $response
        ],JSON_UNESCAPED_UNICODE));
    }

}
