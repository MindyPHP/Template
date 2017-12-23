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

/**
 * Class Node.
 */
class Node
{
    /**
     * @var int
     */
    protected $line;

    /**
     * Node constructor.
     *
     * @param $line
     */
    public function __construct($line)
    {
        $this->line = $line;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param CompilerInterface $compiler
     * @param $indent
     */
    public function addTraceInfo(CompilerInterface $compiler, $indent)
    {
        $compiler->addTraceInfo($this, $indent);
    }

    /**
     * @param CompilerInterface $compiler
     * @param int               $indent
     */
    public function compile(CompilerInterface $compiler, $indent = 0)
    {
    }
}
