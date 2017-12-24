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

abstract class Template implements TemplateInterface
{
    protected $loader;
    protected $helpers = [];
    protected $variableProviders = [];
    protected $parent;
    protected $blocks = [];
    protected $macros = [];
    protected $imports = [];
    protected $stack;

    /**
     * Template constructor.
     *
     * @param TemplateEngine $loader
     * @param array          $helpers
     * @param array          $variableProviders
     */
    public function __construct(TemplateEngine $loader, array $helpers = [], array $variableProviders = [])
    {
        $this->loader = $loader;
        $this->helpers = $helpers;
        $this->variableProviders = $variableProviders;
        $this->parent = null;
        $this->blocks = [];
        $this->macros = [];
        $this->imports = [];
        $this->stack = [];
    }

    /**
     * @param string $template
     *
     * @return TemplateInterface
     */
    public function loadExtends(string $template)
    {
        try {
            return $this->loader->load($template);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                'error extending %s (%s) from %s line %d',
                var_export($template, true), $e->getMessage(), static::NAME,
                $this->getLineTrace($e)
            ));
        }
    }

    /**
     * @param string $template
     *
     * @return TemplateInterface
     */
    public function loadInclude(string $template)
    {
        try {
            return $this->loader->load($template);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                'error including %s (%s) from %s line %d',
                var_export($template, true), $e->getMessage(), static::NAME,
                $this->getLineTrace($e)
            ));
        }
    }

    /**
     * @param string $template
     *
     * @return string
     */
    public function loadImport(string $template)
    {
        try {
            return $this->loader->load($template)->getMacros();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                'error importing %s (%s) from %s line %d',
                var_export($template, true), $e->getMessage(), static::NAME,
                $this->getLineTrace($e)
            ));
        }
    }

    /**
     * @param $name
     * @param $context
     * @param $blocks
     * @param $macros
     * @param $imports
     *
     * @return string|null
     */
    public function displayBlock($name, $context, $blocks, $macros, $imports)
    {
        $blocks = $blocks + $this->blocks;
        $macros = $macros + $this->macros;
        $imports = $imports + $this->imports;

        if (isset($blocks[$name]) && is_callable($blocks[$name])) {
            return call_user_func(
                $blocks[$name], $context, $blocks, $macros, $imports
            );
        } else {
            return null;
        }
    }

    /**
     * @param $name
     * @param $context
     * @param $blocks
     * @param $macros
     * @param $imports
     *
     * @return mixed
     */
    public function displayParent($name, $context, $blocks, $macros, $imports)
    {
        $parent = $this;
        while ($parent = $parent->parent) {
            if (isset($parent->blocks[$name]) &&
                is_callable($parent->blocks[$name])) {
                return call_user_func($parent->blocks[$name], $context, $blocks,
                    $macros, $imports);
            }
        }
    }

    /**
     * @param $module
     * @param $name
     * @param $params
     * @param $context
     * @param $macros
     * @param $imports
     * @param $block
     *
     * @return mixed
     */
    public function expandMacro($module, $name, $params, $context, $macros, $imports, $block)
    {
        $macros = $macros + $this->macros;
        $imports = $imports + $this->imports;

        if (isset($module) && isset($imports[$module])) {
            $macros = $macros + $imports[$module];
        }

        if (isset($macros[$name]) && is_callable($macros[$name])) {
            return call_user_func($macros[$name], $params, $context, $macros, $imports, $block);
        }

        return null;
    }

    /**
     * @param $context
     * @param $name
     *
     * @return $this
     */
    public function pushContext(&$context, $name)
    {
        if (!array_key_exists($name, $this->stack)) {
            $this->stack[$name] = [];
        }
        array_push($this->stack[$name], isset($context[$name]) ?
            $context[$name] : null
        );

        return $this;
    }

    /**
     * @param $context
     * @param $name
     *
     * @return $this
     */
    public function popContext(&$context, $name)
    {
        if (!empty($this->stack[$name])) {
            $context[$name] = array_pop($this->stack[$name]);
        }

        return $this;
    }

    /**
     * @param \Exception|null $e
     * @return null
     */
    public function getLineTrace(\Exception $e = null)
    {
        if (!isset($e)) {
            $e = new \Exception();
        }

        $lines = static::$lines;

        $file = get_class($this).'.php';

        foreach ($e->getTrace() as $trace) {
            if (isset($trace['file']) && basename($trace['file']) == $file) {
                $line = $trace['line'];

                return isset($lines[$line]) ? $lines[$line] : null;
            }
        }

        return null;
    }

    /**
     * @param $name
     * @param array $args
     *
     * @return mixed
     */
    public function helper($name, $args = [])
    {
        $args = func_get_args();
        $name = array_shift($args);
        if (isset($this->helpers[$name]) && is_callable($this->helpers[$name])) {
            return call_user_func_array($this->helpers[$name], $args);
        } elseif (($helper = [Helper::class, $name]) && is_callable($helper)) {
            return call_user_func_array($helper, $args);
        }

        throw new \RuntimeException(sprintf('undefined helper "%s" in %s line %d', $name, static::NAME, $this->getLineTrace()));
    }

    /**
     * @param array $context
     * @param array $blocks
     * @param array $macros
     * @param array $imports
     *
     * @return mixed
     */
    abstract public function display($context = [], $blocks = [], $macros = [], $imports = []);

    /**
     * {@inheritdoc}
     */
    public function render(array $context = [], array $blocks = [], array $macros = [], array $imports = []): string
    {
        ob_start();
        $this->display($this->mergeContext($context), $blocks, $macros);

        return ob_get_clean();
    }

    /**
     * @param array $context
     *
     * @return array
     */
    protected function mergeContext($context = [])
    {
        foreach ($this->variableProviders as $variableProvider) {
            $context = array_merge($context, $variableProvider->getData());
        }

        return array_merge($context, ['__context' => array_keys($context)]);
    }

    /**
     * @param $context
     * @param $seq
     *
     * @return Helper\ContextIterator
     */
    public function iterate($context, $seq)
    {
        return new Helper\ContextIterator($seq, isset($context['loop']) ?
            $context['loop'] : null);
    }

    /**
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * @return array
     */
    public function getMacros()
    {
        return $this->macros;
    }

    /**
     * @return array
     */
    public function getImports(): array
    {
        return $this->imports;
    }

    /**
     * @param $obj
     * @param $attr
     * @param array $args
     *
     * @return mixed|null
     */
    public function getAttr($obj, $attr, $args = [])
    {
        if (is_array($obj)) {
            if (isset($obj[$attr])) {
                if ($obj[$attr] instanceof \Closure) {
                    if (is_array($args)) {
                        array_unshift($args, $obj);
                    } else {
                        $args = [$obj];
                    }

                    return call_user_func_array($obj[$attr], $args);
                }

                return $obj[$attr];
            }

            return null;
        } elseif (is_object($obj)) {
            if (is_array($args)) {
                $callable = [$obj, $attr];

                return is_callable($callable) ?
                    call_user_func_array($callable, $args) : null;
            }
            $members = array_keys(get_object_vars($obj));
            $methods = get_class_methods(get_class($obj));
            if (in_array($attr, $members)) {
                return @$obj->$attr;
            } elseif (in_array('__get', $methods)) {
                return $obj->__get($attr);
            }
            $callable = [$obj, $attr];

            return is_callable($callable) ?
                call_user_func($callable) : null;
        }

        return null;
    }

    /**
     * @param $obj
     * @param $attrs
     * @param $value
     */
    public function setAttr(&$obj, $attrs, $value)
    {
        if (empty($attrs)) {
            $obj = $value;

            return;
        }
        $attr = array_shift($attrs);
        if (is_object($obj)) {
            $class = get_class($obj);
            $members = array_keys(get_object_vars($obj));
            if (!in_array($attr, $members)) {
                if (empty($attrs) && method_exists($obj, '__set')) {
                    $obj->__set($attr, $value);

                    return;
                } elseif (property_exists($class, $attr)) {
                    throw new \RuntimeException(
                        "inaccessible '$attr' object attribute"
                    );
                }
                if ($attr === null || $attr === false || $attr === '') {
                    if ($attr === null) {
                        $token = 'null';
                    }
                    if ($attr === false) {
                        $token = 'false';
                    }
                    if ($attr === '') {
                        $token = 'empty string';
                    }
                    throw new \RuntimeException(sprintf(
                        'invalid object attribute (%s) in %s line %d',
                        $token,
                        static::NAME,
                        $this->getLineTrace()
                    ));
                }
                $obj->{$attr} = null;
            }
            if (!isset($obj->$attr)) {
                $obj->$attr = null;
            }
            $this->setAttr($obj->$attr, $attrs, $value);
        } else {
            if (!is_array($obj)) {
                $obj = [];
            }
            $this->setAttr($obj[$attr], $attrs, $value);
        }
    }
}
