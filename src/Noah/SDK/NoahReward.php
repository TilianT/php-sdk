<?php

namespace Noah\SDK;

/**
 * Class NoahReward
 * @package Noah\SDK
 */
class NoahReward
{
    /**
     * Total blocks for reward
     */
    const TOTAL_BLOCKS_COUNT = 43702612;

    /**
     * First reward
     */
    CONST FIRST_REWARD = 333;

    /*
     * Last reward
     */
    CONST LAST_REWARD = 68;

    /**
     * Get reward by the block number in PIP
     *
     * @param int $blockNumber
     * @return string
     */
    public static function get(int $blockNumber): string
    {
        // check that block number is correct
        if($blockNumber <= 0) {
            throw new \InvalidArgumentException('Block number should be greater than 0');
        }

        if($blockNumber > self::TOTAL_BLOCKS_COUNT) {
            return NoahConverter::convertValue('0', 'pip');
        }

        if($blockNumber === self::TOTAL_BLOCKS_COUNT) {
            return NoahConverter::convertValue(self::LAST_REWARD, 'pip');
        }

        $reward = self::formula($blockNumber);

        return NoahConverter::convertValue($reward, 'pip');
    }

    /**
     * Calculate reward by formula
     *
     * @param int $blockNumber
     * @return string
     */
    protected static function formula(int $blockNumber): string
    {
        $reward = self::FIRST_REWARD - ($blockNumber / 200000);

        return ceil($reward);
    }
}