<?php

namespace spec\Moccalotto\Hayttp;

use SimpleXmlElement;
use PhpSpec\ObjectBehavior;
use Moccalotto\Hayttp\Request;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;

class RequestSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith('post', 'https://example.org');
        $this->shouldHaveType(Request::class);
        $this->shouldHaveType(RequestContract::class);
    }

    public function it_posts_json()
    {
        $this->beConstructedThrough('post', ['https://example.org']);

        $data = ['this' => 'array', 'will' => 'be', 'conterted' => 'to', 'json' => 'object'];

        $req = $this->sendsJson($data);

        $req->shouldHaveType(RequestContract::class);

        $req->render()->shouldContain(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $req->render()->shouldContain('Content-Type: application/json');
    }

    public function it_posts_xml()
    {
        $this->beConstructedThrough('post', ['https://example.org']);

        $data = new SimpleXmlElement('<root></root>');

        $req = $this->sendsXml($data);

        $req->shouldHaveType(Request::class);

        $req->render()->shouldContain($data->asXml());

        $req->render()->shouldContain('Content-Type: application/xml');
    }
}
