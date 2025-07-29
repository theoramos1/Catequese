<?php
namespace catechesis;

class CurlStubs
{
    public static array $mockResponse = ['exec' => false, 'status' => 0];
}

function curl_init($url = null)
{
    return \curl_init($url);
}

function curl_exec($ch)
{
    return CurlStubs::$mockResponse['exec'];
}

function curl_getinfo($ch, $option)
{
    if ($option === CURLINFO_HTTP_CODE) {
        return CurlStubs::$mockResponse['status'];
    }
    return null;
}

function curl_close($ch)
{
    \curl_close($ch);
}
