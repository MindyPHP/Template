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

class CommentNodeTest extends AbstractTemplateTestCase
{
    public function providerComment()
    {
        return [
            [
                '{% comment %}from russia with love!{% endcomment %}',
                '',
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerComment
     *
     * @param string $template
     * @param string $result
     * @param array  $data
     */
    public function testComment(string $template, string $result, array $data)
    {
        $this->assertSame(
            $this->templateEngine->renderString($template, $data),
            $result
        );
    }
}
