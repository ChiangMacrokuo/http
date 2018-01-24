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
use Kernel\Route\Url;
use Kernel\Exception\KernelException;
class Request 
{
    
    /**
     * URL过滤规则
     * @var array
     */
    private static $urlFilter = array('xss'=>"\\=\\+\\/v(?:8|9|\\+|\\/)|\\%0acontent\\-(?:id|location|type|transfer\\-encoding)");
    
    /**
     * 提交参数过滤规则
     * @var array
     */
    private static $argsFilter = array(
        'xss'=>"[\\'\\\"\\;\\*\\<\\>].*\\bon[a-zA-Z]{3,15}[\\s\\r\\n\\v\\f]*\\=|\\b(?:expression)\\(|\\<script[\\s\\\\\\/]|\\<\\!\\[cdata\\[|\\b(?:eval|alert|prompt|msgbox)\\s*\\(|url\\((?:\\#|data|javascript)",
        'sql'=>"[^\\{\\s]{1}(\\s|\\b)+(?:select\\b|update\\b|insert(?:(\\/\\*.*?\\*\\/)|(\\s)|(\\+))+into\\b).+?(?:from\\b|set\\b)|[^\\{\\s]{1}(\\s|\\b)+(?:create|delete|drop|truncate|rename|desc)(?:(\\/\\*.*?\\*\\/)|(\\s)|(\\+))+(?:table\\b|from\\b|database\\b)|into(?:(\\/\\*.*?\\*\\/)|\\s|\\+)+(?:dump|out)file\\b|\\bsleep\\([\\s]*[\\d]+[\\s]*\\)|benchmark\\(([^\\,]*)\\,([^\\,]*)\\)|(?:declare|set|select)\\b.*@|union\\b.*(?:select|all)\\b|(?:select|update|insert|create|delete|drop|grant|truncate|rename|exec|desc|from|table|database|set|where)\\b.*(charset|ascii|bin|char|uncompress|concat|concat_ws|conv|export_set|hex|instr|left|load_file|locate|mid|sub|substring|oct|reverse|right|unhex)\\(|(?:master\\.\\.sysdatabases|msysaccessobjects|msysqueries|sysmodules|mysql\\.db|sys\\.database_name|information_schema\\.|sysobjects|sp_makewebtask|xp_cmdshell|sp_oamethod|sp_addextendedproc|sp_oacreate|xp_regread|sys\\.dbms_export_extension)",
        'other'=>"\\.\\.[\\\\\\/].*\\%00([^0-9a-fA-F]|$)|%00[\\'\\\"\\.]"
    );
    
    /**
     * 获取服务器域名地址
     * @return string
     */
    public static function getDomain()
    {
        return isset($_SERVER['scheme']) ? $_SERVER['scheme'] . '://' . $_SERVER['HTTP_HOST'] : '';
    }
 
    /**
     * 获取服务器的IP地址
     */
    public static function getServerIp()
    {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
    }
    
