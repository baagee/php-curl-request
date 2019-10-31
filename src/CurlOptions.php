<?php
/**
 * Desc:
 * User: baagee
 * Date: 2019/10/4
 * Time: 14:11
 */

namespace BaAGee\CurlRequest;

use BaAGee\CurlRequest\Base\CurlOptionsAbstract;
use BaAGee\CurlRequest\Base\CurlRequestAbstract;

/**
 * Class CurlOptions
 * @package CurlRequest
 */
class CurlOptions extends CurlOptionsAbstract
{
    /**
     * @var null
     */
    protected $curlHandler = null;

    /**
     * @var array
     */
    protected $headers = [
        'Accept-Encoding: gzip, deflate'
    ];

    /**
     * @var array
     */
    protected $options = [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_ENCODING       => 'gzip,deflate'
    ];

    /**
     * CurlOptions constructor.
     * @param $curlHandler
     */
    public function __construct($curlHandler)
    {
        $this->curlHandler = $curlHandler;
    }

    /**
     * @param $maxRedirs
     */
    protected function setRedirs($maxRedirs)
    {
        if ($maxRedirs > 0) {
            $this->options[CURLOPT_FOLLOWLOCATION] = true;
            $this->options[CURLOPT_MAXREDIRS]      = $maxRedirs;
        } else {
            $this->options[CURLOPT_FOLLOWLOCATION] = false;
        }
    }

