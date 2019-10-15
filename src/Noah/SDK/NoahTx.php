<?php

namespace Noah\SDK;

use InvalidArgumentException;
use Exception;
use Web3p\RLP\Buffer;
use Web3p\RLP\RLP;
use Noah\Library\ECDSA;
use Noah\Library\Helper;
use Noah\SDK\NoahCoins\{
    NoahCoinTx, NoahDelegateTx, NoahEditCandidateTx,
    NoahMultiSendTx, NoahRedeemCheckTx, NoahSellAllCoinTx,
    NoahSetCandidateOffTx, NoahSetCandidateOnTx, NoahCreateCoinTx,
    NoahDeclareCandidacyTx, NoahSendCoinTx, NoahUnbondTx,
    NoahSellCoinTx, NoahBuyCoinTx
};

/**
 * Class NoahTx
 * @package Noah\SDK
 */
class NoahTx
{
    /**
     * Transaction
     *
     * @var array
     */
    protected $tx;

    /** @var RLP */
    protected $rlp;

    /**
     * Noah transaction structure
     *
     * @var array
     */
    protected $structure = [
        'nonce',
        'chainId',
        'gasPrice',
        'gasCoin',
        'type',
        'data',
        'payload',
        'serviceData',
        'signatureType',
        'signatureData'
    ];

    /**
     * @var string
     */
    protected $txSigned;

    /**
     * Transaction data
     * @var NoahCoinTx
     */
    protected $txDataObject;

    /** Fee in PIP */
    const PAYLOAD_COMMISSION = 2;

    /** All gas price multiplied by FEE DEFAULT (PIP) */
    const FEE_DEFAULT_MULTIPLIER = 1000000000000000;

    /** Type of single signature for the transaction */
    const SIGNATURE_SINGLE_TYPE = 1;

    /** Type of multi signature for the transaction */
    const SIGNATURE_MULTI_TYPE = 2;

    /** Mainnet chain id */
    const MAINNET_CHAIN_ID = 1;

    /** Testnet chain id */
    const TESTNET_CHAIN_ID = 2;

