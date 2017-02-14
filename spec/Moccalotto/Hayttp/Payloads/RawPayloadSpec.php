<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace spec\Moccalotto\Hayttp\Payloads;

use PhpSpec\ObjectBehavior;

/**
 * Test.
 *
 * @codingStandardsIgnoreStart
 */
class RawPayloadSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith('', 'text/plain');
        $this->shouldHaveType('Moccalotto\Hayttp\Payloads\RawPayload');
    }

    public function it_implements_contract()
    {
        $this->beConstructedWith('', 'text/plain');
        $this->shouldHaveType('Moccalotto\Hayttp\Contracts\Payload');
    }

    public function it_renders_contents()
    {
        $this->beConstructedWith('foo', 'text/plain');
        $this->render()->shouldBe('foo');
    }

    public function it_has_content_type()
    {
        $this->beConstructedWith('{}', 'application/json');
        $this->render()->shouldBe('{}');
        $this->contentType()->shouldBe('application/json');
    }
}
