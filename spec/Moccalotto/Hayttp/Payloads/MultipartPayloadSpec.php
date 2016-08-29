<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace spec\Moccalotto\Hayttp\Payloads;

use PhpSpec\ObjectBehavior;

/**
 * Test.
 *
 * @codingStandardsIgnoreStart
 */
class MultipartPayloadSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Moccalotto\Hayttp\Payloads\MultipartPayload');
    }

    public function it_implements_contract()
    {
        $this->beConstructedWith('', 'text/plain');
        $this->shouldHaveType('Moccalotto\Hayttp\Contracts\Payload');
    }

    public function it_has_correct_content_type()
    {
        $this->contentType()->shouldStartWith('multipart/form-data');
        $this->contentType()->shouldBe(sprintf(
            'multipart/form-data; boundary=%s',
            $this->boundary()->getWrappedObject()
        ));
    }

    public function it_can_render_base()
    {
        $this->render()->shouldBe(sprintf(
            '--%s--%s',
            $this->boundary()->getWrappedObject(),
            "\r\n"
        ));
    }

    public function it_ca_add_a_field()
    {
        $rendered = $this->withField(
            'fieldName',
            'fieldContents',
            'fileName',
            'text/plain'
        )->render();

        $rendered->shouldContain('Content-Disposition:');
        $rendered->shouldContain('name="fieldName"');
        $rendered->shouldContain('fieldContents');
        $rendered->shouldContain('filename="fileName"');
        $rendered->shouldContain('Content-Type: text/plain');
    }

    public function it_can_add_a_field_without_filename()
    {
        $rendered = $this->withField(
            'fieldName',
            'fieldContents',
            null,
            null
        )->render();

        $rendered->shouldContain('Content-Disposition:');
        $rendered->shouldContain('name="fieldName"');
        $rendered->shouldContain('fieldContents');
        $rendered->shouldNotContain('filename');
        $rendered->shouldNotContain('Content-Type:');
    }
}
