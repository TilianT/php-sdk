<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahPrefix;

/**
 * Class NoahEditCandidateTx
 * @package Noah\SDK\NoahCoins
 */
class NoahEditCandidateTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 14;

    /**
     * Fee units
     */
    const COMMISSION = 10000;

    /**
     * Edit candidate tx data
     *
     * @var array
     */
    public $data = [
        'pubkey' => '',
        'reward_address' => '',
        'owner_address' => ''
    ];

    /**
     * Prepare data for signing
     *
     * @return array
     */
    public function encode(): array
    {
        return [
            // Remove Noah public key prefix and convert hex string to binary
            'pubkey' => hex2bin(
                Helper::removePrefix($this->data['pubkey'], NoahPrefix::PUBLIC_KEY)
            ),

            // Remove Noah wallet prefix and convert hex string to binary
            'reward_address' => hex2bin(
                Helper::removeWalletPrefix($this->data['reward_address'])
            ),

            // Remove Noah wallet prefix and convert hex string to binary
            'owner_address' => hex2bin(
                Helper::removeWalletPrefix($this->data['owner_address'])
            )
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

            // Add Noah wallet prefix to string
            'reward_address' => Helper::addWalletPrefix($txData[1]),

            // Add Noah wallet prefix to string
            'owner_address' => Helper::addWalletPrefix($txData[2])
        ];
    }
}