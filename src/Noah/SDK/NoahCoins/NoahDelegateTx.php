<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahConverter;
use Noah\SDK\NoahPrefix;

/**
 * Class NoahDelegateTx
 * @package Noah\SDK\NoahCoins
 */
class NoahDelegateTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 7;

    /**
     * Fee units
     */
    const COMMISSION = 200;

    /**
     * Delegate tx data
     *
     * @var array
     */
    public $data = [
        'pubkey' => '',
        'coin' => '',
        'stake' => ''
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

            // Convert coin name
            'coin' => NoahConverter::convertCoinName($this->data['coin']),

            // Convert stake field from NOAH to QNOAH
            'stake' => NoahConverter::convertValue($this->data['stake'], 'qnoah')
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

            // Pack coin name
            'coin' => Helper::hex2str($txData[1]),

            // Convert stake from QNOAH to NOAH
            'stake' => NoahConverter::convertValue(Helper::hexDecode($txData[2]), 'noah')
        ];
    }
}