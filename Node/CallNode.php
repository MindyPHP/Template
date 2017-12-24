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

class CallNode extends Node
{
    private $module;
    private $name;
    private $args;
    private $block;

    public function __construct($module, $name, $args, $block, $line)
    {
        parent::__construct($line);
        $this->module = $module;
        $this->name = $name;
        $this->args = $args;
        $this->block = $block;
    }

    public function compile(CompilerInterface $compiler, $indent = 0)
    {
        $compiler->raw(
            'echo $this->expandMacro(\''.$this->module.'\', \''.$this->name.
            '\', [', $indent
        );

        foreach ($this->args as $key => $val) {
            $compiler->raw("'$key' => ");
            $val->compile($compiler);
            $compiler->raw(',');
        }

        $compiler->raw('], $context, $macros, $imports, function($context) {'."\n");
        if (isset($this->block)) {
            $this->block->compile($compiler, $indent + 1);
        }
        $compiler->raw('});'."\n", $indent);
    }
}
