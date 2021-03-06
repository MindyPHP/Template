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
 * Class ImportNode.
 */
class ImportNode extends Node
{
    protected $module;
    protected $import;

    public function __construct($module, Node $import, $line)
    {
        parent::__construct($line);
        $this->module = $module;
        $this->import = $import;
    }

    public function compile(CompilerInterface $compiler, $indent = 0)
    {
        $compiler->addTraceInfo($this, $indent);
        $compiler->raw("'$this->module' => ", $indent);
        $compiler->raw('$this->loadImport(');
        $this->import->compile($compiler);
        $compiler->raw("),\n");
    }
}
