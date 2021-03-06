<?php

namespace Mindy\Template\Tests;

use Mindy\Template\Lexer;
use Mindy\Template\TokenStream;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function tokenProvider()
    {
        $paths = [];

        $dir = new \DirectoryIterator(realpath(__DIR__ . '/actual'));

        foreach ($dir as $file) {
            if ($file->isFile()) {
                $tokenFile = realpath(__DIR__ . '/tokens/' . $file->getBasename('.html') . '.php');
                if (is_readable($tokenFile)) {
                    $paths[] = [$file->getPathname(), $tokenFile];
                }
            }
        }

        return $paths;
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testTokenizeReturnsTokenStream($actual, $expected)
    {
        $lexer = new Lexer(file_get_contents($actual));
        $tokenStream = $lexer->tokenize();
        $this->assertTrue($tokenStream instanceof TokenStream);
        $this->assertEquals(include $expected, $tokenStream->getTokens());
    }
}

