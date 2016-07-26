<?php

namespace spec\Moccalotto\Hayttp;

use Moccalotto\Hayttp\Request;
use PhpSpec\ObjectBehavior;

class RequestSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith('post', 'https://example.org');
        $this->shouldHaveType(Request::class);
    }

    public function it_posts_json()
    {
        $this->beConstructedThrough('post', ['https://example.org']);
        $this->shouldHaveType(Request::class);

        $this->sendsJson(['this' => 'object', 'will' => 'be', 'converted' => 'to json'])->shouldHaveType(Request::class);
    }
}
