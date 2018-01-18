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

use Countable;
use Mindy\Template\Helper\RangeIterator;
use Traversable;

/**
 * Class Helper.
 */
class Helper
{
    public static $encoding = 'UTF-8';

    public static function is_array($obj)
    {
        return is_array($obj);
    }

    public static function is_object($obj)
    {
        return is_object($obj);
    }

    public static function is_string($obj)
    {
        return is_string($obj);
    }

    public static function is_numeric($obj)
    {
        return is_numeric($obj);
    }

    public static function get_class($obj)
    {
        return get_class($obj);
    }

    public static function number_format($number , $decimals = 0 , $dec_point = '.' , $thousands_sep = ',')
    {
        return number_format((float)$number, $decimals, $dec_point, $thousands_sep);
    }

    public static function substr_count($obj, $needle)
    {
        return mb_substr_count((string)$obj, (string)$needle, self::$encoding);
    }

    public static function dirname($obj)
    {
        return dirname($obj);
    }

    public static function basename($obj)
    {
        return basename($obj);
    }

    public static function strtr($obj, array $params = [])
    {
        return strtr($obj, $params);
    }

    public static function method_exists($obj, $method)
    {
        if (is_object($obj)) {
            return method_exists($obj, $method);
        }

        return false;
    }

    public static function implode($obj = null, $glue = '')
    {
        return implode($glue, ($obj instanceof Traversable) ? iterator_to_array($obj) : (array) $obj);
    }

    /**
     * @param null $obj
     * @param string $glue
     * @return string
     * @deprecated since 4.0
     */
    public static function join($obj = null, $glue = '')
    {
        return self::implode($obj, $glue);
    }

    public static function explode($obj = null, $delimiter = ':')
    {
        if (is_null($obj)) {
            return [];
        }

        return explode($delimiter, (string) $obj);
    }

    public static function abs($obj = null)
    {
        return abs(intval($obj));
    }

    public static function slice($obj, $start, $length = null)
    {
        if ($obj instanceof Traversable) {
            $obj = iterator_to_array($obj);
        }

        if (is_array($obj)) {
            return array_slice($obj, $start, $length === null ? count($obj) : $length);
        } elseif (is_string($obj)) {
            return mb_substr($obj, $start, $length === null ? self::length($obj) : $length, self::$encoding);
        }

        return null;
    }

    public static function startswith($obj, $needle)
    {
        return mb_strpos((string) $obj, $needle, 0, self::$encoding) === 0;
    }

    public static function istartswith($obj, $needle)
    {
        return mb_strpos(self::lower($obj), self::lower($needle), 0, self::$encoding) === 0;
    }

    public static function contains($obj, $needle)
    {
        return mb_strpos((string) $obj, $needle, 0, self::$encoding) !== false;
    }

    public static function icontains($obj, $needle)
    {
        return mb_strpos(self::lower($obj), self::lower($needle), 0, self::$encoding) !== false;
    }

    public static function capitalize($obj)
    {
        return self::upper(
            mb_substr((string) $obj, 0, 1, self::$encoding)).
            self::lower(mb_substr((string) $obj, 1, self::length((string) $obj), self::$encoding)
        );
    }

    public static function cycle($obj = null)
    {
        $obj = ($obj instanceof Traversable) ? iterator_to_array($obj) : (array) $obj;

        return new Helper\Cycler((array) $obj);
    }

    public static function time($obj = null)
    {
        return time();
    }

    public static function date($obj = null, $format = 'Y-m-d H:m:s')
    {
        if (!is_numeric($obj) && is_string($obj)) {
            $obj = strtotime($obj);
        }

        return date($format, $obj ? $obj : time());
    }

    public static function strtotime($obj = null)
    {
        return strtotime((string) $obj);
    }

    public static function dump($obj = null)
    {
        return sprintf('<pre>%s</pre>', print_r($obj, true));
    }

    public static function e($obj = null, $force = false)
    {
        return self::escape($obj, $force);
    }

    public static function escape($obj = null, $force = false)
    {
        return htmlspecialchars((string) $obj, ENT_QUOTES, self::$encoding, $force);
    }

    public static function first($obj = null)
    {
        if (is_string($obj)) {
            return substr($obj, 0, 1);
        }
        $obj = $obj instanceof Traversable ? iterator_to_array($obj) : (array) $obj;
        $keys = array_keys($obj);
        if (count($keys)) {
            return $obj[current($keys)];
        }

        return null;
    }

    public static function format($obj, $args)
    {
        return call_user_func_array('sprintf', func_get_args());
    }

    public static function is_divisible_by($obj = null, $number = null)
    {
        if (!isset($number)) {
            return false;
        }
        if (!is_numeric($obj) || !is_numeric($number)) {
            return false;
        }
        if ($number == 0) {
            return false;
        }

        return fmod($obj, $number) == 0;
    }

    public static function is_empty($obj = null)
    {
        if (is_null($obj)) {
            return true;
        } elseif (is_array($obj) || is_string($obj) || $obj instanceof Countable) {
            return self::length($obj) == 0;
        } elseif ($obj instanceof Traversable) {
            return iterator_count($obj) === 0;
        }

        return false;
    }

    public static function is_even($obj = null)
    {
        if (is_scalar($obj) || is_null($obj)) {
            $obj = is_numeric($obj) ? intval($obj) : strlen($obj);
        } elseif (is_array($obj) || $obj instanceof Countable || $obj instanceof Traversable) {
            $obj = self::length($obj);
        } else {
            return false;
        }

        return abs($obj % 2) == 0;
    }

