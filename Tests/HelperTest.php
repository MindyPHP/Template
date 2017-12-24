<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;

use ArrayIterator;
use Mindy\Template\Helper\ContextIterator;
use Mindy\Template\Helper\Cycler;
use Mindy\Template\Helper\RangeIterator;
use PHPUnit\Framework\TestCase;
use Traversable;

class TestArrayIterator extends ArrayIterator
{
}

class TestIteratorAggregate implements \IteratorAggregate
{
    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator([]);
    }
}

class HelperTest extends TestCase
{
    public function testContextIterator()
    {
        $this->assertInstanceOf(
            \ArrayIterator::class,
            (new ContextIterator(0, null))->sequence
        );
        $this->assertInstanceOf(
            \ArrayIterator::class,
            (new ContextIterator(new TestArrayIterator, null))->sequence
        );
        $this->assertInstanceOf(
            \ArrayIterator::class,
            (new ContextIterator(new TestIteratorAggregate, null))->sequence
        );
    }

    public function testCycler()
    {
        $cycler = new Cycler(range(1, 5));
        $this->assertInstanceOf(\ArrayIterator::class, $cycler->getIterator());
        $this->assertSame(0, $cycler->count());
        $this->assertSame(1, $cycler->next());
        $this->assertSame(1.0, $cycler->cycle());

        $cycler = new Cycler(range(1, 1));
        $this->assertSame(1, $cycler->random());
    }

    public function testRangeIterator()
    {
        $range = new RangeIterator(5, 1);
        $this->assertTrue($range->valid());
        $this->assertTrue($range->includes(2));
        $this->assertFalse($range->includes(123));

        $range = new RangeIterator(1, 5);
        $this->assertSame(4, $range->length());
        $this->assertTrue($range->valid());

        $this->assertSame(1, $range->key());
        $this->assertSame(1, $range->current());
        $this->assertSame(2, $range->next()->current());
        $this->assertSame(1, $range->rewind()->current());

        $this->assertTrue($range->includes(2));
        $this->assertFalse($range->includes(123));

        $range = new RangeIterator(1, 1);
        $this->assertSame(1, $range->random());
    }
}
