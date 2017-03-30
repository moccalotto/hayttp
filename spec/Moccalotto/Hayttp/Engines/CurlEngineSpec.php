<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace spec\Moccalotto\Hayttp\Engines;

use PhpSpec\ObjectBehavior;

class CurlEngineSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Moccalotto\Hayttp\Engines\CurlEngine');
    }

    public function it_implements_engine_contract()
    {
        $this->shouldHaveType('Moccalotto\Hayttp\Contracts\Engine');
    }
}
