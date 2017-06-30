<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Mock;

use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * HTTP Mock server.
 */
class Route
{
    /**
     * @var array
     */
    protected $matches;

    /**
     * Constructor.
     *
     * @param array $matches
     */
    public function __construct($matches)
    {
        $this->matches = $matches;
    }

    public function get($key, $default = null)
    {
        return $this->matches[$key] ?? $default;
    }

    public function has($key)
    {
        return isset($this->matches[$key]);
    }

    public function assertHas($key)
    {
        PHPUnit::assertArrayHasKey($key, $this->matches);

        return $this;
    }

    public function assertRegExp($key, $regex)
    {
        $this->assertHas($key);
        PHPUnit::assertRegExp($regex, $this->get($key));

        return $this;
    }

    public function assertInteger($key)
    {
        return $this->assertRegExp($key, '/^\d+$/');
    }

    public function all() : array
    {
        return $this->matches;
    }
}
