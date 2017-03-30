<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace spec\Moccalotto\Hayttp\Engines;

use PhpSpec\ObjectBehavior;

/**
 * Test.
 *
 * @codingStandardsIgnoreStart
 */
class NativeEngineSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Moccalotto\Hayttp\Engines\NativeEngine');
    }

    public function it_implements_engine_contract()
    {
        $this->shouldHaveType('Moccalotto\Hayttp\Contracts\Engine');
    }
}
