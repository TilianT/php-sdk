
## About

This is a pure PHP SDK for working with <b>Noah</b> blockchain

* [Installation](#installing)
* [Noah Api](#using-noahapi)
    - Methods:
	    - [getBalance](#getbalance)
	    - [getNonce](#getnonce)
	    - [send](#send)
	    - [getStatus](#getstatus)
	    - [getValidators](#getvalidators)
	    - [estimateCoinBuy](#estimatecoinbuy)
	    - [estimateCoinSell](#estimatecoinsell)
	    - [getCoinInfo](#getcoininfo)
	    - [getBlock](#getblock)
	    - [getEvents](#getevents)
	    - [getTransaction](#gettransaction)
	    - [getCandidate](#getcandidate)
	    - [getCandidates](#getcandidates)
	    - [estimateTxCommission](#estimatetxcommission)
	    - [getTransactions](#gettransactions)
	    - [getUnconfirmedTxs](#getunconfirmedtxs)
	    - [getMaxGasPrice](#getmaxgasprice)
	    - [getMinGasPrice](#getmingasprice)
	    - [getMissedBlocks](#getmissedblocks)
	- [Error handling](#error-handling)
	
* [Noah SDK](#using-noahsdk)
	- [Sign transaction](#sign-transaction)
		- [SendCoin](#example-3)
		- [SellCoin](#example-4)
		- [SellAllCoin](#example-5)
		- [BuyCoin](#example-6)
		- [CreateCoin](#example-7)
		- [DeclareCandidacy](#example-8)
		- [Delegate](#example-9)
		- [SetCandidateOn](#example-10)
		- [SetCandidateOff](#example-11)
		- [RedeemCheck](#example-12)
		- [Unbond](#example-13)
		- [MultiSend](#example-14)
		- [EditCandidate](#example-15)
	- [Get fee of transaction](#get-fee-of-transaction)
	- [Get hash of transaction](#get-hash-of-transaction)
	- [Decode Transaction](#decode-transaction)
	- [Noah Check](#create-noah-check)
	- [Noah Wallet](#noah-wallet)
* [Tests](#tests)

## Installing

```bash
composer require noah/php-sdk
```

## Using NoahAPI

You can get all valid responses and full documentation at [noah Node Api](https://noah-go-node.readthedocs.io/en/latest/api.html)

Create NoahAPI instance

```php
use Noah\NoahAPI;

$nodeUrl = 'http://176.9.116.72:8841'; // example of a node url

$api = new NoahAPI($nodeUrl);
```

### getBalance

Returns coins list, balance and transaction count (for nonce) of an address.

``
getBalance(string $noahAddress, ?int $height = null): \stdClass
``

###### Example

```php
$api->getBalance('NOAHxDD6DD653557698cb9ad13639825A48fad435284d')

// {"jsonrpc": "2.0", "id": "", "result": { "balance": { ... }, "transaction_count": "0"}}

```

### getNonce

Returns next transaction number (nonce) of an address.

``
getNonce(string $noahAddress): int
``

###### Example

```php
$api->getNonce('NOAHxDD6DD653557698cb9ad13639825A48fad435284d')
```

### send

Returns the result of sending <b>signed</b> tx.

``
send(string $tx): \stdClass
``

###### Example

```php
$api->send('f873010101aae98a4d4e540000000000000094fe60014a6e9ac91618f5d1cab3fd58cded61ee99880de0b6b3a764000080801ca0ae0ee912484b9bf3bee785f4cbac118793799450e0de754667e2c18faa510301a04f1e4ed5fad4b489a1065dc1f5255b356ab9a2ce4b24dde35bcb9dc43aba019c')
```

### getStatus

Returns node status info.

``
getStatus(): \stdClass
``

### getValidators

Returns list of active validators.

``
getValidators(?int $height = null): \stdClass
``

### estimateCoinBuy

Return estimate of buy coin transaction.

``
estimateCoinBuy(string $coinToSell, string $valueToBuy, string $coinToBuy, ?int $height = null): \stdClass
``

### estimateCoinSell

Return estimate of sell coin transaction.

``
estimateCoinSell(string $coinToSell, string $valueToSell, string $coinToBuy, ?int $height = null): \stdClass
``

### getCoinInfo

Returns information about coin.
Note: this method does not return information about base coins (NOAH).

``
getCoinInfo(string $coin, ?int $height = null): \stdClass
``

### getBlock

Returns block data at given height.

``
getBlock(int $height): \stdClass
``

### getEvents

Returns events at given height.

``
getEvents(int $height): \stdClass
``

### getTransaction

Returns transaction info.

``
getTransaction(string $hash): \stdClass
``

### getCandidate

Returns candidate’s info by provided public_key. It will respond with 404 code if candidate is not found.

``
getCandidate(string $publicKey, ?int $height = null): \stdClass
``

### getCandidates

Returns list of candidates.

$height is optional parameter.

``
getCandidates(?int $height = null, ?bool $includeStakes = false): \stdClass
``

### estimateTxCommission

Return estimate of transaction.

``
estimateTxCommission(string $tx): \stdClass
``

### getTransactions

Return transactions by query.

``
getTransactions(string $query, ?int $page = null, ?int $perPage = null): \stdClass
``

### getUnconfirmedTxs

Returns unconfirmed transactions.

``
getUnconfirmedTxs(?int $limit = null): \stdClass
``

### getMaxGasPrice

Returns current max gas price.noah

``
getMaxGasPrice(?int $height = null): \stdClass
``

### getMinGasPrice

Returns current min gas price.

``
getMinGasPrice(): \stdClass
``

### getMissedBlocks

Returns missed blocks by validator public key.

``
getMissedBlocks(string $pubKey, ?int $height = null): \stdClass
``

### Error handling

Example of how you can handle errors and get the response body.

```php
use Noah\NoahAPI;
use GuzzleHttp\Exception\RequestException;

// create instance
$api = new NoahAPI('node url here');

try {
    // success response
    $response = $api->send('signed tx here');
} catch(RequestException $exception) {
    // short exception message
    $message = $exception->getMessage();
    
    // error response in json
    $content = $exception->getResponse()
                    ->getBody()
                    ->getContents();
    
    // error response as array
    $error = json_decode($content, true);                
}
```


## Using NoahSDK

### Sign transaction

Returns a signed tx.

###### Example

* Sign the <b>SendCoin</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahSendCoinTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahSendCoinTx::TYPE,
    'data' => [
        'coin' => 'NOAH',
        'to' => 'NOAHxDD6DD653557698cb9ad13639825A48fad435284d',
        'value' => '10'
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>SellCoin</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahSellCoinTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahSellCoinTx::TYPE,
    'data' => [
         'coinToSell' => 'NOAH',
         'valueToSell' => '1',
         'coinToBuy' => 'TEST',
         'minimumValueToBuy' => 1
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>SellAllCoin</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahSellAllCoinTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahSellAllCoinTx::TYPE,
    'data' => [
         'coinToSell' => 'TEST',
         'coinToBuy' => 'NOAH',
         'minimumValueToBuy' => 1
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>BuyCoin</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahBuyCoinTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahBuyCoinTx::TYPE,
    'data' => [
         'coinToBuy' => 'NOAH',
         'valueToBuy' => '1',
         'coinToSell' => 'TEST',
         'maximumValueToSell' => 1
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>CreateCoin</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahCreateCoinTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahCreateCoinTx::TYPE,
    'data' => [
        'name' => 'TEST COIN',
        'symbol' => 'TEST',
        'initialAmount' => '100',
        'initialReserve' => '10',
        'crr' => 10
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>DeclareCandidacy</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahDeclareCandidacyTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahDeclareCandidacyTx::TYPE,
    'data' => [
        'address' => 'NOAHx62422E5bC53bfDD93D5a2f6f7E1a527EDe071eF5',
        'pubkey' => 'Np066c65a9bdac10509f93c9e804eb1e91f3bff4a356d21bb3c4b18588b12a78c2',
        'commission' => 10,
        'coin' => 'NOAH',
        'stake' => '5'
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>Delegate</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahDelegateTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahDelegateTx::TYPE,
    'data' => [
        'pubkey' => 'Np59ee6baed4b03a9cc4ab2736ef25e80dcf1840f60e0f8c0559692719f505a0c4',
        'coin' => 'NOAH',
        'stake' => '5'
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>SetCandidateOn</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahSetCandidateOnTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahSetCandidateOnTx::TYPE,
    'data' => [
        'pubkey' => 'Np066c65a9bdac10509f93c9e804eb1e91f3bff4a356d21bb3c4b18588b12a78c2'
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>SetCandidateOff</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahSetCandidateOffTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahSetCandidateOffTx::TYPE,
    'data' => [
        'pubkey' => 'Np066c65a9bdac10509f93c9e804eb1e91f3bff4a356d21bb3c4b18588b12a78c2'
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>RedeemCheck</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahRedeemCheckTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahRedeemCheckTx::TYPE,
    'data' => [
        'check' => 'your check',
        'proof' => 'created by NoahCheck proof'
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>Unbond</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahUnbondTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahUnbondTx::TYPE,
    'data' => [
        'pubkey' => 'Np....',
        'coin' => 'NOAH',
        'value' => '1'
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>MultiSend</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahMultiSendTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahMultiSendTx::TYPE,
    'data' => [
        'list' => [
            [
                'coin' => 'NOAH',
                'to' => 'NOAHxDD6DD653557698cb9ad13639825A48fad435284d',
                'value' => '10'
            ], [
                'coin' => 'NOAH',
                'to' => 'NOAHxACa1D460A862B02F8E73c0D9f6ff18810Bed8bea',
                'value' => '15'
            ]
        ]
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

###### Example
* Sign the <b>EditCandidate</b> transaction

```php
use Noah\SDK\NoahTx;
use Noah\SDK\NoahCoins\NoahEditCandidateTx;

$tx = new NoahTx([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'gasPrice' => 1,
    'gasCoin' => 'NOAH',
    'type' => NoahEditCandidateTx::TYPE,
    'data' => [
        'pubkey' => 'candidate public key',
        'reward_address' => 'Noah address for rewards',
        'owner_address' => 'Noah address of owner'
    ],
    'payload' => '',
    'serviceData' => '',
    'signatureType' => NoahTx::SIGNATURE_SINGLE_TYPE // or SIGNATURE_MULTI_TYPE
]);

$tx->sign('your private key')
```

### Get fee of transaction

* Calculate fee of transaction. You can get fee AFTER signing or decoding transaction.
```php
use Noah\SDK\NoahTx;

$tx = new NoahTx([....]);
$sign = $tx->sign('your private key');

$tx->getFee();
```

### Get hash of transaction

* Get hash of encoded transaction
```php
use Noah\SDK\NoahTx;

$tx = new NoahTx([....]);
$sign = $tx->sign('your private key');

$hash = $tx->getHash();
```

* Get hash of decoded transaction
```php
use Noah\SDK\NoahTx;

$tx = new NoahTx('NOAHx....');

$hash = $tx->getHash();
```

### Decode transaction

Returns an array with transaction data.

###### Example

* Decode transaction

```php
use Noah\SDK\NoahTx;

$tx = new NoahTx('string tx');

// $tx->from, $tx->data, $tx->nonce ...

```

### Create Noah Check

###### Example

* Create check

```php
use Noah\SDK\NoahCheck;

$check = new NoahCheck([
    'nonce' => $nonce,
    'chainId' => NoahTx::MAINNET_CHAIN_ID, // or NoahTx::TESTNET_CHAIN_ID
    'dueBlock' => 999999,
    'coin' => 'NOAH',
    'value' => '10'
], 'your pass phrase');

echo $check->sign('your private key here'); 

// Nc.......

```

* Create proof

```php
use Noah\SDK\NoahCheck;

$check = new NoahCheck('your Noah address here', 'your pass phrase');

echo $check->createProof(); 
```

* Decode check

```php
use Noah\SDK\NoahCheck;

$check = new NoahCheck('your Noah check here');

$check->getBody();  // check body

$check->getOwnerAddress(); // check owner address
```

### Noah Wallet

###### Example

* Create wallet. This method returns generated seed, private key, public key, mnemonic and Noah address.

```php
use Noah\SDK\NoahWallet;

$wallet = NoahWallet::create();
```

* Generate mnemonic.

```php
use Noah\SDK\NoahWallet;

$mnemonic = NoahWallet::generateMnemonic();
```

* Get seed from mnemonic.

```php
use Noah\SDK\NoahWallet;

$seed = NoahWallet::mnemonicToSeed($mnemonic);
```

* Get private key from seed.

```php
use Noah\SDK\NoahWallet;

$privateKey = NoahWallet::seedToPrivateKey($seed);
```

* Get public key from private key.

```php
use Noah\SDK\NoahWallet;

$publicKey = NoahWallet::privateToPublic($privateKey);
```

* Get Noah address from public key.

```php
use Noah\SDK\NoahWallet;

$address = NoahWallet::getAddressFromPublicKey($publicKey);
```

## Tests

To run unit tests: 

```bash
vendor/bin/phpunit tests
```