    public static function is_odd($obj = null)
    {
        if (is_scalar($obj) || is_null($obj)) {
            $obj = is_numeric($obj) ? intval($obj) : strlen($obj);
        } elseif (is_array($obj) || $obj instanceof Countable || $obj instanceof Traversable) {
            $obj = self::length($obj);
        } else {
            return false;
        }

        return abs($obj % 2) == 1;
    }

    public static function json_encode($obj = null)
    {
        return json_encode($obj, JSON_UNESCAPED_UNICODE);
    }

    public static function keys($obj = null)
    {
        if ($obj instanceof Traversable) {
            $obj = iterator_to_array($obj);
        }

        if (is_array($obj)) {
            return array_keys($obj);
        }

        return [];
    }

    public static function last($obj = null)
    {
        if (is_string($obj)) {
            return mb_substr($obj, -1, 1, self::$encoding);
        }

        $obj = ($obj instanceof Traversable) ? iterator_to_array($obj) : (array) $obj;
        $keys = array_keys($obj);
        if ($len = count($keys)) {
            return $obj[end($keys)];
        }

        return null;
    }

    public static function length($obj = null)
    {
        if (is_string($obj) || is_numeric($obj)) {
            return mb_strlen((string) $obj, self::$encoding);
        } elseif (is_array($obj) || ($obj instanceof Countable)) {
            return count($obj);
        } elseif ($obj instanceof Traversable) {
            return iterator_count($obj);
        }

        return 0;
    }

    public static function lower($obj = null)
    {
        return mb_strtolower((string) $obj, self::$encoding);
    }

    public static function nl2br($obj = null, $is_xhtml = false)
    {
        return nl2br((string) $obj, $is_xhtml);
    }

    public static function range($lower = null, $upper = null, $step = 1)
    {
        return new RangeIterator(intval($lower), intval($upper), intval($step));
    }

    public static function repeat($obj, $times = 2)
    {
        return str_repeat((string) $obj, $times);
    }

    public static function replace($obj = null, $search = '', $replace = '', $regex = false)
    {
        if ($regex) {
            return preg_replace($search, $replace, (string) $obj);
        }

        return str_replace($search, $replace, (string) $obj);
    }

    public static function strip_tags($obj = null, $allowableTags = '')
    {
        return strip_tags((string) $obj, $allowableTags);
    }

    public static function title($obj = null)
    {
        return ucwords((string) $obj);
    }

    public static function trim($obj = null, $charlist = " \t\n\r\0\x0B")
    {
        return trim((string) $obj, $charlist);
    }

    public static function striptags($obj = null, $allowable_tags = null)
    {
        return strip_tags((string) $obj, $allowable_tags);
    }

    public static function truncate($obj = null, $length = 255, $preserve_words = false, $hellip = '&hellip;')
    {
        $obj = (string) $obj;
        $len = mb_strlen($obj, self::$encoding);

        if ($length >= $len) {
            return $obj;
        }

        $truncated = $preserve_words ? preg_replace('/\s+?(\S+)?$/', '', mb_substr($obj, 0, $length + 1, self::$encoding)) : mb_substr($obj, 0, $length, self::$encoding);

        return $truncated.$hellip;
    }

    public static function unescape($obj = null)
    {
        return htmlspecialchars_decode((string) $obj, ENT_QUOTES);
    }

    public static function raw($obj = null)
    {
        return htmlspecialchars_decode((string) $obj, ENT_QUOTES);
    }

    public static function safe($obj = null)
    {
        return htmlspecialchars_decode((string) $obj, ENT_QUOTES);
    }

    public static function chunk($obj, $by)
    {
        return $obj ? array_chunk($obj, $by) : null;
    }

    public static function upper($obj = null)
    {
        return mb_strtoupper((string) $obj, self::$encoding);
    }

    public static function url_encode($obj = null)
    {
        return urlencode((string) $obj);
    }

    public static function word_wrap($obj = null, $width = 75, $break = "\n", $cut = false)
    {
        return wordwrap((string) $obj, $width, $break, $cut);
    }

    public static function round($obj = null, $precision = 0, $type = 'common')
    {
        switch ($type) {
            case 'ceil':
                return ceil($obj);
                break;
            case 'floor':
                return floor($obj);
                break;
        }

        return round($obj, $precision);
    }

    public static function has_key($obj, $key)
    {
        return array_key_exists($key, (array) $obj);
    }

    public static function call($obj, $method, array $args = [])
    {
        return call_user_func_array([$obj, $method], $args);
    }

    public static function merge($src = null, $dst = null)
    {
        if (!$src) {
            $src = [];
        }

        if (!$dst) {
            $dst = [];
        }

        return array_merge($src, $dst);
    }

    public static function strict_type($obj = null)
    {
        if (is_numeric($obj) && mb_strlen($obj, self::$encoding)) {
            return (bool) $obj;
        } elseif (is_numeric($obj)) {
            return (int) $obj;
        } elseif (is_string($obj) && in_array((string) $obj, ['true', 'false'])) {
            return (bool) $obj;
        }

        return (string) $obj;
    }

    /**
     * @param null $obj
     * @return int
     * @deprecated since 4.0
     */
    public static function toint($obj = null)
    {
        return self::to_int($obj);
    }

    /**
     * @param null $obj
     * @return int
     */
    public static function to_int($obj = null): int
    {
        return (int)$obj;
    }

    /**
     * @param null $obj
     * @return string
     */
    public static function to_string($obj = null): string
    {
        return (string)$obj;
    }

    /**
     * @param null $obj
     * @return array
     */
    public static function to_array($obj = null): array
    {
        return (array)$obj;
    }

    /**
     * @param null $obj
     * @return float
     */
    public static function to_float($obj = null): float
    {
        return (float)$obj;
    }
}