    /**
     * @param $ip
     * @param $port
     */
    protected function setProxy($ip, $port)
    {
        if (!empty($ip) && !empty($port)) {
            // 验证IP
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new \RuntimeException("proxy.ip不合法");
            }
            $this->options[CURLOPT_PROXY]     = $ip;
            $this->options[CURLOPT_PROXYPORT] = intval($port);
        }
    }

    /**
     * @param $referer
     */
    protected function setReferer($referer)
    {
        if (!empty($referer)) {
            $this->options[CURLOPT_REFERER] = $referer;
        }
    }

    /**
     * @param $return
     */
    protected function setReturnHeader($return)
    {
        $this->options[CURLOPT_HEADER] = $return;
    }

    /**
     * @param $cTimeout
     * @param $timeout
     */
    protected function setTimeout($cTimeout, $timeout)
    {
        $this->options[CURLOPT_CONNECTTIMEOUT_MS] = $cTimeout;
        $this->options[CURLOPT_TIMEOUT_MS]        = $timeout;
    }

    /**
     * @param $ua
     */
    protected function setUserAgent($ua)
    {
        if (!empty($ua)) {
            $this->options[CURLOPT_USERAGENT] = $ua;
        }
    }

    /**
     * @param $url
     * @param $config
     */
    protected function checkSSL($url, $config)
    {
        $ssl                                   = $config['ssl'];
        $this->options[CURLOPT_SSL_VERIFYPEER] = 0;
        $this->options[CURLOPT_SSL_VERIFYHOST] = 0;
        if (stripos($url, 'https://') !== false && stripos($url, $config['host']) !== false) {
            // config的host和url的host是同一个
            if (
                is_array($ssl) &&
                !empty($ssl['cert_file']) && is_file($ssl['cert_file']) &&
                !empty($ssl['key_file']) && is_file($ssl['key_file'])
            ) {
                if (isset($ssl['cert_type']) && !empty($ssl['cert_type'])) {
                    $this->options[CURLOPT_SSLCERTTYPE] = $ssl['cert_type'];
                }
                if (isset($ssl['cert_file']) && !empty($ssl['cert_file'])) {
                    $this->options[CURLOPT_SSLCERT] = $ssl['cert_file'];
                }
                if (isset($ssl['key_type']) && !empty($ssl['key_type'])) {
                    $this->options[CURLOPT_SSLKEYTYPE] = $ssl['key_type'];
                }
                if (isset($ssl['key_file']) && !empty($ssl['key_file'])) {
                    $this->options[CURLOPT_SSLKEY] = $ssl['key_file'];
                }

                if (isset($ssl['cert_pwd']) && !empty($ssl['cert_pwd'])) {
                    $this->options[CURLOPT_SSLCERTPASSWD] = $ssl['cert_pwd'];
                }
            }
            // https请求 不验证证书和host
        }
    }

    /**
     * @param $cookies
     */
    protected function setCookies($cookies)
    {
        if (!empty($cookies)) {
            $this->options[CURLOPT_COOKIE] = $cookies;
        }
    }

    /**
     * @param $url
     * @param $method
     * @param $params
     */
    protected function setPayload(&$url, $method, $params)
    {
        $method = strtoupper($method);
        if (!in_array($method, CurlRequestAbstract::ALLOW_METHODS)) {
            throw new \RuntimeException('请求方法不允许,只允许：' . implode(',', CurlRequestAbstract::ALLOW_METHODS));
        }
        if ($method === 'GET') {
            // GET方式穿参数
            $params = http_build_query($params);
            strpos($url, '?') === false ? $url .= '?' : $url .= '&';
            $url                            .= $params;
            $this->options[CURLOPT_HTTPGET] = 1;
            $this->options[CURLOPT_POST]    = 0;
        } else {
            // 非GET方式
            $this->options[CURLOPT_HTTPGET] = 0;
            if ($method !== 'POST') {
                // 不是POST 比如PUT delete options
                if (is_array($params)) {
                    $params          = json_encode($params, JSON_UNESCAPED_UNICODE);
                    $this->headers[] = 'Content-Type: application/json';
                } else {
                    $params = strval($params);
                }
            } else {
                // POST传输数据
                if (is_array($params)) {
                    $hasFile = false;
                    foreach ($params as $field => $val) {
                        if ($val instanceof \CURLFile) {
                            $hasFile = true;
                            break;
                        }
                    }
                    if ($hasFile) {
                        //有文件上传 不做处理
                        $headers[] = 'Content-Type: multipart/form-data';
                    } else {
                        // 没有文件 使用urlencoded
                        $params          = http_build_query($params);
                        $this->headers[] = "Content-Type: application/x-www-form-urlencoded";
                        $this->headers[] = "Content-Length:" . strlen($params);
                    }
                } elseif (is_string($params)) {
                    $isJson = function ($jsonStr) {
                        json_decode($jsonStr, true);
                        return json_last_error() === 0;
                    };
                    if ($isJson($params)) {
                        $this->headers[] = "Content-Type: application/json";
                    } else {
                        $this->headers[] = "Content-Type: application/x-www-form-urlencoded";
                    }
                    $this->headers[] = "Content-Length:" . strlen($params);
                }
            }
            $this->options[CURLOPT_CUSTOMREQUEST] = $method;
            $this->options[CURLOPT_POSTFIELDS]    = $params;
        }
    }

    /**
     * @param $headers
     */
    protected function setHeaders($headers = [])
    {
        // 设置请求头
        if (!empty($headers)) {
            $this->headers = array_values(array_unique(array_merge($this->headers, $headers)));
        }

        $this->options[CURLOPT_HTTPHEADER] = $this->headers;
    }

    /**
     * @param $url
     */
    protected function setUrl($url)
    {
        //设置请求URL
        $this->options[CURLOPT_URL] = $url;
    }

    /**
     * @param array  $config
     * @param string $url
     * @param        $params
     * @param string $method
     * @param array  $headers
     * @param string $cookies
     */
    protected function buildOptions(array $config, string $url, $params, string $method, array $headers, string $cookies)
    {
        //设置超时
        $this->setTimeout($config['connect_timeout_ms'], $config['timeout_ms']);
        // 返回值是否返回response header
        $this->setReturnHeader($config['return_header']);
        // 最大跳转
        $this->setRedirs($config['max_redirs']);
        // 代理
        $this->setProxy($config['proxy']['ip'], $config['proxy']['port']);
        // referer
        $this->setReferer($config['referer']);
        // user-agent
        $this->setUserAgent($config['user_agent']);
        // 请求参数和方法
        $this->setPayload($url, $method, $params);
        // 设置请求头
        $this->setHeaders($headers);
        //设置请求URL
        $this->setUrl($url);
        // https不验证
        $this->checkSSL($url, $config);
        // 设置Cookie
        $this->setCookies($cookies);
    }

    /**
     * 设置CURL参数
     * @param array  $config
     * @param string $url
     * @param        $params
     * @param array  $headers
     * @param string $cookies
     * @param string $method
     * @param array  $options
     * @return $this
     */
    public function setOptions(array $config, string $url, $params, array $headers, string $cookies, string $method, array $options = [])
    {
        $this->buildOptions($config, $url, $params, $method, $headers, $cookies);
        if (!empty($options) && is_array($options)) {
            // $this->options = array_merge($this->options, $options);
            $this->options = $options + $this->options;
        }
        curl_setopt_array($this->curlHandler, $this->options);
        return $this;
    }

    /**
     * @return resource
     */
    public function getCurlHandler()
    {
        return $this->curlHandler;
    }
}