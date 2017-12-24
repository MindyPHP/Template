<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Expression;

use Mindy\Template\CompilerInterface;

/**
 * Class FilterExpression.
 */
class FilterExpression extends Expression
{
    private $node;
    private $filters = [];

    public function __construct($node, $filters, $line)
    {
        parent::__construct($line);
        $this->node = $node;
        $this->filters = $filters;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function appendFilter($filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function prependFilter($filter)
    {
        array_unshift($this->filters, $filter);

        return $this;
    }

    public function compile(CompilerInterface $compiler, $indent = 0)
    {
        $stack = [];
        for ($i = count($this->filters) - 1; $i >= 0; --$i) {
            list($name, $arguments) = $this->filters[$i];
            $compiler->raw('$this->helper(\''.$name.'\', ');
            $stack[] = $arguments;
        }
        $this->node->compile($compiler);
        foreach (array_reverse($stack) as $i => $arguments) {
            foreach ($arguments as $arg) {
                $compiler->raw(', ');
                $arg->compile($compiler);
            }
            $compiler->raw(')');
        }
    }
}
