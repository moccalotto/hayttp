<?php

namespace Moccalotto\Hayttp\Contracts;

interface Response
{
    /**
     * Get the entire response, including headers, as a string.
     *
     * @return string
     */
    public function render() : string;
}
