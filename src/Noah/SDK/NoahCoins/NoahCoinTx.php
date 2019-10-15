<?php

namespace Noah\SDK\NoahCoins;

use Noah\Contracts\NoahTxInterface;

/**
 * Class NoahCoinTx
 * @package Noah\SDK\NoahCoins
 */
abstract class NoahCoinTx implements NoahTxInterface
{
    /**
     * Send coin tx data
     *
     * @var array
     */
    public $data;

    /**
     * NoahSendCoinTx constructor.
     * @param $data
     * @throws \Exception
     */
    public function __construct(array $data, $convert = false)
    {
        if(count($data) !== count($this->data)) {
            throw new \Exception('Invalid elements of data');
        }

        if(!$convert) {
            foreach ($this->data as $key => $value) {
                if (!isset($data[$key])) {
                    throw new \Exception('Undefined element "' . $key . '" in tx data');
                }

                $this->data[$key] = $data[$key];
            }

            $this->data = $this->encode();
        }
        else {
            $this->data = $this->decode($data);
        }
    }

    /**
     * Get
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], []);
        }

        return $this->data[$name];
    }

    /**
     * Get transaction data fee
     *
     * @return int
     */
    public function getFee()
    {
        return static::COMMISSION;
    }

    /**
     * Prepare data tx for signing
     *
     * @return array
     */
    abstract function encode(): array;

    /**
     * Prepare output tx data
     *
     * @param array $txData
     * @return array
     */
    abstract function decode(array $txData): array;
}