<?php

use Moccalotto\Hayttp\Hayttp;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

if (!function_exists('hayttp')) {

    /**
     * Get the default Hayttp instance
     *
     * @return Hayttp
     */
    function hayttp()
    {
        return Hayttp::instance();
    }
}

if (!function_exists('hayttp_request')) {

    /**
     * Create a Hayttp request
     *
     * @param string $method
     * @param string $url
     *
     * @return ResponseContract
     */
    function hayttp_request($method, $url)
    {
        return hayttp()->createRequest($method, $url);
    }
}

if (!function_exists('hayttp_do')) {

    /**
     * Create a Hayttp and send a Http Request
     *
     * @param string $method
     * @param string $url
     * @param mixed  $data Data payload.
     *                     If $data is instance of SimpleXmlElement, it will be sent as application/xml payload
     *                     If $data is an array or StdClass, it will be sent as application/json payload
     *                     If $data is a scalar value, it will be sent as application/octet-stream
     *                     If $data is null, no body will be attached
     *
     * @return ResponseContract
     */
    function hayttp_do($method, $url, $data = null)
    {
        $request = hayttp_request($method, $url)->ensure2xx();

        if (is_a($data, 'SimpleXmlElement')) {
            return $request->sendXml();
        }

        if (is_array($data) || is_a($data, 'StdClass')) {
            return $request->sendJson($data);
        }

        if (is_scalar($data)) {
            return $request->sendRaw($data);
        }

        return $request->send();
    }
}

if (!function_exists('hayttp_get')) {

    /**
     * Execute a Hayttp »get« request.
     *
     * @param string $url
     *
     * @return ResponseContract
     */
    function hayttp_get($url)
    {
        return hayttp_do('get', $url);
    }
}

if (!function_exists('hayttp_post')) {

    /**
     * Execute a Hayttp »post« request.
     *
     * @param string $url
     * @param mixed  $data Data payload.
     *                     If $data is instance of SimpleXmlElement, it will be sent as application/xml payload
     *                     If $data is an array or StdClass, it will be sent as application/json payload
     *                     If $data is a scalar value, it will be sent as application/octet-stream
     *                     If $data is null, no body will be attached
     *
     * @return ResponseContract
     */
    function hayttp_post($url, $data = null)
    {
        return hayttp_do('post', $url, $data);
    }
}

if (!function_exists('hayttp_put')) {

    /**
     * Execute a Hayttp »put« request.
     *
     * @param string $url
     * @param mixed  $data Data payload.
     *                     If $data is instance of SimpleXmlElement, it will be sent as application/xml payload
     *                     If $data is an array or StdClass, it will be sent as application/json payload
     *                     If $data is a scalar value, it will be sent as application/octet-stream
     *                     If $data is null, no body will be attached
     *
     * @return ResponseContract
     */
    function hayttp_put($url, $data = null)
    {
        return hayttp_do('put', $url, $data);
    }
}

if (!function_exists('hayttp_patch')) {

    /**
     * Execute a Hayttp »patch« request.
     *
     * @param string $url
     * @param mixed  $data Data payload.
     *                     If $data is instance of SimpleXmlElement, it will be sent as application/xml payload
     *                     If $data is an array or StdClass, it will be sent as application/json payload
     *                     If $data is a scalar value, it will be sent as application/octet-stream
     *                     If $data is null, no body will be attached
     *
     * @return ResponseContract
     */
    function hayttp_patch($url, $data = null)
    {
        return hayttp_do('patch', $url, $data);
    }
}

if (!function_exists('hayttp_delete')) {

    /**
     * Execute a Hayttp »delete« request.
     *
     * @param string $url
     * @param mixed  $data Data payload.
     *                     If $data is instance of SimpleXmlElement, it will be sent as application/xml payload
     *                     If $data is an array or StdClass, it will be sent as application/json payload
     *                     If $data is a scalar value, it will be sent as application/octet-stream
     *                     If $data is null, no body will be attached
     *
     * @return ResponseContract
     */
    function hayttp_delete($url, $data = null)
    {
        return hayttp_do('delete', $url, $data);
    }
}

if (!function_exists('hayttp_head')) {

    /**
     * Execute a Hayttp »head« request.
     *
     * @param string $url
     *
     * @return ResponseContract
     */
    function hayttp_head($url)
    {
        return hayttp_do('head', $url);
    }
}

if (!function_exists('hayttp_options')) {

    /**
     * Execute a Hayttp »options« request.
     *
     * @param string $url
     *
     * @return ResponseContract
     */
    function hayttp_options($url)
    {
        return hayttp_do('options', $url);
    }
}
