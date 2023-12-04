# kevacoin-php

kevacoin library for PHP

```
$client = new \Kevachat\Kevacoin\Client(
    KEVA_PROTOCOL,
    KEVA_HOST,
    KEVA_PORT,
    KEVA_USERNAME,
    KEVA_PASSWORD
);

echo $client->getBlockCount();

```