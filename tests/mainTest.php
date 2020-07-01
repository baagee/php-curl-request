<?php
/**
 * Desc:
 * User: baagee
 * Date: 2019/7/27
 * Time: 20:39
 */

use BaAGee\CurlRequest\MultipleRequest;
use BaAGee\CurlRequest\SingleRequest;

include __DIR__ . '/../vendor/autoload.php';


if (!function_exists('\curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '')
    {
        return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
    }
}

class mainTest extends \PHPUnit\Framework\TestCase
{
    function testSingle()
    {
        $config  = [
            'referer'    => 'https://www.json.cn/',
            'host'       => '127.0.0.1:8550',
            'user_agent' => 'test'
        ];
        $request = new SingleRequest($config);

        $path    = '/api/test/curl';
        $params  = [
            'username' => 'ghfjfhj',
            'password' => 'fhdghdf',
            'code'     => 'dsgsgd',
        ];
        $method  = 'GET';
        $headers = [];
        $res     = $request->setHeaders($headers)->request($path, $params, $method);
        var_dump($res);

        $res = $request->setHeaders($headers)->get($params, $path);
        var_dump($res);
        $this->assertNotEmpty($res);
    }


    function testUpload()
    {
        $re      = new SingleRequest([
            'host'               => 'http://127.0.0.1:8550',
            'timeout_ms'         => 1000,//读取超时 毫秒
            'connect_timeout_ms' => 1000, // 连接超时 毫秒
        ]);
        $path    = '/api/upload/images';
        $params  = [
            'image-file' => curl_file_create(realpath('./111.png'), 'image/jpeg'),
        ];
        $method  = 'POST';
        $cookies = 'PHPSESSID=147f6c0f7e8b93879183a93e00843ecf';
        $headers = [
            'x-test-time: ' . time(),
        ];
        $res     = $re->setHeaders($headers)->setCookies($cookies)->request($path, $params, $method);
        var_dump(json_decode($res['result'], true));
        $this->assertNotEmpty($res);
    }


    function testMulti()
    {
        $config   = [
            'return_header' => 0,
            'max_redirs'    => 0,
            'retry_times'   => 3
        ];
        $mRequest = new MultipleRequest($config);

        $path   = 'http://127.0.0.1:9001/index.php';
        $params = [
            'username' => 'ghfjfhj',
            'password' => 'fhdghdf',
            'code'     => 'dsgsgd',
        ];

        $data = [
            [
                'path'    => $path,
                'params'  => $params,
                'method'  => 'POST',
                'headers' => [],
                'cookies' => ''
            ],
            [
                'path'    => $path,
                'params'  => $params,
                'method'  => 'GET',
                'headers' => [],
                'cookies' => ''
            ],
            [
                'path'    => $path,
                'params'  => $params,
                'method'  => 'DELETE',
                'headers' => [],
                'cookies' => ''
            ],
            [
                'path'    => $path,
                'params'  => $params,
                'method'  => 'PUT',
                'headers' => [],
                'cookies' => ''
            ],
            [
                'path'    => $path,
                'params'  => $params,
                'method'  => 'OPTIONS',
                'headers' => [],
                'cookies' => ''
            ],
        ];
        $t    = microtime(true);
        $res  = $mRequest->request($data);
        var_dump(microtime(true) - $t);
        foreach ($res as $re){
            $this->assertNotEmpty($re['result']);
        }
    }
}

