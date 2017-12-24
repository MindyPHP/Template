<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;

use Mindy\Template\Token;

return [
    new Token(Token::BLOCK_BEGIN, '{%', 1, 1),
    new Token(Token::NAME, 'include', 1, 4),
    new Token(Token::STRING, 'includes/partial.html', 1, 12),
    new Token(Token::BLOCK_END, '%}', 1, 36),
    new Token(Token::TEXT, "\n", 1, 38),

    new Token(Token::EOF, null, 2, 1),
];
