<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;
use Noah\Library\Helper;
use Noah\SDK\NoahPrefix;

/**
 * Class NoahSetCandidateOnTx
 * @package Noah\SDK\NoahCoins
 */
class NoahSetCandidateOnTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 10;

    /**
     * Fee units
     */
    const COMMISSION = 100;

    /**
     * Set candidate on tx data
     *
     * @var array
     */
    public $data = [
        'pubkey' => ''
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
        ];
    }
}