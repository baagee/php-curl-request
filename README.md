# php-curl-request
简单的PHP Curl请求类

## 使用示例
```php
<?php
/**
 * Desc:
 * User: baagee
 * Date: 2019/10/4
 * Time: 17:48
 */
use BaAGee\CurlRequest\MultipleRequest;
use BaAGee\CurlRequest\SingleRequest;

include "../vendor/autoload.php";

function testSingle()
{
    $config  = [];
    $request = new SingleRequest($config);

    $path    = 'http://127.0.0.1:8550/api/test/curl';
    $params  = [
        'username' => 'ghfjfhj',
        'password' => 'fhdghdf',
        'code'     => 'dsgsgd',
    ];
    $method  = 'GET';
    $headers = [
        "X-LOG-ID: ".time()
    ];
    $res     = $request->setHeaders($headers)->request($path, $params, $method);
    var_dump($res);
    // or
    $res = $request->setHeaders($headers)->get($params, $path);
    var_dump($res);
}

if (!function_exists('\curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '')
    {
        return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
    }
}

//上传文件
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
    $headers = [];
    $res     = $re->setCookies($cookies)->request($path, $params, $method);
    var_dump(json_decode($res['result'], true));
}

// 批量请求
function testMulti()
{
    $config   = [
        'return_header' => 0
    ];
    $mRequest = new MultipleRequest($config);

    $path   = 'http://127.0.0.1:8550/api/test/curl';
    $params = [
        'username' => 'ghfjfhj',
        'password' => 'fhdghdf',
        'code'     => 'dsgsgd',
    ];
    // 批量请求数据格式 二维数组
    $data = [
        [
            'path'    => $path,
            'params'  => $params,
            'method'  => 'POST',
            'headers' => [],
            'cookies' => '',
            'curlOptions'=>[],
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
    file_put_contents('./res.json', json_encode($res));
    var_dump(microtime(true) - $t);
}

testSingle();

testMulti();

testUpload();
```

## 以上所有的config默认值：
```php
protected $config = [
    'host'               => '',
    'timeout_ms'         => 1000,//读取超时 毫秒
    'connect_timeout_ms' => 1000, // 连接超时 毫秒
    'max_redirs'         => 1,
    'proxy'              => [// 代理设置
        'ip'   => '',
        'port' => ''
    ],
    'referer'            => '',// http-referer
    'user_agent'         => '',// user-agent
    'return_header'      => 0,// 返回值是否展示header
    'retry_times'        => 1,// 单个请求时失败重试次数
    'ssl'                => [ // ssl相关参数 
        'cert_pwd'  => '',
        'cert_type' => 'PEM',
        'key_type'  => 'PEM',
        'cert_file' => '',
        'key_file'  => '',
    ]
];
```