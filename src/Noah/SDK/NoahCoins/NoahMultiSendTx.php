<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;

/**
 * Class NoahMultiSendTx
 * @package Noah\SDK\NoahCoins
 */
class NoahMultiSendTx extends NoahCoinTx implements NoahTxInterface
{
    /**
     * Type
     */
    const TYPE = 13;

    /**
     * Fee units
     */
    const COMMISSION = 5;

    /**
     * Send coin tx data
     *
     * @var array
     */
    public $data = [
        'list' => [],
    ];

    /**
     * Prepare tx data for signing
     *
     * @return array
     */
    public function encode(): array
    {
        foreach($this->data['list'] as $key => $data) {
            $sendCoinTxData = new NoahSendCoinTx($data);
            $this->data['list'][$key] = $sendCoinTxData->data;
        }

        return $this->data;
    }

    /**
     * Prepare output tx data
     *
     * @param array $txData
     * @return array
     */
    public function decode(array $txData): array
    {
        $txData = $txData[0];

        foreach($txData as $key => $data) {
            $sendCoinTxData = new NoahSendCoinTx($data, true);
            $txData[$key] = $sendCoinTxData->data;
        }

        return [
            'list' => $txData
        ];
    }

    /**
     * Transaction data fee
     *
     * @return int
     */
    public function getFee()
    {
        return NoahSendCoinTx::COMMISSION + (count($this->data['list']) - 1) * self::COMMISSION;
    }
}