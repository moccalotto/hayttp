<?php

use Moccalotto\Hayttp\Hayttp;
use Moccalotto\Hayttp\Contracts\Response;

if (!function_exists('hayttp')) {

    /**
     * Create a Hayttp instance or perform a GET request.
     *
     * @param string|null $url If given, a GET request is made to the given URL and a Response is returned.
     *                         If not given, an instance of the Hayttp facade is returned.
     *
     * @return Hayttp|Response
     */
    function hayttp(string $url = null)
    {
        if ($url === null) {
            return Hayttp::instance();
        }

        return Hayttp::get($url)->send();
    }
}
