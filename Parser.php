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

use Mindy\Template\Expression\AddExpression;
use Mindy\Template\Expression\AndExpression;
use Mindy\Template\Expression\ArrayExpression;
use Mindy\Template\Expression\AttributeExpression;
use Mindy\Template\Expression\CompareExpression;
use Mindy\Template\Expression\ConcatExpression;
use Mindy\Template\Expression\ConditionalExpression;
use Mindy\Template\Expression\ConstantExpression;
use Mindy\Template\Expression\DivExpression;
use Mindy\Template\Expression\Expression;
use Mindy\Template\Expression\FilterExpression;
use Mindy\Template\Expression\FunctionCallExpression;
use Mindy\Template\Expression\InclusionExpression;
use Mindy\Template\Expression\JoinExpression;
use Mindy\Template\Expression\ModExpression;
use Mindy\Template\Expression\MulExpression;
use Mindy\Template\Expression\NameExpression;
use Mindy\Template\Expression\NegExpression;
use Mindy\Template\Expression\NotExpression;
use Mindy\Template\Expression\OrExpression;
use Mindy\Template\Expression\PosExpression;
use Mindy\Template\Expression\StringExpression;
use Mindy\Template\Expression\SubExpression;
use Mindy\Template\Expression\XorExpression;
use Mindy\Template\Library\LibraryInterface;
use Mindy\Template\Node\BlockDisplayNode;
use Mindy\Template\Node\BlockNode;
use Mindy\Template\Node\BreakNode;
use Mindy\Template\Node\CallNode;
use Mindy\Template\Node\ContinueNode;
use Mindy\Template\Node\ExtendsNode;
use Mindy\Template\Node\ForNode;
use Mindy\Template\Node\IfNode;
use Mindy\Template\Node\ImportNode;
use Mindy\Template\Node\IncludeNode;
use Mindy\Template\Node\MacroNode;
use Mindy\Template\Node\Node;
use Mindy\Template\Node\NodeList;
use Mindy\Template\Node\OutputNode;
use Mindy\Template\Node\ParentNode;
use Mindy\Template\Node\RawNode;
use Mindy\Template\Node\SetNode;
use Mindy\Template\Node\SpacelessNode;
use Mindy\Template\Node\TextNode;
use Mindy\Template\Node\YieldNode;

/**
 * Class Parser.
 */
class Parser
{
    /**
     * @var TokenStream
     */
    private $stream;
    /**
     * @var null
     */
    private $extends = null;
    /**
     * @var array
     */
    private $blocks = [];
    /**
     * @var array
     */
    private $currentBlock = [];
    /**
     * @var array
     */
    private $tags = [];
    /**
     * @var int
     */
    private $inForLoop = 0;
    /**
     * @var array
     */
    private $macros = [];
    /**
     * @var bool
     */
    private $inMacro = false;
    /**
     * @var array
     */
    private $imports = [];

    /**
     * Parser constructor.
     * @param TokenStream $stream
     */
    public function __construct(TokenStream $stream)
    {
        $this->stream = $stream;

        $this->tags = [
            'if' => 'parseIf',
            'for' => 'parseFor',
            'break' => 'parseBreak',
            'continue' => 'parseContinue',
            'extends' => 'parseExtends',
            'set' => 'parseSet',
            'block' => 'parseBlock',
            'parent' => 'parseParent',
            'macro' => 'parseMacro',
            'call' => 'parseCall',
            'yield' => 'parseYield',
            'import' => 'parseImport',
            'include' => 'parseInclude',
            'spaceless' => 'parseSpaceless',
        ];
    }

    /**
     * @param LibraryInterface[] $libraries
     *
     * @return $this
     */
    public function setLibraries(array $libraries)
    {
        foreach ($libraries as $library) {
            $library->setParser($this);
            $library->setStream($this->stream);
            $this->tags = array_merge($this->tags, $library->getTags());
        }
        return $this;
    }

    public function parse($path, $class): Module
    {
        $body = $this->subparse();
        return new Module(
            $path,
            $class,
            $this->extends,
            $this->imports,
            $this->blocks,
            $this->macros,
            $body
        );
    }

