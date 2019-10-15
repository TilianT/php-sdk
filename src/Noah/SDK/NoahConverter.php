<?php

namespace Noah\SDK;

use Noah\Library\Helper;
use InvalidArgumentException;

/**
 * Class NoahConverter
 * @package Noah\SDK
 */
class NoahConverter
{
    /**
     * PIP in BIP
     */
    const DEFAULT = '1000000000000000000';

    /**
     * Convert value
     *
     * @param string $num
     * @param string $to
     * @return string
     */
    public static function convertValue(string $num, string $to)
    {
        if ($to === 'pip') {
            return bcmul(self::DEFAULT, $num, 0);
        } else if ($to === 'bip') {
            return Helper::niceNumber(bcdiv($num, self::DEFAULT, 25));
        }
    }

    /**
     * Add nulls to coin name
     *
     * @param string $symbol
     * @return string
     */
    public static function convertCoinName(string $symbol): string
    {
        $countOfNulls = 10 - strlen($symbol);
        if($countOfNulls < 0) {
            throw new InvalidArgumentException('Coin name could have no more than 10 symbols.');
        }

        return $symbol  . str_repeat(chr(0), $countOfNulls);
    }
}