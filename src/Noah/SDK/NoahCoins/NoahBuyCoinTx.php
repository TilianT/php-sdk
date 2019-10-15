<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahConverter;

/**
 * Class NoahBuyCoinTx
 * @package Noah\SDK\NoahCoins
 */
class NoahBuyCoinTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 4;

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
        'coinToBuy' => '',
        'valueToBuy' => '',
        'coinToSell' => '',
        'maximumValueToSell' => ''
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
            'coinToBuy' => NoahConverter::convertCoinName($this->data['coinToBuy']),

            // Convert field from Noah to QNoah
            'valueToBuy' => NoahConverter::convertValue($this->data['valueToBuy'], 'qnoah'),

            // Add nulls before symbol
            'coinToSell' => NoahConverter::convertCoinName($this->data['coinToSell']),

            // Convert field from NOAH to QNoah
            'maximumValueToSell' => NoahConverter::convertValue($this->data['maximumValueToSell'], 'qnoah')
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
            'coinToBuy' => Helper::hex2str($txData[0]),

            // Convert field from QNoah to NOAH
            'valueToBuy' => NoahConverter::convertValue(Helper::hexDecode($txData[1]), 'noah'),

            // Pack symbol
            'coinToSell' => Helper::hex2str($txData[2]),

            // Convert field from QNoah to NOAH
            'maximumValueToSell' => NoahConverter::convertValue(Helper::hexDecode($txData[3]), 'noah')
        ];
    }
}