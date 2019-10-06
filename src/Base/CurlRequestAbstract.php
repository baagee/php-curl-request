<?php

namespace BaAGee\CurlRequest\Base;

use BaAGee\CurlRequest\CurlOptions;

/**
 * Desc:
 * User: baagee
 * Date: 2019/7/19
 * Time: 14:13
 */
abstract class CurlRequestAbstract
{
    public const ALLOW_METHODS = [
        'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'
    ];

    /**
     * @var array 配置
     */
    protected $config = [
        'host'               => '',
        'timeout_ms'         => 1000,//读取超时 毫秒
        'connect_timeout_ms' => 1000, // 连接超时 毫秒
        'max_redirs'         => 1,
        'proxy'              => [
            'ip'   => '',
            'port' => ''
        ],
        'referer'            => '',
        'user_agent'         => '',
        'return_header'      => 0,//返回值是否展示header
        'retry_times'        => 1,//单个请求时失败重试次数
        'ssl'                => [
            'cert_pwd'  => '',
            'cert_type' => 'PEM',
            'key_type'  => 'PEM',
            'cert_file' => '',
            'key_file'  => '',
        ]
    ];

    /**
     * @param        $curlHandler
     * @param string $method
     * @param string $path
     * @param        $params
     * @param array  $headers
     * @param string $cookies
     * @param array  $curlOptions
     */
    final protected function setOptions(&$curlHandler, string $method, string $path, $params, array $headers,
                                        string $cookies, array $curlOptions = [])
    {
        $path        = $this->getUrl($path);
        $optionsObj  = new CurlOptions($curlHandler);
        $curlHandler = $optionsObj->setOptions($this->config, $path, $params, $headers, $cookies, $method, $curlOptions)->getCurlHandler();
    }

    /**
     * @return false|resource
     */
    final protected static function getCurlHandler()
    {
        $ch = curl_init();
        curl_reset($ch);
        return $ch;
    }

    /**
     * @return resource
     */
    final protected static function getMultiCurlHandler()
    {
        return curl_multi_init();
    }

    /**
     * CurlRequest constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * @param $path
     * @return string
     */
    final protected function getUrl($path)
    {
        if (stripos($path, 'http://') === 0 || stripos($path, 'https://') === 0) {
            //有http
        } else {
            // 没有http
            if (empty($this->config['host'])) {
                throw new \RuntimeException("config.host不能为空");
            }
            $path = sprintf('%s/%s', rtrim($this->config['host'], '/ '), ltrim($path, '/ '));
            if (stripos($path, 'http://') === false && stripos($path, 'https://') === false) {
                // 还是没有http 加上
                $path = 'http://' . $path;
            }
        }
        return $path;
    }
}