    private function subparse($test = null): NodeList
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $nodes = [];
        while (!$this->stream->isEOS()) {
            switch ($this->stream->getCurrentToken()->getType()) {
                case Token::TEXT:
                    $token = $this->stream->next();
                    $nodes[] = new TextNode($token->getValue(), $token->getLine());
                    break;

                case Token::BLOCK_BEGIN:
                    $this->stream->next();
                    $token = $this->stream->getCurrentToken();
                    if ($token->getType() !== Token::NAME) {
                        throw new SyntaxError(
                            sprintf(
                                'unexpected "%s", expecting a valid tag',
                                str_replace("\n", '\n', $token->getValue())
                            ),
                            $token
                        );
                    }
                    if (!is_null($test) && $token->test($test)) {
                        return new NodeList($nodes, $line);
                    }

                    if (!in_array($token->getValue(), array_keys($this->tags))) {
                        if (is_array($test)) {
                            $expecting = '"'.implode('" or "', $test).'"';
                        } elseif ($test) {
                            $expecting = '"'.$test.'"';
                        } else {
                            $expecting = 'a valid tag';
                        }
                        throw new SyntaxError(
                            sprintf(
                                'unexpected "%s", expecting %s',
                                str_replace("\n", '\n', $token->getValue()),
                                $expecting
                            ),
                            $token
                        );
                    }
                    $this->stream->next();
                    if (isset($this->tags[$token->getValue()]) &&
                        is_callable([$this, $this->tags[$token->getValue()]])
                    ) {
                        $node = call_user_func(
                            [$this, $this->tags[$token->getValue()]], $token
                        );
                    } else {
                        throw new SyntaxError(
                            sprintf(
                                'missing construct handler "%s"',
                                $token->getValue()
                            ),
                            $token
                        );
                    }
                    if (!is_null($node)) {
                        $nodes[] = $node;
                    }
                    break;

                case Token::OUTPUT_BEGIN:
                    $token = $this->stream->next();
                    $expr = $this->parseExpression();
                    $nodes[] = $this->parseIfModifier(
                        $token, new OutputNode($expr, $token->getLine())
                    );
                    $this->stream->expect(Token::OUTPUT_END);
                    break;

                case Token::RAW_BEGIN:
                    $token = $this->stream->next();
                    $expr = $this->parseExpression();
                    $nodes[] = $this->parseIfModifier(
                        $token, new RawNode($expr, $token->getLine())
                    );
                    $this->stream->expect(Token::RAW_END);
                    break;

                default:
                    throw new SyntaxError(
                        'parser ended up in unsupported state',
                        $this->stream->getCurrentToken()
                    );
            }
        }

