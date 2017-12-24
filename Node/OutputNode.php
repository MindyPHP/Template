<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Node;

use Mindy\Template\CompilerInterface;
use Mindy\Template\Expression\FilterExpression;

/**
 * Class OutputNode.
 */
class OutputNode extends Node
{
    protected $expr;

    public function __construct($expr, $line)
    {
        parent::__construct($line);
        $this->expr = $expr;
    }

    /**
     * @param array $filters
     * @param array $filterNames
     * @return bool
     */
    protected function isNeedEscape(array $filters, array $filterNames): bool
    {
        foreach ($filters as $filter) {
            list($name, ) = $filter;
            if (in_array($name, $filterNames)) {
                return false;
            }
        }

        return true;
    }

    public function compile(CompilerInterface $compiler, $indent = 0)
    {
        $escape = true;
        if ($this->expr instanceof FilterExpression) {
            $escape = $this->isNeedEscape($this->expr->getFilters(), ['raw', 'safe']);
        }

        $compiler->addTraceInfo($this, $indent);

        if ($escape) {
            $compiler->raw('echo $this->helper(\'escape\', ', $indent);
            $this->expr->compile($compiler);
            $compiler->raw(");\n");
        } else {
            $compiler->raw('echo ', $indent);
            $this->expr->compile($compiler);
            $compiler->raw(";\n");
        }
    }
}
