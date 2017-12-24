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

use ArrayIterator;
use Mindy\Template\Helper;
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
     *
     * @see http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *                     <b>Traversable</b>
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator();
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
            (new ContextIterator(new TestArrayIterator(), null))->sequence
        );
        $this->assertInstanceOf(
            \ArrayIterator::class,
            (new ContextIterator(new TestIteratorAggregate(), null))->sequence
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

    public function testHelperClass()
    {
        $this->assertFalse(Helper::method_exists(new \stdClass(), 'foobar'));
        $this->assertFalse(Helper::method_exists(null, 'foobar'));

        $this->assertSame('1,2,3', Helper::implode([1, 2, 3], ','));
        $this->assertSame('', Helper::implode(null, ','));
        $this->assertSame('1,2,3', Helper::join([1, 2, 3], ','));
        $this->assertSame('', Helper::join(null, ','));

        $this->assertSame(['1', '2', '3'], Helper::explode('1,2,3', ','));
        $this->assertSame([], Helper::explode(null, ','));

        $this->assertSame(2, Helper::abs(2.3));

        $this->assertTrue(Helper::startswith('Test', 'Tes'));
        $this->assertTrue(Helper::istartswith('Test', 'tes'));

        $this->assertTrue(Helper::contains('Test', 'Tes'));
        $this->assertTrue(Helper::icontains('Test', 'tes'));

        $this->assertSame('test', Helper::lower('Test'));
        $this->assertSame('TEST', Helper::upper('Test'));

        $this->assertSame('Test', Helper::capitalize('test'));
        $this->assertSame('Мир алло', Helper::capitalize('мир алло'));

        $this->assertSame('1', Helper::first('123'));
        $this->assertSame(1, Helper::first([1, 2, 3]));
        $this->assertNull(Helper::first());

        $this->assertSame('1', Helper::last('321'));
        $this->assertSame(1, Helper::last([3, 2, 1]));
        $this->assertNull(Helper::last());

        $this->assertTrue(Helper::is_empty());
        $this->assertTrue(Helper::is_empty([]));
        $this->assertTrue(Helper::is_empty(''));
        $this->assertTrue(Helper::is_empty(new ArrayIterator()));
        $this->assertTrue(Helper::is_empty(new TestIteratorAggregate()));
        $this->assertFalse(Helper::is_empty(0));

        $this->assertSame(0, Helper::length());
        $this->assertSame(1, Helper::length(1));
        $this->assertSame(1, Helper::length('1'));
        $this->assertSame(1, Helper::length([1]));
        $this->assertSame(1, Helper::length(new ArrayIterator([1])));
        $this->assertSame(0, Helper::length(new TestIteratorAggregate()));

        $this->assertFalse(Helper::is_even(1));
        $this->assertTrue(Helper::is_even(0));
        $this->assertTrue(Helper::is_even([]));
        $this->assertFalse(Helper::is_even([1]));
        $this->assertFalse(Helper::is_even(new \stdClass()));
        $this->assertTrue(Helper::is_even(new ArrayIterator([])));
        $this->assertFalse(Helper::is_even(new ArrayIterator([1])));

        $this->assertFalse(Helper::is_odd(2));
        $this->assertTrue(Helper::is_odd(5));
        $this->assertFalse(Helper::is_odd([]));
        $this->assertTrue(Helper::is_odd([1]));
        $this->assertFalse(Helper::is_odd(new \stdClass()));

        $this->assertSame(['foo'], Helper::keys(['foo' => 'bar']));
        $this->assertSame(['foo'], Helper::keys(new ArrayIterator(['foo' => 'bar'])));
        $this->assertSame([], Helper::keys(new \stdClass()));

        $this->assertSame([], Helper::to_array(new \stdClass()));
        $this->assertSame(1, Helper::toint('1'));
        $this->assertSame(1, Helper::to_int('1'));
        $this->assertSame('1', Helper::to_string(1));
        $this->assertSame(1.02, Helper::to_float('1.02'));

        $this->assertTrue(is_numeric(Helper::strtotime('tomorrow')));
        $this->assertTrue(is_numeric(Helper::time()));
        $this->assertTrue(is_string(Helper::date()));
        $this->assertTrue(is_string(Helper::date('tomorrow')));
        $this->assertSame('<pre>tomorrow</pre>', Helper::dump('tomorrow'));
        $this->assertSame([2, 3], Helper::slice([1, 2, 3], 1));
        $this->assertSame('23', Helper::slice('123', 1));
        $this->assertSame([], Helper::slice(new TestIteratorAggregate(), 1));
        $this->assertNull(Helper::slice(new \stdClass(), 1));

        $this->assertTrue(Helper::is_divisible_by(10, 1));
        $this->assertTrue(Helper::is_divisible_by(10, 2));
        $this->assertFalse(Helper::is_divisible_by(10, 3));
        $this->assertFalse(Helper::is_divisible_by(null, 3));
        $this->assertFalse(Helper::is_divisible_by(new \stdClass(), 3));
        $this->assertFalse(Helper::is_divisible_by(new \stdClass()));
        $this->assertFalse(Helper::is_divisible_by(10, 0));

        $this->assertSame('12', Helper::format("%s%s", 1, 2));
    }
}
