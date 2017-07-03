<?php

namespace spec\Moccalotto\Hayttp\Mock;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Test.
 *
 * @codingStandardsIgnoreStart
 */
class RouteSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([]);

        $this->shouldHaveType('Moccalotto\Hayttp\Mock\Route');
    }

    function it_can_fetch_a_parameter()
    {
        $this->beConstructedWith([
            'foo' => 'value',
        ]);

        $this->get('foo')->shouldBe('value');
    }

    function it_can_fetch_parameters_with_fallback_value()
    {
        $this->beConstructedWith([
            'foo' => 'value',
        ]);

        $this->get('bar')->shouldBe(null);
        $this->get('bar', 'default')->shouldBe('default');
    }

    function it_can_detect_if_parameters_are_present()
    {
        $this->beConstructedWith([
            'foo' => 'value',
        ]);

        $this->has('foo')->shouldBe(true);
        $this->has('bar')->shouldBe(false);
    }

    function it_can_assert_a_parameter_must_exist()
    {
        $this->beConstructedWith([
            'foo' => 'value',
        ]);

        $this->ensureHas('foo')->shouldHaveType('Moccalotto\Hayttp\Mock\Route');

        $this->shouldThrow('Moccalotto\Hayttp\Exceptions\RouteException')->during(
            'ensureHas',
            ['bar']
        );
    }
}
