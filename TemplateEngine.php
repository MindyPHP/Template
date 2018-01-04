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

use Mindy\Template\Finder\FinderInterface;
use Mindy\Template\Library\LibraryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class Loader.
 */
class TemplateEngine implements TemplateEngineInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const CLASS_PREFIX = '__MindyTemplate_';

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
     * @var array|LibraryInterface[]
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
     * @var int
     */
    protected $mode;

    /**
     * Loader constructor.
     *
     * @param FinderInterface|null $finder
     * @param string|callable      $target
     * @param int                  $mode
     * @param bool                 $exceptionHandler
     */
    public function __construct(FinderInterface $finder, $target, int $mode = LoaderMode::RECOMPILE_NORMAL, bool $exceptionHandler = false)
    {
        $this->finder = $finder;
        $this->target = is_callable($target) ? call_user_func($target) : $target;
        $this->mode = $mode;
        $this->exceptionHandler = $exceptionHandler;

        if (!is_dir($this->target)) {
            mkdir($this->target, 0777, true);
        }
    }

    /**
     * @param int $mode
     */
    public function setMode(int $mode)
    {
        if (false === in_array($mode, LoaderMode::getModes())) {
            throw new \LogicException(sprintf(
                'Unknown mode %s, available %s',
                $mode,
                implode(',', LoaderMode::getModes())
            ));
        }

        $this->mode = $mode;
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
     * @return string
     */
    protected function renderSyntaxError(SyntaxError $exception)
    {
        return strtr(file_get_contents(__DIR__.'/templates/exception.html'), [
            '{exception}' => $exception->getMessage(),
            '{line}' => $exception->getToken()->getLine(),
            '{source}' => $exception->getTemplateContent(),
            '{css}' => file_get_contents(__DIR__.'/templates/prism.css'),
            '{js}' => file_get_contents(__DIR__.'/templates/prism.js'),
        ]);
    }

    /**
     * @param string $class
     *
     * @return string
     */
    public function resolveCacheTemplatePath(string $class): string
    {
        return sprintf('%s/%s.php', $this->target, $class);
    }

    /**
     * @param string $cache
     * @param string $path
     *
     * @return bool
     */
    public function isNeedRecompile(string $cache, string $path): bool
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
        $parser->setLibraries($this->libraries);

        return $parser;
    }

    /**
     * @param string $content
     * @param string $path
     * @param string $class
     *
     * @return Compiler
     */
    protected function createCompiler(string $content, string $path, string $class): Compiler
    {
        return new Compiler($this->createParser($content)->parse($path, $class));
    }

    /**
     * @param $templatePath
     *
     * @return mixed|null|string
     */
    protected function resolveCachedTemplatePath($templatePath)
    {
        if (isset($this->paths[$templatePath])) {
            $path = $this->paths[$templatePath];
        } else {
            $path = $this->finder->find($templatePath);
            if (null === $path) {
                throw new RuntimeException(sprintf(
                    '%s is not a valid readable template',
                    $templatePath
                ));
            }
            $this->paths[$templatePath] = $path;
        }

        return $path;
    }

    /**
     * @param string $content
     * @param string $path
     * @param string $class
     *
     * @throws SyntaxError
     *
     * @return TemplateInterface
     */
    protected function compileAndSaveTemplate(string $content, string $path, string $class): TemplateInterface
    {
        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        if (!class_exists($class, false)) {
            $target = $this->resolveCacheTemplatePath($class);

            if ($this->isNeedRecompile($target, $path)) {
                $compiledTemplate = $this
                    ->createCompiler($content, $target, $class)
                    ->compile();

                file_put_contents($target, $compiledTemplate);
            }

            require_once $target;
        }

        return $this->cache[$class] = new $class(
            $this,
            $this->getHelpers(),
            $this->variableProviders
        );
    }

    /**
     * @param string $templatePath
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return TemplateInterface
     */
    public function load(string $templatePath): TemplateInterface
    {
        $path = $this->resolveCachedTemplatePath($templatePath);
        $class = $this->generateTemplateCacheClass($path);

        return $this->compileAndSaveTemplate(
            $this->finder->getContents($path),
            $this->resolveCacheTemplatePath($class),
            $class
        );
    }

    /**
     * @param string $content
     *
     * @return TemplateInterface
     */
    public function loadFromString(string $content): TemplateInterface
    {
        $class = $this->generateTemplateCacheClass($content);

        return $this->compileAndSaveTemplate(
            $content,
            $this->resolveCacheTemplatePath($class),
            $class
        );
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function generateTemplateCacheClass(string $string): string
    {
        return self::CLASS_PREFIX.hash('crc32', $string);
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @throws SyntaxError
     *
     * @return string
     */
    public function render(string $template, array $data = [])
    {
        try {
            return $this->load($template)->render($data);
        } catch (SyntaxError $e) {
            if ($this->exceptionHandler) {
                $templatePath = $this->finder->find($template);

                $e->setTemplateFile($templatePath);
                $e->setTemplateContent($this->finder->getContents($templatePath));

                return $this->renderSyntaxError($e);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @throws SyntaxError
     */
    public function stream(string $template, array $data = [])
    {
        echo $this->render($template, $data);
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @throws SyntaxError
     *
     * @return string
     */
    public function renderString(string $template, array $data = [])
    {
        try {
            return $this->loadFromString($template)->render($data);
        } catch (SyntaxError $e) {
            if ($this->exceptionHandler) {
                $e->setTemplateContent($template);

                return $this->renderSyntaxError($e);
            } else {
                throw $e;
            }
        }
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
     * @return array
     */
    public function getHelpers(): array
    {
        $libraryHelpers = [];
        foreach ($this->libraries as $library) {
            /** @var LibraryInterface $library */
            $libraryHelpers = array_merge($libraryHelpers, $library->getHelpers());
        }

        return $this->helpers;
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
