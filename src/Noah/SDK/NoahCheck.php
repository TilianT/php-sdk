<?php

namespace Noah\SDK;

use Noah\Library\ECDSA;
use Noah\Library\Helper;
use Web3p\RLP\Buffer;
use Web3p\RLP\RLP;

/**
 * Class NoahCheck
 * @package Noah\SDK
 */
class NoahCheck
{
    /**
     * @var RLP
     */
    protected $rlp;

    /**
     * @var string
     */
    protected $noahAddress;

    /**
     * Check passphrase
     *
     * @var string
     */
    protected $passphrase;

    /**
     * Check structure
     *
     * @var array
     */
    protected $structure = [
        'nonce',
        'chainId',
        'dueBlock',
        'coin',
        'value',
        'lock',
        'v',
        'r',
        's'
    ];

    /**
     * Define RLP, password and encode/decode check.
     *
     * NoahCheck constructor.
     * @param $checkOrAddress
     * @param string|null $passphrase
     */
    public function __construct($checkOrAddress, ?string $passphrase = null)
    {
        $this->rlp = new RLP;

        if(is_array($checkOrAddress)) {
            $this->structure = $this->defineProperties($checkOrAddress);
        }

        if(is_string($checkOrAddress) && !$passphrase) {
            $this->structure = $this->decode($checkOrAddress);
        }
        else if(is_string($checkOrAddress)) {
            $this->noahAddress = $checkOrAddress;
        }

        $this->passphrase = $passphrase;
    }

    /**
     * Get check structure.
     *
     * @return array
     */
    public function getBody(): array
    {
        return $this->structure;
    }

    /**
     * Get owner address from decoded check.
     *
     * @return string
     */
    public function getOwnerAddress(): string
    {
        return $this->noahAddress;
    }

    /**
     * Sign check.
     *
     * @param string $privateKey
     * @return string
     */
    public function sign(string $privateKey): string
    {
        // create message hash and passphrase by first 4 fields
        $msgHash = $this->serialize(array_slice($this->structure, 0, 5));

        $passphrase = hash('sha256', $this->passphrase);

        // create elliptic curve and sign
        $signature = ECDSA::sign($msgHash, $passphrase);

        // define lock field
        $this->structure['lock'] = hex2bin($this->formatLockFromSignature($signature));

        // create message hash with lock field
        $msgHashWithLock = $this->serialize(array_slice($this->structure, 0, 6));

        // create signature
        $signature = ECDSA::sign($msgHashWithLock, $privateKey);
        $this->structure = array_merge($this->structure, Helper::hex2buffer($signature));

        // rlp encode data and add Noah wallet prefix
        return NoahPrefix::CHECK . $this->rlp->encode($this->structure)->toString('hex');
    }

    /**
     * Create proof by address and passphrase.
     *
     * @return string
     * @throws \Exception
     */
    public function createProof(): string
    {
        if(!$this->noahAddress) {
            throw new \Exception('Noah address is not defined');
        }

        // create msg hash of address
        $noahAddress = [hex2bin(Helper::removeWalletPrefix($this->noahAddress))];
        $addressHash = $this->serialize($noahAddress);

        // get SHA 256 hash of password and create EC signature
        $passphrase = hash('sha256', $this->passphrase);
        $signature = ECDSA::sign($addressHash, $passphrase);

        // return formatted proof
        return $this->formatLockFromSignature($signature);
    }

    /**
     * Decode check.
     *
     * @param string $check
     * @return array
     */
    protected function decode(string $check): array
    {
        // prepare check string and convert to hex array
        $check = Helper::removePrefix($check, NoahPrefix::CHECK);
        $check = $this->rlp->decode('0x' . $check);
        $check = Helper::rlpArrayToHexArray($check);

        // prepare decoded data
        $data = [];
        foreach ($check as $key => $value) {
            $field = $this->structure[$key];

            switch ($field) {
                case 'nonce':
                case 'coin':
                    $data[$field] = Helper::hex2str($value);
                    break;

                case 'value':
                    $data[$field] = NoahConverter::convertValue(Helper::hexDecode($value), 'bip');
                    break;

                default:
                    $data[$field] = $value;
                    if(in_array($field, ['dueBlock', 'v', 'chainId'])) {
                        $data[$field] = hexdec($value);
                    }
                    break;
            }
        }

        // set owner address
        list($body, $signature) = array_chunk($data, 6, true);
        $this->setOwnerAddress($body, $signature);

        return $data;
    }

    /**
     * Set check owner address.
     *
     * @param array $body
     * @param array $signature
     */
    protected function setOwnerAddress(array $body, array $signature): void
    {
        // encode check to rlp
        $lock = array_pop($body);
        $check = $this->encode($body);
        $check['lock'] = hex2bin($lock);

        // create keccak hash from check
        $msg = $this->serialize($check);

        // recover public key
        $publicKey = ECDSA::recover($msg, $signature['r'], $signature['s'], $signature['v']);
        $publicKey = NoahPrefix::PUBLIC_KEY . $publicKey;

        $this->noahAddress = NoahWallet::getAddressFromPublicKey($publicKey);
    }

    /**
     * Merge input fields with structure.
     *
     * @param array $check
     * @return array
     * @throws \Exception
     */
    protected function defineProperties(array $check): array
    {
        $structure = array_flip($this->structure);

        if(!$this->validateFields($check)) {
            throw new \Exception('Invalid fields');
        }

        return array_merge($structure, $this->encode($check));
    }

    /**
     * Encode input fields.
     *
     * @param array $check
     * @return array
     */
    protected function encode(array $check): array
    {
        return [
            'nonce' => Helper::hexDecode(
                Helper::str2hex($check['nonce'])
            ),

            'chainId' => dechex($check['chainId']),

            'dueBlock' => $check['dueBlock'],

            'coin' => NoahConverter::convertCoinName($check['coin']),

            'value' => NoahConverter::convertValue($check['value'], 'pip'),
        ];
    }

    /**
     * Create message Keccak hash from structure fields limited by number of fields.
     *
     * @return array
     */
    protected function serialize($data): string
    {
        // create msg hash with lock field
        $msgHash = $this->rlp->encode($data)->toString('hex');

        return Helper::createKeccakHash($msgHash);
    }

    /**
     * Validate that input fields are correct.
     *
     * @param array $fields
     * @return bool
     */
    protected function validateFields(array $fields): bool
    {
        $structure = array_flip($this->structure);

        foreach ($fields as $field => $fieldValue) {
            if(!isset($structure[$field])) {
                return false;
            }

            if($field === 'nonce' && strlen($fieldValue) > 32) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prepare lock field.
     *
     * @param array $signature
     * @return string
     */
    protected function formatLockFromSignature(array $signature): string
    {
        $r = str_pad($signature['r'], 64, '0', STR_PAD_LEFT);
        $s = str_pad($signature['s'], 64, '0', STR_PAD_LEFT);
        $recovery = hexdec($signature['v']) === ECDSA::V_BITS ? '00' : '01';

        return $r . $s. $recovery;
    }
}