<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahPrefix;

/**
 * Class NoahRedeemCheckTx
 * @package Noah\SDK\NoahCoins
 */
class NoahRedeemCheckTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 9;

    /**
     * Fee units
     */
    const COMMISSION = 30;

    /**
     * Noah Redeem check tx data
     *
     * @var array
     */
    public $data = [
        'check' => '',
        'proof' => ''
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
            'check' => hex2bin(
                Helper::removePrefix($this->data['check'], NoahPrefix::CHECK)
            ),

            // Convert hex string to binary
            'proof' => hex2bin($this->data['proof'])
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
            // Add Noah wallet prefix to hex string
            'check' => NoahPrefix::CHECK . $txData[0],

            // Define proof field
            'proof' => $txData[1],
        ];
    }
}