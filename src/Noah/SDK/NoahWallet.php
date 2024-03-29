<?php

namespace Noah\SDK;

use BitWasp\BitcoinLib\BIP39\BIP39;
use kornrunner\Keccak;
use BIP\BIP44;
use Noah\Library\ECDSA;
use Noah\Library\Helper;

/**
 * Class NoahWallet
 * @package Noah\SDK
 */
class NoahWallet
{
    /**
     * Amount of entropy bits
     */
    const BIP44_ENTROPY_BITS = 128;

    /**
     * Address path for creating wallet from the seed
     */
    const BIP44_SEED_ADDRESS_PATH = "m/44'/60'/0'/0/0";

    /**
     * Create Noah wallet
     *
     * @return array
     * @throws \Exception
     */
    public static function create(): array
    {
        $mnemonic = self::generateMnemonic();
        $seed = self::mnemonicToSeed($mnemonic);
        $privateKey = self::seedToPrivateKey($seed);
        $publicKey = self::privateToPublic($privateKey);
        $address = self::getAddressFromPublicKey($publicKey);

        return [
            'seed' => $seed,
            'address' => $address,
            'mnemonic' => $mnemonic,
            'public_key' => $publicKey,
            'private_key' => $privateKey
        ];
    }

    /**
     * Generate public key
     *
     * @param string $privateKey
     * @return string
     */
    public static function privateToPublic(string $privateKey): string
    {
        return NoahPrefix::PUBLIC_KEY . ECDSA::privateToPublic($privateKey);
    }

    /**
     * Retrieve address from public key
     *
     * @param string $publicKey
     * @return string
     * @throws \Exception
     */
    public static function getAddressFromPublicKey(string $publicKey): string
    {
        // remove public key
        $publicKey = Helper::removePrefix($publicKey, NoahPrefix::PUBLIC_KEY);

        // create keccak hash
        $hash = Keccak::hash(hex2bin($publicKey), 256);

        return NoahPrefix::ADDRESS . substr($hash, -40);
    }

    /**
     * Generate mnemonic phrase from entropy.
     *
     * @return string
     */
    public static function generateMnemonic(): string
    {
        return BIP39::entropyToMnemonic(
            BIP39::generateEntropy(self::BIP44_ENTROPY_BITS)
        );
    }

    /**
     * Get seed from the mnemonic phrase.
     *
     * @param string $mnemonic
     * @return string
     */
    public static function mnemonicToSeed(string $mnemonic): string
    {
        return BIP39::mnemonicToSeedHex($mnemonic, '');
    }

    /**
     * Get private key from seed.
     *
     * @param string $seed
     * @return string
     */
    public static function seedToPrivateKey(string $seed): string
    {
        return BIP44::fromMasterSeed($seed)->derive(self::BIP44_SEED_ADDRESS_PATH)->privateKey;
    }

    /**
     * Validate that address is valid Noah address
     *
     * @param string $address
     * @return bool
     */
    public static function validateAddress(string $address): bool
    {
        return strlen($address) === 42 && substr($address, 0, 2) === NoahPrefix::ADDRESS && ctype_xdigit(substr($address, -40));
    }
}