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

use InvalidArgumentException;
use Mindy\Template\Finder\FinderInterface;
use Mindy\Template\Library\LibraryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Loader.
 */
class TemplateEngine implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var bool enable exception handler
     */
    public $exceptionHandler = false;

    /**
     * @var array
     */
    protected $options = [];
    /**
     * @var array|VariableProviderInterface[]
     */
    protected $variableProviders = [];
    /**
     * @var array
     */
    protected $paths = [];
    /**
     * @var array
     */
    protected $cache = [];
    /**
     * @var array
     */
    protected $libraries = [];
    /**
     * @var FinderInterface
     */
    protected $finder;
    /**
     * @var callable|string
     */
    protected $target;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var array
     */
    protected $helpers = [];
    /**
     * @var array
     */
    protected $globals = [];
    /**
     * @var bool
     */
    protected $autoescape = true;
    /**
     * @var int
     */
    protected $mode;

    /**
     * Loader constructor.
     *
     * @param FinderInterface $finder
     * @param string|callable $target
     * @param int             $mode
     * @param bool            $autoescape
     */
    public function __construct(FinderInterface $finder, $target, $mode = LoaderMode::RECOMPILE_NORMAL, bool $autoescape = true)
    {
        $this->finder = $finder;
        $this->target = is_callable($target) ? call_user_func($target) : $target;
        $this->mode = $mode;
        $this->autoescape = $autoescape;

        if (!is_dir($this->target)) {
            (new Filesystem())->mkdir($this->target);
        }
    }

    /**
     * @param VariableProviderInterface $variableProvider
     */
    public function addVariableProvider(VariableProviderInterface $variableProvider)
    {
        $this->variableProviders[] = $variableProvider;
    }

    /**
     * @param SyntaxError $exception
     *
     * @throws SyntaxError
     */
    protected function handleSyntaxError(SyntaxError $exception)
    {
        if ($this->exceptionHandler) {
            echo strtr(file_get_contents(__DIR__.'/templates/debug.html'), [
                '{exception}' => $exception->getMessage(),
                '{line}' => $exception->getToken()->getLine(),
                '{source}' => $this->finder->getContents($exception->getTemplateFile()),
                '{styles}' => implode('', [
                    file_get_contents(__DIR__.'/templates/core.css'),
                    file_get_contents(__DIR__.'/templates/exception.css'),
                ]),
                '{loader}' => $this,
            ]);
            die();
        }
        throw $exception;
    }

    /**
     * @param string $class
     *
     * @return string
     */
    protected function resolveCacheTemplatePath(string $class): string
    {
        return sprintf('%s/%s.php', $this->target, $class);
    }

    /**
     * @param string $template
     *
     * @return $this
     */
    public function compile(string $template)
    {
        $path = $this->finder->find($template);
        if (null === $path) {
            throw new RuntimeException(sprintf('%s is not a valid readable template', $template));
        }

        $class = ClassGenerator::generateClass($path);

        $target = $this->resolveCacheTemplatePath($class);

        if ($this->isNeedRecompile($target, $path)) {
            try {
                $this
                    ->createCompiler($this->finder->getContents($path))
                    ->compile($path, $target);
            } catch (SyntaxError $e) {
                $e->setTemplateFile($path);
                $this->handleSyntaxError($e->setMessage($path.': '.$e->getMessage()));
            }
        }

        return $this;
    }

    /**
     * @param string $cache
     * @param string $path
     *
     * @return bool
     */
    protected function isNeedRecompile(string $cache, string $path): bool
    {
        switch ($this->mode) {
            case LoaderMode::RECOMPILE_ALWAYS:
                return true;

            case LoaderMode::RECOMPILE_NEVER:
                return !file_exists($cache);

            case LoaderMode::RECOMPILE_NORMAL:
            default:
                return !file_exists($cache) || filemtime($cache) < $this->finder->lastModified($path);
        }
    }

    /**
     * @param string $content
     *
     * @return Lexer
     */
    protected function createLexer(string $content): Lexer
    {
        return new Lexer($content);
    }

    /**
     * @param string $content
     *
     * @return Parser
     */
    protected function createParser(string $content): Parser
    {
        $parser = new Parser($this->createLexer($content)->tokenize());
        $parser->setAutoEscape($this->autoescape);
        $parser->setLibraries($this->libraries);

        return $parser;
    }

    /**
     * @param string $content
     *
     * @return Compiler
     */
    protected function createCompiler(string $content): Compiler
    {
        return new Compiler($this->createParser($content)->parse());
    }

    /**
     * @param $template
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return TemplateInterface
     */
    public function load($template): TemplateInterface
    {
        if ($template instanceof Template) {
            return $template;
        }

        if (!is_string($template)) {
            throw new InvalidArgumentException('string expected');
        }

        if (isset($this->paths[$template])) {
            $path = $this->paths[$template];
        } else {
            $path = $this->finder->find($template);
            if (null === $path) {
                throw new RuntimeException(sprintf(
                    '%s is not a valid readable template',
                    $template
                ));
            }
            $this->paths[$template] = $path;
        }

        $class = ClassGenerator::generateClass($path);

        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        if (!class_exists($class, false)) {
            $target = $this->resolveCacheTemplatePath($class);

            if ($this->isNeedRecompile($target, $path)) {
                try {
                    $this
                        ->createCompiler($this->finder->getContents($path))
                        ->compile($path, $target);
                } catch (SyntaxError $e) {
                    $e->setTemplateFile($path);

                    $this->handleSyntaxError($e->setMessage($path.': '.$e->getMessage()));
                }
            }
            require_once $target;
        }

        return $this->cache[$class] = new $class($this, $this->helpers, $this->variableProviders);
    }

    /**
     * @param string $content
     *
     * @return TemplateInterface
     */
    public function loadFromString(string $content): TemplateInterface
    {
        $name = ClassGenerator::generateName($content);
        $class = ClassGenerator::generateClass($name);

        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        $target = $this->resolveCacheTemplatePath($class);

        try {
            $this
                ->createCompiler($content)
                ->compile($name, $target);
        } catch (SyntaxError $e) {
            $e->setTemplateFile($name);
            $this->handleSyntaxError($e->setMessage($name.': '.$e->getMessage()));
        }

        require_once $target;

        return $this->cache[$class] = new $class($this, $this->helpers, $this->variableProviders);
    }

    /**
     * @param string $template
     *
     * @return bool
     */
    public function isValid(string $template): bool
    {
        $path = $this->finder->find($template);
        if (null === $path) {
            throw new RuntimeException(sprintf(
                '%s is not a valid readable template',
                $template
            ));
        }

        try {
            $this->createCompiler($this->finder->getContents($path));
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error($e->getMessage());
            }

            return false;
        }

        return true;
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @return string
     */
    public function render(string $template, array $data = [])
    {
        return $this->load($template)->render($data);
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @return string
     */
    public function renderString(string $template, array $data = [])
    {
        return $this->loadFromString($template)->render($data);
    }

    /**
     * @param $name
     * @param $func
     *
     * @return $this
     */
    public function addHelper($name, $func)
    {
        $this->helpers[$name] = $func;

        return $this;
    }

    /**
     * @param LibraryInterface $library
     *
     * @return $this
     */
    public function addLibrary(LibraryInterface $library)
    {
        $this->libraries[] = $library;

        foreach ($library->getHelpers() as $name => $func) {
            $this->addHelper($name, $func);
        }

        return $this;
    }

    /**
     * @return FinderInterface
     */
    public function getFinder(): FinderInterface
    {
        return $this->finder;
    }
}
