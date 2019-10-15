<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahConverter;

/**
 * Class NoahSellAllCoinTx
 * @package Noah\SDK\NoahCoins
 */
class NoahSellAllCoinTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 3;

    /**
     * Fee units
     */
    const COMMISSION = 100;

    /**
     * Send coin tx data
     *
     * @var array
     */
    public $data = [
        'coinToSell' => '',
        'coinToBuy' => '',
        'minimumValueToBuy' => ''
    ];

    /**
     * Prepare tx data for signing
     *
     * @return array
     */
    public function encode(): array
    {
        return [
            // Add nulls before symbol
            'coinToSell' => NoahConverter::convertCoinName($this->data['coinToSell']),

            // Add nulls before symbol
            'coinToBuy' => NoahConverter::convertCoinName($this->data['coinToBuy']),

            // Convert field from NOAH to QNOAH
            'minimumValueToBuy' => NoahConverter::convertValue($this->data['minimumValueToBuy'], 'qnoah')
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
            // Pack symbol
            'coinToSell' => Helper::hex2str($txData[0]),

            // Pack symbol
            'coinToBuy' => Helper::hex2str($txData[1]),

            // Convert field from QNOAH to NOAH
            'minimumValueToBuy' => NoahConverter::convertValue(Helper::hexDecode($txData[2]), 'noah')
        ];
    }
}