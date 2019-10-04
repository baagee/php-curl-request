<?php
/**
 * Desc:
 * User: baagee
 * Date: 2019/10/4
 * Time: 20:52
 */

namespace BaAGee\CurlRequest\Base;

abstract class CurlOptionsAbstract
{
    abstract public function setOptions(array $config, string $url, $params, array $headers, string $cookies, string $method);
}
