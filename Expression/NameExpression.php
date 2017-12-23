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
 * Class NameExpression.
 */
class NameExpression extends Expression
{
    protected $name;

    public function __construct($name, $line)
    {
        parent::__construct($line);
        $this->name = $name;
    }

    public function raw(CompilerInterface $compiler, $indent = 0)
    {
        $compiler->raw($this->name, $indent);
    }

    public function repr(CompilerInterface $compiler, $indent = 0)
    {
        $compiler->repr($this->name, $indent);
    }

    public function compile(CompilerInterface $compiler, $indent = 0)
    {
        $compiler->raw('(array_key_exists(\''.$this->name.'\', $context) ? ');
        $compiler->raw('$context[\''.$this->name.'\'] : null)');
    }
}
