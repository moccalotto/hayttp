<?php

namespace Moccalotto\Hayttp\Contracts;

use Moccalotto\Hayttp\Response as Response;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

interface Engine
{
    public function send(RequestContract $request) : ResponseContract;
}
