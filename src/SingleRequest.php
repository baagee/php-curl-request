<?php
/**
 * Desc: 单个请求
 * User: baagee
 * Date: 2019/10/4
 * Time: 14:16
 */

namespace BaAGee\CurlRequest;

use BaAGee\CurlRequest\Base\CurlRequestAbstract;

/**
 * @method get($params, string $path)
 * @method post($params, string $path)
 * @method put($params, string $path)
 * @method delete($params, string $path)
 * @method options($params, string $path)
 * @method patch($params, string $path)
 * Class SingleRequest
 * @package CurlRequest
 */
class SingleRequest extends CurlRequestAbstract
{
    /**
     * @var null
     */
    protected $curlHandler = null;

    /**
     * @var array
     */
    protected $headers = [];
    /**
     * @var string
     */
    protected $cookies = '';
    /**
     * @var array
     */
    protected $curlOptions = [];

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param string $cookies
     * @return $this
     */
    public function setCookies(string $cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * @param array $curlOptions
     * @return $this
     */
    public function setCurlOptions(array $curlOptions)
    {
        $this->curlOptions = $curlOptions;
        return $this;
    }

    /**
     * 清空
     */
    private function reset()
    {
        $this->cookies     = '';
        $this->headers     = [];
        $this->curlOptions = [];
    }

    /**
     * 发送请求
     * @param string $path   请求路径
     * @param mixed  $params 参数
     * @param string $method 请求方法
     * @return array
     */
    public function request(string $path, $params, string $method)
    {
        if ($this->curlHandler == null) {
            $this->curlHandler = static::getCurlHandler();
        } else {
            curl_reset($this->curlHandler);
        }

        $this->setOptions($this->curlHandler, $method, $path, $params, $this->headers, $this->cookies, $this->curlOptions);
        $this->reset();//清空
        $result = null;
        for ($retry = 0; $retry <= intval($this->config['retry_times']); $retry++) {
            $result   = curl_exec($this->curlHandler);
            $curlInfo = curl_getinfo($this->curlHandler);
            $errno    = curl_errno($this->curlHandler);// 错误码
            $errmsg   = curl_error($this->curlHandler); // 错误信息
            if ($errno == 0) {
                break;
            } else {
                if ($retry == intval($this->config['retry_times'])) {
                    // 出错
                    throw new \RuntimeException($errmsg, $errno);
                } else {
                    $retry++;
                }
            }
        }

        return compact('retry','result', 'curlInfo', 'errno', 'errmsg');
    }

    /**
     * @param $name
     * @param $arguments
     * @return array
     */
    public function __call($name, $arguments)
    {
        return $this->request($arguments[1] ?? '', $arguments[0] ?? '', $name);
    }

    /**
     * 释放资源
     */
    public function __destruct()
    {
        if (is_resource($this->curlHandler)) {
            curl_close($this->curlHandler);
        }
    }
}


