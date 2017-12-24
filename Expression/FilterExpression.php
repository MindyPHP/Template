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
    protected $node;
    protected $filters;

    public function __construct($node, $filters, $line)
    {
        parent::__construct($line);
        $this->node = $node;
        $this->filters = $filters;
    }

    public function isRaw()
    {
        return in_array('raw', $this->filters) || in_array('safe', $this->filters);
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

    protected function isNeedEscape(array $saveFilterNames): bool
    {
        foreach ($this->filters as $i => $filter) {
            if (in_array($filter[0], $saveFilterNames)) {
                return false;
            }
        }

        return true;
    }

    public function compile(CompilerInterface $compiler, $indent = 0)
    {
        static $saveFilterNames = ['raw', 'safe'];

        $postponed = [];

        if ($this->isNeedEscape($saveFilterNames)) {
            $this->appendFilter(['escape', []]);
        }

        for ($i = count($this->filters) - 1; $i >= 0; --$i) {
            list($name, $arguments) = $this->filters[$i];
            if (in_array($name, $saveFilterNames)) {
                continue;
            }
            $compiler->raw('$this->helper(\''.$name.'\', ');
            $postponed[] = $arguments;
        }

        $this->node->compile($compiler);

        foreach (array_reverse($postponed) as $arguments) {
            foreach ($arguments as $arg) {
                $compiler->raw(', ');
                $arg->compile($compiler);
            }
            $compiler->raw(')');
        }
    }
}
