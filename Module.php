<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template;

use Mindy\Template\Node\ExtendsNode;
use Mindy\Template\Node\ImportNode;
use Mindy\Template\Node\Node;
use Mindy\Template\Node\NodeList;

/**
 * Class Module.
 */
class Module
{
    /**
     * @var ExtendsNode
     */
    protected $extends;
    /**
     * @var ImportNode[]
     */
    protected $imports;
    /**
     * @var CompilerInterface[]
     */
    protected $blocks;
    /**
     * @var CompilerInterface[]
     */
    protected $macros;
    /**
     * @var NodeList
     */
    protected $body;

    /**
     * Module constructor.
     *
     * @param Node|null $extends
     * @param array     $imports
     * @param array     $blocks
     * @param array     $macros
     * @param NodeList  $body
     */
    public function __construct(Node $extends = null, array $imports, array $blocks, array $macros, NodeList $body)
    {
        $this->extends = $extends;
        $this->imports = $imports;
        $this->blocks = $blocks;
        $this->macros = $macros;
        $this->body = $body;
    }

    /**
     * @param string            $module
     * @param CompilerInterface $compiler
     * @param int               $indent
     */
    public function compile(string $module, CompilerInterface $compiler, $indent = 0)
    {
        $name = ClassGenerator::generateName($module);
        $class = ClassGenerator::generateClass($module);

        $compiler->raw("<?php\n");
        $moduleName = trim(preg_replace('/(\s\s+|[\n\r])/', ' ', $module));
        $compiler->raw(
            '// '.md5($moduleName).' '.gmdate('Y-m-d H:i:s T', time()).
            "\n", $indent
        );
        $compiler->raw("\nuse \\Mindy\\Template\\Template;\n\n");
        $compiler->raw("class $class extends Template\n", $indent);
        $compiler->raw("{\n", $indent);

        $compiler->raw('const NAME = ', $indent + 1);
        $compiler->repr($name);
        $compiler->raw(";\n\n");

        $compiler->raw(
            'public function __construct($loader, $helpers = array(), $variablesProviders = array())'."\n",
            $indent + 1
        );
        $compiler->raw("{\n", $indent + 1);
        $compiler->raw(
            'parent::__construct($loader, $helpers, $variablesProviders);'."\n",
            $indent + 2
        );

        // blocks constructor
        if (!empty($this->blocks)) {
            $compiler->raw('$this->blocks = array('."\n", $indent + 2);
            foreach ($this->blocks as $name => $block) {
                $compiler->raw(
                    "'$name' => array(\$this, 'block_{$name}'),\n", $indent + 3
                );
            }
            $compiler->raw(");\n", $indent + 2);
        }

        // macros constructor
        if (!empty($this->macros)) {
            $compiler->raw('$this->macros = array('."\n", $indent + 2);
            foreach ($this->macros as $name => $macro) {
                $compiler->raw(
                    "'$name' => array(\$this, 'macro_{$name}'),\n", $indent + 3
                );
            }
            $compiler->raw(");\n", $indent + 2);
        }

        // imports constructor
        if (!empty($this->imports)) {
            $compiler->raw('$this->imports = array('."\n", $indent + 2);
            foreach ($this->imports as $import) {
                $import->compile($compiler, $indent + 3);
            }
            $compiler->raw(");\n", $indent + 2);
        }

        $compiler->raw("}\n\n", $indent + 1);

        $compiler->raw(
            'public function display'.
            '($context = array(), $blocks = array(), $macros = array(),'.
            ' $imports = array())'.
            "\n", $indent + 1
        );
        $compiler->raw("{\n", $indent + 1);

        // extends
        if ($this->extends) {
            $this->extends->compile($compiler, $indent + 2);
        }
        $this->body->compile($compiler, $indent + 2);
        $compiler->raw("}\n", $indent + 1);

        foreach ($this->blocks as $block) {
            $block->compile($compiler, $indent + 1);
        }

        foreach ($this->macros as $macro) {
            $macro->compile($compiler, $indent + 1);
        }

        // line trace info
        $compiler->raw("\n");
        $compiler->raw('protected static $lines = ', $indent + 1);
        $compiler->raw($compiler->getTraceInfo(true).";\n");

        $compiler->raw("}\n");
        $compiler->raw('// end of '.md5($moduleName)."\n");
    }
}
