<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahConverter;

/**
 * Class NoahCreateCoinTx
 * @package Noah\SDK\NoahCoins
 */
class NoahCreateCoinTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 5;

    /**
     * Fee units
     */
    const COMMISSION = 1000;

    /**
     * Send coin tx data
     *
     * @var array
     */
    public $data = [
        'name' => '',
        'symbol' => '',
        'initialAmount' => '',
        'initialReserve' => '',
        'crr' => ''
    ];

    /**
     * Prepare tx data for signing
     *
     * @return array
     */
    public function encode(): array
    {
        return [
            // Define name field
            'name' => $this->data['name'],

            // Add nulls before symbol
            'symbol' => NoahConverter::convertCoinName($this->data['symbol']),

            // Convert field from NOAH to PIP
            'initialAmount' => NoahConverter::convertValue($this->data['initialAmount'], 'pip'),

            // Convert field from NOAH to PIP
            'initialReserve' => NoahConverter::convertValue($this->data['initialReserve'], 'pip'),

            // Define crr field
            'crr' => $this->data['crr'] === 0 ? '' : $this->data['crr']
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
            // Pack name
            'name' => Helper::hex2str($txData[0]),

            // Pack symbol
            'symbol' => Helper::hex2str($txData[1]),

            // Convert field from PIP to NOAH
            'initialAmount' => NoahConverter::convertValue(Helper::hexDecode($txData[2]), 'qnoah'),

            // Convert field from PIP to NOAH
            'initialReserve' => NoahConverter::convertValue(Helper::hexDecode($txData[3]), 'qnoah'),

            // Convert crr field from hex string to number
            'crr' => hexdec($txData[4])
        ];
    }
}