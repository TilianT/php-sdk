<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahConverter;
use Noah\SDK\NoahPrefix;

/**
 * Class NoahUnbondTx
 * @package Noah\SDK\NoahCoins
 */
class NoahUnbondTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 8;

    /**
     * Fee units
     */
    const COMMISSION = 200;

    /**
     * Unbond tx data
     *
     * @var array
     */
    public $data = [
        'pubkey' => '',
        'coin' => '',
        'value' => ''
    ];

    /**
     * Prepare data for signing
     *
     * @return array
     */
    public function encode(): array
    {
        return [
            // Remove Noah wallet prefix and convert hex string to binary
            'pubkey' => hex2bin(
                Helper::removePrefix($this->data['pubkey'], NoahPrefix::PUBLIC_KEY)
            ),

            // Add nulls before coin name
            'coin' => NoahConverter::convertCoinName($this->data['coin']),

            // Convert from NOAH to QNOAH
            'value' => NoahConverter::convertValue($this->data['value'], 'qnoah')
        ];
    }

    /**
     * Prepare output tx data
     *
     * @param array $txData
     * @return array
     */
    public function decode(array $txData): array
    {
        return [
            // Add Noah wallet prefix to string
            'pubkey' => NoahPrefix::PUBLIC_KEY . $txData[0],

            // Pack binary to string
            'coin' => Helper::hex2str($txData[1]),

            // Convert value from QNOAH to NOAH
            'value' => NoahConverter::convertValue(Helper::hexDecode($txData[2]), 'noah')
        ];
    }
}