        return new NodeList($nodes, $line);
    }

    private function parseSpaceless($token)
    {
        if ($this->stream->consume(Token::BLOCK_END)) {
            $nodeList = $this->subparse('endspaceless');
            if ($this->stream->next()->getValue() != 'endspaceless') {
                throw new SyntaxError('malformed spaceless statement', $token);
            }
        }
        $this->stream->expect(Token::BLOCK_END);
        return new SpacelessNode($nodeList, $token->getLine());
    }

    private function parseIf($token): Node
    {
        $line = $token->getLine();
        $expr = $this->parseExpression();
        $this->stream->expect(Token::BLOCK_END);
        $body = $this->subparse(['elif', 'elseif', 'else', 'endif']);
        $tests = [[$expr, $body]];
        $else = null;

        $end = false;
        while (!$end) {
            switch ($this->stream->next()->getValue()) {
                case 'elif':
                case 'elseif':
                    $expr = $this->parseExpression();
                    $this->stream->expect(Token::BLOCK_END);
                    $body = $this->subparse(['elseif', 'else', 'endif']);
                    $tests[] = [$expr, $body];
                    break;
                case 'else':
                    $this->stream->expect(Token::BLOCK_END);
                    $else = $this->subparse(['endif']);
                    break;
                case 'endif':
                    $this->stream->expect(Token::BLOCK_END);
                    $end = true;
                    break;
                default:
                    throw new SyntaxError('malformed if statement', $token);
                    break;
            }
        }

        return new IfNode($tests, $else, $line);
    }

    private function parseIfModifier($token, $node): Node
    {
        static $modifiers = ['if', 'unless'];

        if ($this->stream->test($modifiers)) {
            $statement = $this->stream->expect($modifiers)->getValue();
            $test_expr = $this->parseExpression();
            if ($statement == 'if') {
                $node = new IfNode(
                    [[$test_expr, $node]], null, $token->getLine()
                );
            } elseif ($statement == 'unless') {
                $node = new IfNode(
                    [[
                        new NotExpression($test_expr, $token->getLine()), $node,
                    ]], null, $token->getLine()
                );
            }
        }

        return $node;
    }

    private function parseFor($token): Node
    {
        ++$this->inForLoop;
        $line = $token->getLine();
        $key = null;
        $value = $this->stream->expect(Token::NAME)->getValue();
        if ($this->stream->consume(Token::OPERATOR, ',')) {
            $key = $value;
            $value = $this->stream->expect(Token::NAME)->getValue();
        }
        $this->stream->expect(Token::OPERATOR, 'in');
        $seq = $this->parseExpression();
        $this->stream->expect(Token::BLOCK_END);
        $body = $this->subparse(['else', 'endfor']);
        --$this->inForLoop;
        if ($this->stream->getCurrentToken()->getValue() == 'else') {
            $this->stream->next();
            $this->stream->expect(Token::BLOCK_END);
            $else = $this->subparse('endfor');
            if ($this->stream->getCurrentToken()->getValue() != 'endfor') {
                throw new SyntaxError('malformed for statement', $token);
            }
        } elseif ($this->stream->getCurrentToken()->getValue() == 'endfor') {
            $else = null;
        } else {
            throw new SyntaxError('malformed for statement', $token);
        }
        $this->stream->next();
        $this->stream->expect(Token::BLOCK_END);

        return new ForNode($seq, $key, $value, $body, $else, $line);
    }

    private function parseBreak($token): Node
    {
        if (!$this->inForLoop) {
            throw new SyntaxError('unexpected break, not in for loop', $token);
        }
        $node = $this->parseIfModifier(
            $token, new BreakNode($token->getLine())
        );
        $this->stream->expect(Token::BLOCK_END);

        return $node;
    }

    private function parseContinue($token): Node
    {
        if (!$this->inForLoop) {
            throw new SyntaxError(
                'unexpected continue, not in for loop', $token
            );
        }
        $node = $this->parseIfModifier(
            $token, new ContinueNode($token->getLine())
        );
        $this->stream->expect(Token::BLOCK_END);

        return $node;
    }

    private function parseExtends($token)
    {
        if (!is_null($this->extends)) {
            throw new SyntaxError('multiple extends tags', $token);
        }

        if (!empty($this->currentBlock)) {
            throw new SyntaxError(
                'cannot declare extends inside blocks', $token
            );
        }

        if ($this->inMacro) {
            throw new SyntaxError(
                'cannot declare extends inside macros', $token
            );
        }

        $parent = $this->parseExpression();
        $params = null;

        if ($this->stream->consume(Token::NAME, 'with')) {
            $this->stream->expect(Token::OPERATOR, '[');
            $params = $this->parseArrayExpression();
            $this->stream->expect(Token::OPERATOR, ']');
        }

        $this->extends = $this->parseIfModifier(
            $token, new ExtendsNode($parent, $params, $token->getLine())
        );

        $this->stream->expect(Token::BLOCK_END);

        return null;
    }

    private function parseSet($token): Node
    {
        $attrs = [];
        $name = $this->stream->expect(Token::NAME)->getValue();
        while (!$this->stream->test(Token::OPERATOR, '=') &&
            !$this->stream->test(Token::BLOCK_END)
        ) {
            if ($this->stream->consume(Token::OPERATOR, '.')) {
                $attrs[] = $this->stream->expect(Token::NAME)->getValue();
            } else {
                $this->stream->expect(Token::OPERATOR, '[');
                $attrs[] = $this->parseExpression();
                $this->stream->expect(Token::OPERATOR, ']');
            }
        }
        if ($this->stream->consume(Token::OPERATOR, '=')) {
            $value = $this->parseExpression();
            $node = $this->parseIfModifier(
                $token, new SetNode($name, $attrs, $value, $token->getLine())
            );
            $this->stream->expect(Token::BLOCK_END);
        } else {
            $this->stream->expect(Token::BLOCK_END);
            $body = $this->subparse('endassign');
            if ($this->stream->next()->getValue() != 'endassign') {
                throw new SyntaxError('malformed set statement', $token);
            }
            $this->stream->expect(Token::BLOCK_END);
            $node = new SetNode($name, $attrs, $body, $token->getLine());
        }

        return $node;
    }

    private function parseBlock($token): Node
    {
        if ($this->inMacro) {
            throw new SyntaxError(
                'cannot declare blocks inside macros', $token
            );
        }
        $name = $this->stream->expect(Token::NAME)->getValue();
        if (isset($this->blocks[$name])) {
            throw new SyntaxError(
                sprintf('block "%s" already defined', $name),
                $token
            );
        }
        array_push($this->currentBlock, $name);
        if ($this->stream->consume(Token::BLOCK_END)) {
            $body = $this->subparse('endblock');
            if ($this->stream->next()->getValue() != 'endblock') {
                throw new SyntaxError('malformed block statement', $token);
            }
            $this->stream->consume(Token::NAME, $name);
        } else {
            $expr = $this->parseExpression();
            $body = new OutputNode($expr, $token->getLine());
        }
        $this->stream->expect(Token::BLOCK_END);
        array_pop($this->currentBlock);
        $this->blocks[$name] = new BlockNode($name, $body, $token->getLine());

        return new BlockDisplayNode($name, $token->getLine());
    }

    private function parseParent($token): Node
    {
        if ($this->inMacro) {
            throw new SyntaxError(
                'cannot call parent block inside macros', $token
            );
        }

        if (empty($this->currentBlock)) {
            throw new SyntaxError('parent must be inside a block', $token);
        }

        $node = $this->parseIfModifier(
            $token,
            new ParentNode($this->currentBlock[count($this->currentBlock) - 1],
                $token->getLine())
        );
        $this->stream->expect(Token::BLOCK_END);

        return $node;
    }

    private function parseMacro($token)
    {
        if (!empty($this->currentBlock)) {
            throw new SyntaxError(
                'cannot declare macros inside blocks', $token
            );
        }

        if ($this->inMacro) {
            throw new SyntaxError(
                'cannot declare macros inside another macro', $token
            );
        }

        $this->inMacro = true;
        $name = $this->stream->expect(Token::NAME)->getValue();
        if (isset($this->macros[$name])) {
            throw new SyntaxError(
                sprintf('macro "%s" already defined', $name),
                $token
            );
        }
        $args = [];
        if ($this->stream->consume(Token::OPERATOR, '(')) {
            while (!$this->stream->test(Token::OPERATOR, ')')) {
                if (!empty($args)) {
                    $this->stream->expect(Token::OPERATOR, ',');
                    if ($this->stream->test(Token::OPERATOR, ')')) {
                        break;
                    }
                }
                $key = $this->stream->expect(Token::NAME)->getValue();
                if ($this->stream->consume(Token::OPERATOR, '=')) {
                    $val = $this->parseLiteralExpression();
                } else {
                    $val = new ConstantExpression(null, $token->getLine());
                }
                $args[$key] = $val;
            }
            $this->stream->expect(Token::OPERATOR, ')');
        }
        $this->stream->expect(Token::BLOCK_END);
        $body = $this->subparse('endmacro');
        if ($this->stream->next()->getValue() != 'endmacro') {
            throw new SyntaxError('malformed macro statement', $token);
        }
        $this->stream->consume(Token::NAME, $name);
        $this->stream->expect(Token::BLOCK_END);
        $this->macros[$name] = new MacroNode(
            $name, $args, $body, $token->getLine()
        );
        $this->inMacro = false;
    }

    private function parseCall($token): Node
    {
        $module = null;
        $name = $this->stream->expect(Token::NAME)->getValue();
        if ($this->stream->consume(Token::OPERATOR, '.')) {
            $module = $name;
            $name = $this->stream->expect(Token::NAME)->getValue();
        }

        $args = [];

        if ($this->stream->consume(Token::OPERATOR, '(')) {
            while (!$this->stream->test(Token::OPERATOR, ')')) {
                if (!empty($args)) {
                    $this->stream->expect(Token::OPERATOR, ',');
                    if ($this->stream->test(Token::OPERATOR, ')')) {
                        break;
                    }
                }
                if ($this->stream->test(Token::NAME) &&
                    $this->stream->look()->test(Token::OPERATOR, '=')
                ) {
                    $key = $this->stream->expect(Token::NAME)->getValue();
                    $this->stream->expect(Token::OPERATOR, '=');
                    $val = $this->parseExpression();
                    $args[$key] = $val;
                } else {
                    $args[] = $this->parseExpression();
                }
            }
            $this->stream->expect(Token::OPERATOR, ')');
        }

        $body = null;

        if ($this->stream->consume(Token::NAME, 'with')) {
            $this->stream->expect(Token::BLOCK_END);
            $body = $this->subparse('endcall');
            if ($this->stream->next()->getValue() != 'endcall') {
                throw new SyntaxError('malformed call statement', $token);
            }
        }

        $this->stream->expect(Token::BLOCK_END);

        return new CallNode($module, $name, $args, $body, $token->getLine());
    }

    private function parseYield($token): Node
    {
        $args = [];

        if ($this->stream->consume(Token::OPERATOR, '(')) {
            while (!$this->stream->test(Token::OPERATOR, ')')) {
                if (!empty($args)) {
                    $this->stream->expect(Token::OPERATOR, ',');
                    if ($this->stream->test(Token::OPERATOR, ')')) {
                        break;
                    }
                }
                $key = $this->stream->expect(Token::NAME)->getValue();
                $this->stream->expect(Token::OPERATOR, '=');
                $val = $this->parseExpression();
                $args[$key] = $val;
            }
            $this->stream->expect(Token::OPERATOR, ')');
        }

        $this->stream->expect(Token::BLOCK_END);

        return new YieldNode($args, $token->getLine());
    }

    private function parseImport($token)
    {
        $import = $this->parseExpression();
        $this->stream->expect(Token::NAME, 'as');
        $module = $this->stream->expect(Token::NAME)->getValue();
        $this->stream->expect(Token::BLOCK_END);
        $this->imports[$module] = new ImportNode(
            $module, $import, $token->getLine()
        );
    }

    private function parseInclude($token): Node
    {
        $include = $this->parseExpression();
        $params = null;

        if ($this->stream->consume(Token::NAME, 'with')) {
            $this->stream->expect(Token::OPERATOR, '[');
            $params = $this->parseArrayExpression();
            $this->stream->expect(Token::OPERATOR, ']');
        }

        $node = $this->parseIfModifier(
            $token, new IncludeNode($include, $params, $token->getLine())
        );

        $this->stream->expect(Token::BLOCK_END);

        return $node;
    }

    private function parseExpression(): Expression
    {
        return $this->parseConditionalExpression();
    }

    private function parseConditionalExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $expr1 = $this->parseXorExpression();
        while ($this->stream->consume(Token::OPERATOR, '?')) {
            $expr2 = $this->parseOrExpression();
            $this->stream->expect(Token::OPERATOR, ':');
            $expr3 = $this->parseConditionalExpression();
            $expr1 = new ConditionalExpression($expr1, $expr2, $expr3, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $expr1;
    }

    private function parseXorExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseOrExpression();
        while ($this->stream->consume(Token::OPERATOR, 'xor')) {
            $right = $this->parseOrExpression();
            $left = new XorExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseOrExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseAndExpression();
        while ($this->stream->consume(Token::OPERATOR, 'or')) {
            $right = $this->parseAndExpression();
            $left = new OrExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseAndExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseNotExpression();
        while ($this->stream->consume(Token::OPERATOR, 'and')) {
            $right = $this->parseNotExpression();
            $left = new AndExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseNotExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        if ($this->stream->consume(Token::OPERATOR, 'not')) {
            $node = $this->parseNotExpression();

            return new NotExpression($node, $line);
        }

        return $this->parseInclusionExpression();
    }

    private function parseInclusionExpression(): Expression
    {
        static $operators = ['not', 'in'];

        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseCompareExpression();
        while ($this->stream->test(Token::OPERATOR, $operators)) {
            if ($this->stream->consume(Token::OPERATOR, 'not')) {
                $this->stream->expect(Token::OPERATOR, 'in');
                $right = $this->parseCompareExpression();
                $left = new NotExpression(
                    new InclusionExpression($left, $right, $line), $line
                );
            } else {
                $this->stream->expect(Token::OPERATOR, 'in');
                $right = $this->parseCompareExpression();
                $left = new InclusionExpression($left, $right, $line);
            }
        }

        return $left;
    }

    private function parseCompareExpression(): Expression
    {
        static $operators = [
            '!==', '===', '==', '!=', '<>', '<', '>', '>=', '<=',
        ];
        $line = $this->stream->getCurrentToken()->getLine();
        $expr = $this->parseConcatExpression();
        $ops = [];
        while ($this->stream->test(Token::OPERATOR, $operators)) {
            $ops[] = [
                $this->stream->next()->getValue(),
                $this->parseAddExpression(),
            ];
        }

        if (empty($ops)) {
            return $expr;
        }

        return new CompareExpression($expr, $ops, $line);
    }

    private function parseConcatExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseJoinExpression();
        while ($this->stream->consume(Token::OPERATOR, '~')) {
            $right = $this->parseJoinExpression();
            $left = new ConcatExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseJoinExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseAddExpression();
        while ($this->stream->consume(Token::OPERATOR, '..')) {
            $right = $this->parseAddExpression();
            $left = new JoinExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseAddExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseSubExpression();
        while ($this->stream->consume(Token::OPERATOR, '+')) {
            $right = $this->parseSubExpression();
            $left = new AddExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseSubExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseMulExpression();
        while ($this->stream->consume(Token::OPERATOR, '-')) {
            $right = $this->parseMulExpression();
            $left = new SubExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseMulExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseDivExpression();
        while ($this->stream->consume(Token::OPERATOR, '*')) {
            $right = $this->parseDivExpression();
            $left = new MulExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseDivExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseModExpression();
        while ($this->stream->consume(Token::OPERATOR, '/')) {
            $right = $this->parseModExpression();
            $left = new DivExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseModExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $left = $this->parseUnaryExpression();
        while ($this->stream->consume(Token::OPERATOR, '%')) {
            $right = $this->parseUnaryExpression();
            $left = new ModExpression($left, $right, $line);
            $line = $this->stream->getCurrentToken()->getLine();
        }

        return $left;
    }

    private function parseUnaryExpression(): Expression
    {
        if ($this->stream->test(Token::OPERATOR, ['-', '+'])) {
            switch ($this->stream->getCurrentToken()->getValue()) {
                case '-':
                    return $this->parseNegExpression();
                case '+':
                    return $this->parsePosExpression();
            }
        }

        return $this->parsePrimaryExpression();
    }

    private function parseNegExpression(): Expression
    {
        $token = $this->stream->next();
        $node = $this->parseUnaryExpression();

        return new NegExpression($node, $token->getLine());
    }

    private function parsePosExpression(): Expression
    {
        $token = $this->stream->next();
        $node = $this->parseUnaryExpression();

        return new PosExpression($node, $token->getLine());
    }

    private function parsePrimaryExpression(): Expression
    {
        $token = $this->stream->getCurrentToken();
        switch ($token->getType()) {
            case Token::CONSTANT:
            case Token::NUMBER:
            case Token::STRING:
                $node = $this->parseLiteralExpression();
                break;
            case Token::NAME:
                $this->stream->next();
                $node = new NameExpression($token->getValue(), $token->getLine());
                if ($this->stream->test(Token::OPERATOR, '(')) {
                    $node = $this->parseFunctionCallExpression($node);
                }
                break;
            default:
                if ($this->stream->consume(Token::OPERATOR, '[')) {
                    $node = $this->parseArrayExpression();
                    $this->stream->expect(Token::OPERATOR, ']');
                } elseif ($this->stream->consume(Token::OPERATOR, '(')) {
                    $node = $this->parseExpression();
                    $this->stream->expect(Token::OPERATOR, ')');
                } else {
                    throw new SyntaxError(
                        sprintf(
                            'unexpected "%s", expecting an expression',
                            str_replace("\n", '\n', $token->getValue())
                        ),
                        $token
                    );
                }
        }

        return $this->parsePostfixExpression($node);
    }

    private function parseLiteralExpression(): Expression
    {
        $token = $this->stream->getCurrentToken();
        switch ($token->getType()) {
            case Token::CONSTANT:
                $this->stream->next();
                switch ($token->getValue()) {
                    case 'true':
                        $node = new ConstantExpression(true, $token->getLine());
                        break;
                    case 'false':
                        $node = new ConstantExpression(false, $token->getLine());
                        break;
                    case 'null':
                        $node = new ConstantExpression(null, $token->getLine());
                        break;
                }
                break;
            case Token::NUMBER:
                $this->stream->next();
                if (preg_match('/\./', $token->getValue())) {
                    $node = new ConstantExpression(
                        floatval($token->getValue()), $token->getLine()
                    );
                } else {
                    $node = new ConstantExpression(
                        intval($token->getValue()), $token->getLine()
                    );
                }
                break;
            case Token::STRING:
                $this->stream->next();
                $node = new StringExpression(
                    strval($token->getValue()), $token->getLine()
                );
                break;
            default:
                throw new SyntaxError(
                    sprintf(
                        'unexpected "%s", expecting an expression',
                        str_replace("\n", '\n', $token->getValue())
                    ),
                    $token
                );
        }

        return $this->parsePostfixExpression($node);
    }

    private function parseFunctionCallExpression($node): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $this->stream->expect(Token::OPERATOR, '(');
        $args = [];
        while (!$this->stream->test(Token::OPERATOR, ')')) {
            if (!empty($args)) {
                $this->stream->expect(Token::OPERATOR, ',');
                if ($this->stream->test(Token::OPERATOR, ')')) {
                    break;
                }
            }
            $args[] = $this->parseExpression();
        }
        $this->stream->expect(Token::OPERATOR, ')');

        return new FunctionCallExpression($node, $args, $line);
    }

    private function parseArrayExpression(): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $elements = [];
        do {
            $token = $this->stream->getCurrentToken();
            if ($token->test(Token::OPERATOR, ']')) {
                break;
            }
            if ($token->test(Token::NAME) ||
                $token->test(Token::STRING) ||
                $token->test(Token::NUMBER)
            ) {
                if ($token->test(Token::NAME) ||
                    $token->test(Token::STRING)
                ) {
                    $key = new ConstantExpression(
                        strval($token->getValue()), $line
                    );
                } else {
                    if (preg_match('/\./', $token->getValue())) {
                        $key = new ConstantExpression(
                            floatval($token->getValue()), $line
                        );
                    } else {
                        $key = new ConstantExpression(
                            intval($token->getValue()), $line
                        );
                    }
                }
                $this->stream->next();
                if ($this->stream->consume(Token::OPERATOR, ['=>'])) {
                    $element = $this->parseExpression();
                    $elements[] = [$key, $element];
                } else {
                    $elements[] = $key;
                }
            } else {
                $elements[] = $this->parseExpression();
            }
            $this->stream->consume(Token::OPERATOR, ',');
        } while (!$this->stream->test(Token::OPERATOR, ']'));

        return new ArrayExpression($elements, $line);
    }

    private function parsePostfixExpression($node): Expression
    {
        $stop = false;
        while (!$stop &&
            $this->stream->getCurrentToken()->getType() == Token::OPERATOR
        ) {
            switch ($this->stream->getCurrentToken()->getValue()) {
                case '.':
                case '[':
                    $node = $this->parseAttributeExpression($node);
                    break;
                case '|':
                    $node = $this->parseFilterExpression($node);
                    break;
                default:
                    $stop = true;
                    break;
            }
        }

        return $node;
    }

    private function parseAttributeExpression($node): Expression
    {
        $token = $this->stream->getCurrentToken();
        if ($this->stream->consume(Token::OPERATOR, '.')) {
            $attr = new ConstantExpression(
                $this->stream->expect(Token::NAME)->getValue(),
                $token->getLine()
            );
        } else {
            $this->stream->expect(Token::OPERATOR, '[');
            $attr = $this->parseExpression();
            $this->stream->expect(Token::OPERATOR, ']');
        }

        $args = false;
        if ($this->stream->consume(Token::OPERATOR, '(')) {
            $args = [];
            while (!$this->stream->test(Token::OPERATOR, ')')) {
                if (count($args)) {
                    $this->stream->expect(Token::OPERATOR, ',');
                }
                $args[] = $this->parseExpression();
            }
            $this->stream->expect(Token::OPERATOR, ')');
        }

        return new AttributeExpression($node, $attr, $args, $token->getLine());
    }

    private function parseFilterExpression($node): Expression
    {
        $line = $this->stream->getCurrentToken()->getLine();
        $filters = [];
        while ($this->stream->test(Token::OPERATOR, '|')) {
            $this->stream->next();
            $token = $this->stream->expect(Token::NAME);

            $args = [];
            if ($this->stream->test(Token::OPERATOR, '(')) {
                $this->stream->next();
                while (!$this->stream->test(Token::OPERATOR, ')')) {
                    if (!empty($args)) {
                        $this->stream->expect(Token::OPERATOR, ',');
                        if ($this->stream->test(Token::OPERATOR, ')')) {
                            break;
                        }
                    }
                    $args[] = $this->parseExpression();
                }
                $this->stream->expect(Token::OPERATOR, ')');
            }

            $filters[] = [$token->getValue(), $args];
        }

        return new FilterExpression($node, $filters, $line);
    }
}
