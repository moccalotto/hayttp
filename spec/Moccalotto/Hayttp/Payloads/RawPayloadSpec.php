<?php

namespace spec\Moccalotto\Hayttp\Payloads;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RawPayloadSpec extends ObjectBehavior
{
    function it_is_initializable()
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
