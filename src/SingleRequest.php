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
 * @method get($params, string $path, array $headers = [], string $cookies = '')
 * @method post($params, string $path, array $headers = [], string $cookies = '')
 * @method put($params, string $path, array $headers = [], string $cookies = '')
 * @method delete($params, string $path, array $headers = [], string $cookies = '')
 * @method options($params, string $path, array $headers = [], string $cookies = '')
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
     * @param string $path
     * @param        $params
     * @param string $method
     * @param array  $headers
     * @param string $cookies
     * @return array
     */
    public function request(string $path, $params, string $method, array $headers = [], string $cookies = '')
    {
        if ($this->curlHandler == null) {
            $this->curlHandler = static::getCurlHandler();
        } else {
            curl_reset($this->curlHandler);
        }

        $this->setOptions($this->curlHandler, $method, $path, $params, $headers, $cookies);

        $result = null;
        for ($tryTimes = 0; $tryTimes <= intval($this->config['retry_times']); $tryTimes++) {
            $result   = curl_exec($this->curlHandler);
            $curlInfo = curl_getinfo($this->curlHandler);
            $errno    = curl_errno($this->curlHandler);// 错误码
            $errmsg   = curl_error($this->curlHandler); // 错误信息
            if ($errno == 0) {
                break;
            } else {
                if ($tryTimes == intval($this->config['retry_times'])) {
                    // 出错
                    throw new \RuntimeException($errmsg, $errno);
                } else {
                    $tryTimes++;
                }
            }
        }

        return compact('result', 'curlInfo', 'errno', 'errmsg');
    }

    /**
     * @param $name
     * @param $arguments
     * @return array
     */
    public function __call($name, $arguments)
    {
        return $this->request($arguments[1] ?? '', $arguments[0] ?? '', $name,
            $arguments[2] ?? [], $arguments[3] ?? '');
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


