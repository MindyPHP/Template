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
    new Token(Token::OUTPUT_BEGIN, '{{', 1, 1),
    new Token(Token::OPERATOR, '(', 1, 4),
    new Token(Token::CONSTANT, 'true', 1, 5),
    new Token(Token::OPERATOR, 'and', 1, 10),
    new Token(Token::CONSTANT, 'false', 1, 14),
    new Token(Token::OPERATOR, ')', 1, 19),
    new Token(Token::OPERATOR, 'xor', 1, 21),
    new Token(Token::CONSTANT, 'false', 1, 25),
    new Token(Token::OUTPUT_END, '}}', 1, 31),
    new Token(Token::TEXT, "\n", 1, 33),

    new Token(Token::OUTPUT_BEGIN, '{{', 2, 1),
    new Token(Token::CONSTANT, 'true', 2, 4),
    new Token(Token::OPERATOR, 'and', 2, 9),
    new Token(Token::OPERATOR, '(', 2, 13),
    new Token(Token::CONSTANT, 'false', 2, 14),
    new Token(Token::OPERATOR, 'xor', 2, 20),
    new Token(Token::CONSTANT, 'false', 2, 24),
    new Token(Token::OPERATOR, ')', 2, 29),
    new Token(Token::OUTPUT_END, '}}', 2, 31),
    new Token(Token::TEXT, "\n", 2, 33),

    new Token(Token::OUTPUT_BEGIN, '{{', 3, 1),
    new Token(Token::CONSTANT, 'true', 3, 4),
    new Token(Token::OPERATOR, 'and', 3, 9),
    new Token(Token::CONSTANT, 'false', 3, 13),
    new Token(Token::OPERATOR, 'xor', 3, 19),
    new Token(Token::CONSTANT, 'false', 3, 23),
    new Token(Token::OUTPUT_END, '}}', 3, 29),
    new Token(Token::TEXT, "\n\n", 3, 31),

    new Token(Token::OUTPUT_BEGIN, '{{', 5, 1),
    new Token(Token::CONSTANT, 'true', 5, 4),
    new Token(Token::OPERATOR, 'or', 5, 9),
    new Token(Token::CONSTANT, 'false', 5, 12),
    new Token(Token::OPERATOR, 'xor', 5, 18),
    new Token(Token::CONSTANT, 'false', 5, 22),
    new Token(Token::OUTPUT_END, '}}', 5, 28),
    new Token(Token::TEXT, "\n", 5, 30),

    new Token(Token::OUTPUT_BEGIN, '{{', 6, 1),
    new Token(Token::CONSTANT, 'true', 6, 4),
    new Token(Token::OPERATOR, 'or', 6, 9),
    new Token(Token::OPERATOR, '(', 6, 12),
    new Token(Token::CONSTANT, 'false', 6, 13),
    new Token(Token::OPERATOR, 'xor', 6, 19),
    new Token(Token::CONSTANT, 'false', 6, 23),
    new Token(Token::OPERATOR, ')', 6, 28),
    new Token(Token::OUTPUT_END, '}}', 6, 30),
    new Token(Token::TEXT, "\n", 6, 32),

    new Token(Token::OUTPUT_BEGIN, '{{', 7, 1),
    new Token(Token::OPERATOR, '(', 7, 4),
    new Token(Token::CONSTANT, 'true', 7, 5),
    new Token(Token::OPERATOR, 'or', 7, 10),
    new Token(Token::CONSTANT, 'false', 7, 13),
    new Token(Token::OPERATOR, ')', 7, 18),
    new Token(Token::OPERATOR, 'xor', 7, 20),
    new Token(Token::CONSTANT, 'false', 7, 24),
    new Token(Token::OUTPUT_END, '}}', 7, 30),
    new Token(Token::TEXT, "\n\n", 7, 32),

    new Token(Token::OUTPUT_BEGIN, '{{', 9, 1),
    new Token(Token::CONSTANT, 'true', 9, 4),
    new Token(Token::OPERATOR, 'and', 9, 9),
    new Token(Token::OPERATOR, 'not', 9, 13),
    new Token(Token::CONSTANT, 'false', 9, 17),
    new Token(Token::OUTPUT_END, '}}', 9, 23),
    new Token(Token::TEXT, "\n", 9, 25),

    new Token(Token::OUTPUT_BEGIN, '{{', 10, 1),
    new Token(Token::OPERATOR, 'not', 10, 4),
    new Token(Token::CONSTANT, 'true', 10, 8),
    new Token(Token::OPERATOR, 'or', 10, 13),
    new Token(Token::CONSTANT, 'true', 10, 16),
    new Token(Token::OUTPUT_END, '}}', 10, 21),
    new Token(Token::TEXT, "\n", 10, 23),

    new Token(Token::EOF, null, 11, 1),
];