    /**
     * NoahTx constructor.
     * @param $tx
     * @throws \Exception
     */
    public function __construct($tx)
    {
        $this->tx = $tx;
        $this->rlp = new RLP;

        if(is_string($tx)) {
            $this->txSigned = Helper::removePrefix($tx, NoahPrefix::TRANSACTION);
            $this->tx = $this->decode($tx);
        }

        if(is_array($tx)) {
            $this->tx = $this->encode($tx);
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

        return $this->tx[$name];
    }

    /**
     * Get sender Noah address
     *
     * @param array $tx
     * @return string
     * @throws \Exception
     */
    public function getSenderAddress(array $tx): string
    {
        return NoahWallet::getAddressFromPublicKey(
            $this->recoverPublicKey($tx)
        );
    }

    /**
     * Sign tx
     *
     * @param string $privateKey
     * @return string
     * @throws \Exception
     */
    public function sign(string $privateKey): string
    {
        if(!is_array($this->tx)) {
            throw new \Exception('Undefined transaction');
        }

        // encode data array to RPL
        $tx = $this->txDataRlpEncode($this->tx);
	    $tx['payload'] = new Buffer(str_split($tx['payload'], 1));

        // create keccak hash from transaction
        $keccak = Helper::createKeccakHash(
            $this->rlp->encode($tx)->toString('hex')
        );

        // prepare special [V, R, S] signature bytes and add them to transaction
        $signature = ECDSA::sign($keccak, $privateKey);
        $tx['signatureData'] = $this->rlp->encode(
            Helper::hex2buffer($signature)
        );

        // pack transaction to hex string
        $this->txSigned = $this->rlp->encode($tx)->toString('hex');

        return NoahPrefix::TRANSACTION . $this->txSigned;
    }

    /**
     * Recover public key
     *
     * @param array $tx
     * @return string
     * @throws \Exception
     */
    public function recoverPublicKey(array $tx): string
    {
        // prepare short transaction
        $shortTx = array_diff_key($tx, ['signatureData' => '']);
        $shortTx = Helper::hex2binRecursive($shortTx);
        $shortTx = $this->txDataRlpEncode($shortTx);

        // create kessak hash from transaction
        $msg = Helper::createKeccakHash(
            $this->rlp->encode($shortTx)->toString('hex')
        );

        // recover public key
        $signature = $tx['signatureData'];
        $publicKey = ECDSA::recover($msg, $signature['r'], $signature['s'], $signature['v']);

        return NoahPrefix::PUBLIC_KEY . $publicKey;
    }

    /**
     * Get hash of transaction
     *
     * @return string
     */
    public function getHash(): string
    {
        if(!$this->txSigned) {
            throw new \Exception('You need to sign transaction before');
        }

        // create SHA256 of tx
        $tx = hash('sha256', hex2bin($this->txSigned));

        // return first 40 symbols
        return NoahPrefix::TRANSACTION_HASH . substr($tx, 0, 40);
    }

    /**
     * Get fee of transaction in PIP
     *
     * @return string
     * @throws \Exception
     */
    public function getFee(): string
    {
        if(!$this->txDataObject) {
            throw new Exception('You need to sign transaction before the calculating free');
        }

        // get transaction data fee
        $gas = $this->txDataObject->getFee();

        // multiplied gas price
        $gasPrice = bcmul($gas, self::FEE_DEFAULT_MULTIPLIER, 0);

        // commission for payload and serviceData bytes
        $commission = bcadd(
            strlen($this->payload) * bcmul(self::PAYLOAD_COMMISSION, self::FEE_DEFAULT_MULTIPLIER, 0),
            strlen($this->serviceData) * bcmul(self::PAYLOAD_COMMISSION, self::FEE_DEFAULT_MULTIPLIER, 0)
        );

        return bcadd($gasPrice, $commission, 0);
    }

    /**
     * Decode tx
     *
     * @param string $tx
     * @return array
     * @throws \Exception
     */
    protected function decode(string $tx): array
    {
        // pack RLP to hex string
        $tx = $this->rlpToHex($tx);

        // pack data of transaction to hex string
        $tx[5] = $this->rlpToHex($tx[5]);
        $tx[9] = $this->rlpToHex($tx[9]);

        // encode transaction data
        return $this->encode($this->prepareResult($tx), true);
    }

    /**
     * Encode transaction data
     *
     * @param array $tx
     * @param bool $isHexFormat
     * @return array
     * @throws InvalidArgumentException
     */
    protected function encode(array $tx, bool $isHexFormat = false): array
    {
        // validate transaction structure
        $this->validateTx($tx);
        // make right order in transaction params
        $tx = array_replace(array_intersect_key(array_flip($this->structure), $tx), $tx);

        switch ($tx['type']) {
            case NoahSendCoinTx::TYPE:
                $this->txDataObject = new NoahSendCoinTx($tx['data'], $isHexFormat);
                break;

            case NoahSellCoinTx::TYPE:
                $this->txDataObject = new NoahSellCoinTx($tx['data'], $isHexFormat);
                break;

            case NoahSellAllCoinTx::TYPE:
                $this->txDataObject = new NoahSellAllCoinTx($tx['data'], $isHexFormat);
                break;

            case NoahBuyCoinTx::TYPE:
                $this->txDataObject = new NoahBuyCoinTx($tx['data'], $isHexFormat);
                break;

            case NoahCreateCoinTx::TYPE:
                $this->txDataObject = new NoahCreateCoinTx($tx['data'], $isHexFormat);
                break;

            case NoahDeclareCandidacyTx::TYPE:
                $this->txDataObject = new NoahDeclareCandidacyTx($tx['data'], $isHexFormat);
                break;

            case NoahDelegateTx::TYPE:
                $this->txDataObject = new NoahDelegateTx($tx['data'], $isHexFormat);
                break;

            case NoahUnbondTx::TYPE:
                $this->txDataObject = new NoahUnbondTx($tx['data'], $isHexFormat);
                break;

            case NoahRedeemCheckTx::TYPE:
                $this->txDataObject = new NoahRedeemCheckTx($tx['data'], $isHexFormat);
                break;

            case NoahSetCandidateOnTx::TYPE:
                $this->txDataObject = new NoahSetCandidateOnTx($tx['data'], $isHexFormat);
                break;

            case NoahSetCandidateOffTx::TYPE:
                $this->txDataObject = new NoahSetCandidateOffTx($tx['data'], $isHexFormat);
                break;

            case NoahMultiSendTx::TYPE:
                $this->txDataObject = new NoahMultiSendTx($tx['data'], $isHexFormat);
                break;

            case NoahEditCandidateTx::TYPE:
                $this->txDataObject = new NoahEditCandidateTx($tx['data'], $isHexFormat);
                break;

            default:
                throw new InvalidArgumentException('Unknown transaction type');
                break;
        }

        $tx['data'] = $this->txDataObject->data;

        return $tx;
    }

    /**
     * Prepare output result
     *
     * @param array $tx
     * @return array
     * @throws \Exception
     */
    protected function prepareResult(array $tx): array
    {
        $result = [];
        foreach($this->structure as $key => $field) {
            switch ($field) {
                case 'data':
                    $result[$field] = $tx[$key];
                    break;

                case 'payload':
                    $result[$field] = Helper::hex2str($tx[$key]);
                    break;

                case 'serviceData':
                    $result[$field] = Helper::hex2str($tx[$key]);
                    break;

                case 'gasCoin':
                    $result[$field] = NoahConverter::convertCoinName(
                        Helper::hex2str($tx[$key])
                    );
                    break;

                case 'signatureData':
                    $result[$field] = [
                        'v' => hexdec($tx[$key][0]),
                        'r' => $tx[$key][1],
                        's' => $tx[$key][2]
                    ];
                    break;

                default:
                    $result[$field] = hexdec($tx[$key]);
                    break;
            }
        }

        $result['from'] = $this->getSenderAddress($result);

        return $result;
    }

    /**
     * Convert array items from rlp to hex
     *
     * @param string $data
     * @return array
     */
    protected function rlpToHex(string $data): array
    {
        $data = $this->rlp->decode('0x' . $data);

        foreach ($data as $key => $value) {
            if(is_array($value)) {
                $data[$key] = Helper::rlpArrayToHexArray($value);
            } else {
                $data[$key] = $value->toString('hex');
            }
        }

        return (array) $data;
    }

    /**
     * Convert tx data to rlp
     *
     * @param array $tx
     * @return array
     */
    protected function txDataRlpEncode(array $tx): array
    {
        $tx['gasCoin'] = NoahConverter::convertCoinName($tx['gasCoin']);
        $tx['data'] = $this->rlp->encode($tx['data']);

        return $tx;
    }

    /**
     * Validate transaction structure
     *
     * @param array $tx
     */
    protected function validateTx(array $tx): void
    {
        // get keys of tx and prepare structure keys
        $length = count($this->structure) - 1;
        $tx = array_slice(array_keys($tx), 0, $length);
        $structure = array_slice($this->structure, 0, $length);

        // compare
        if(!empty(array_diff_key($tx, $structure))) {
            throw new InvalidArgumentException('Invalid transaction structure params');
        }
    }
}
