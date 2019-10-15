<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahConverter;
use Noah\SDK\NoahPrefix;

/**
 * Class NoahDeclareCandidacyTx
 * @package Noah\SDK\NoahCoins
 */
class NoahDeclareCandidacyTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 6;

    /**
     * Fee units
     */
    const COMMISSION = 10000;

    /**
     * Declare candidacy tx data
     *
     * @var array
     */
    public $data = [
        'address' => '',
        'pubkey' => '',
        'commission' => '',
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
            'address' => hex2bin(
                Helper::removeWalletPrefix($this->data['address'])
            ),

            // Remove Noah wallet prefix and convert hex string to binary
            'pubkey' => hex2bin(
                Helper::removePrefix($this->data['pubkey'], NoahPrefix::PUBLIC_KEY)
            ),

            // Define commission field
            'commission' => $this->data['commission'] === 0 ? '' : $this->data['commission'],

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
            'address' => Helper::addWalletPrefix($txData[0]),

            // Add Noah wallet prefix to string
            'pubkey' => NoahPrefix::PUBLIC_KEY . $txData[1],

            // Decode hex string to number
            'commission' => Helper::hexDecode($txData[2]),

            // Pack coin name
            'coin' => Helper::hex2str($txData[3]),

            // Convert stake from QNOAH to NOAH
            'stake' => NoahConverter::convertValue(Helper::hexDecode($txData[4]), 'noah')
        ];
    }
}