    /**
     * 获取客户端的IP地址
     */
    public static function getClientIp()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';     
    }
    
    /**
     * 获取浏览器类型
     */
    public static function getOSBrowser()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
    
    /**
     * 获取服务开始时间
     */
    public static function getBeginTime()
    {
        return isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time(true);
    }
    
    /**
     * 获取请求的唯一ID
     */
    public static function getRequestId()
    {
        return isset($_SERVER['REQUEST_TIME_FLOAT']) ? md5($_SERVER['REQUEST_TIME_FLOAT']) : md5(uniqid(md5(microtime(true)),true));
    }
    
    /**
     * 获取请求URL参数
     */
    public static function getUri()
    {
        $requestUri = parse_url(rawurldecode($_SERVER['REQUEST_URI']) , PHP_URL_PATH);
        if (false !== ($pos = strpos($requestUri, $_SERVER['SCRIPT_NAME']))){
            return $requestUri;
        }else {
            if (isset($_SERVER['PATH_INFO'])){
                $pathinfo = $_SERVER['PATH_INFO'];
            }else {
                $pathinfo = '/';
            }
            return rtrim($_SERVER['SCRIPT_NAME'], '/') . $pathinfo;
        }
    }
    
    /**
     * 获取http请求方法
     */
    public static function getMethod()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }
    
    /**
     * 获取GET参数
     * @param  string  $name  参数名
     * @return mixed
     */
    public static function get($name = '') 
    {
        $_GET && self::safeFilter($_GET, self::$argsFilter);            
        if (empty($name)){
            return $_GET;
        }else {
            return isset($_GET[$name]) ? $_GET[$name] : '';
        }
    }
    
    /**
     * 获取POST参数
     * @param  string  $name  参数名
     * @return mixed
     */
    public static function post($name = '')
    {
        $_POST && self::safeFilter($_POST, self::$argsFilter);
        if (empty($name)){
            return $_POST;
        }else {
            return isset($_POST[$name]) ? $_POST[$name] : '';
        }
    }
    
    /**
     * 获取COOKIE参数
     * @param string $name 参数名
     * @return mixed
     */
    public static function cookie($name = '')
    {
        $_COOKIE && self::safeFilter($_COOKIE, self::$argsFilter);
        if (empty($name)){
            return $_COOKIE;
        }else {
            return isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
        }
    }
    
    /**
     * 获取REQUEST参数
     * @param  string  $name 参数名
     * @return mixed
     */
    public static function request($name = '')
    {
        $_REQUEST && self::safeFilter($_REQUEST, self::$argsFilter);
        if (empty($name)){
            return $_REQUEST;
        }else {
            return isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
        }
    }
    
    /**
     * 获取SERVER参数
     * @param  string  $name 参数名
     * @return mixed
     */
    public static function server($name = '')
    {
        if (empty($name)){
            return $_SERVER;
        }elseif ($name == 'HTTP_REFERER'){
            $_SERVER[$name] && self::safeFilter($_SERVER[$name], self::$argsFilter);
            return $_SERVER[$name];
        }elseif($name == 'QUERY_STRING'){
            $_SERVER[$name] && self::safeFilter($_SERVER[$name], self::$urlFilter);
            return $_SERVER[$name];
        }else {
            return isset($_SERVER[$name]) ? $_SERVER[$name] : '';
        }
    }
    
    /**
     * 防护XSS,SQL,代码执行，文件包含等多种高危漏洞
     * @param Array $arr
     *
     */
    private static function safeFilter ($arr, Array $filter){
        foreach ($arr as $key => $value){
            if (!is_array($key)){
                if (!get_magic_quotes_gpc()){
                    $key = addslashes($key);
                }
                self::filter($key, $filter);
                $arr[htmlentities(strip_tags($key))] = $value;
            }else {
                self::safeFilter($arr, $filter);
            }
            if (!is_array($value)){
                if (!get_magic_quotes_gpc()){
                    $value = addslashes($value);
                }
                self::filter($value, $filter);
                $arr[$key] = htmlentities(strip_tags($value));
            }else{
                self::safeFilter($arr[$key], $filter);
            }
        }
    }
    
    /**
     * 过滤字符串
     * @param string $string  需过滤字符串
     * @param array $filter 过滤规则
     * @throws KernelException
     */
    private static function filter($string, Array $filter)
    {
        foreach ($filter as $key => $value){
            if (preg_match("/" . $value . "/is", $string) == 1 || preg_match("/" . $value . "/is", urlencode($string)) == 1){
                L("<br>IP: ".$_SERVER["REMOTE_ADDR"]."<br>时间: ".strftime("%Y-%m-%d %H:%M:%S")."<br>页面:".$_SERVER["PHP_SELF"]."<br>提交方式: ".$_SERVER["REQUEST_METHOD"]."<br>提交数据: ".$string, 'info');
                throw new KernelException('提交带有不合法参数！');
            }
        }
    }
}