<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahConverter;

/**
 * Class NoahSendCoinTx
 * @package Noah\SDK\NoahCoins
 */
class NoahSendCoinTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 1;

    /**
     * Fee units
     */
    const COMMISSION = 10;

    /**
     * Send coin tx data
     *
     * @var array
     */
    public $data = [
        'coin' => '',
        'to' => '',
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
            // Add nulls before coin name
            'coin' => NoahConverter::convertCoinName($this->data['coin']),

            // Remove Noah wallet prefix and convert hex string to binary
            'to' => hex2bin(
                Helper::removeWalletPrefix($this->data['to'])
            ),

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
            // Pack binary to string
            'coin' => Helper::hex2str($txData[0]),

            // Add Noah wallet prefix to string
            'to' => Helper::addWalletPrefix($txData[1]),

            // Convert value from QNOAH to NOAH
            'value' => NoahConverter::convertValue(Helper::hexDecode($txData[2]), 'noah')
        ];
    }
}