<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use Ray\Aop\Exception\InvalidMatcherException;

class BuiltinMatcher extends AbstractMatcher
{
    /**
     * @var string
     */
    private $matcherName;

    /**
     * @var AbstractMatcher
     */
    private $matcher;

    /**
     * @throws \ReflectionException
     */
    public function __construct(string $matcherName, array $arguments)
    {
        parent::__construct();
        $this->matcherName = $matcherName;
        $this->arguments = $arguments;
        $matcherClass = 'Ray\Aop\Matcher\\' . ucwords($this->matcherName) . 'Matcher';
        $matcher = (new \ReflectionClass($matcherClass))->newInstance();
        if (! $matcher instanceof AbstractMatcher) {
            throw new InvalidMatcherException($matcherClass);
        }
        $this->matcher = $matcher;
    }

    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        return $this->matcher->matchesClass($class, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        return $this->matcher->matchesMethod($method, $arguments);
    }
}
