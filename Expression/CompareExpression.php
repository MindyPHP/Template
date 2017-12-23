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
 * Class CompareExpression.
 */
class CompareExpression extends Expression
{
    protected $expr;
    protected $ops;

    public function __construct($expr, $ops, $line)
    {
        parent::__construct($line);
        $this->expr = $expr;
        $this->ops = $ops;
    }

    public function compile(CompilerInterface $compiler, $indent = 0)
    {
        $this->expr->compile($compiler);
        $i = 0;
        foreach ($this->ops as $op) {
            if ($i) {
                $compiler->raw(' && ($tmp'.$i);
            }
            list($op, $node) = $op;
            $compiler->raw(' '.($op == '=' ? '==' : $op).' ');
            $compiler->raw('($tmp'.++$i.' = ');
            $node->compile($compiler);
            $compiler->raw(')');
        }
        if ($i > 1) {
            $compiler->raw(str_repeat(')', $i - 1));
        }
    }
}
