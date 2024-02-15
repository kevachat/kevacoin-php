<?php

declare(strict_types=1);

namespace Kevachat\Kevacoin;

class Client
{
    private $_id = 0;

    private $_curl;
    private $_protocol;
    private $_host;
    private $_port;

    public function __construct(
        string $protocol,
        string $host,
        int $port,
        string $username,
        string $password
    )
    {
        $this->_protocol = $protocol;
        $this->_host = $host;
        $this->_port = $port;

        $this->_curl = curl_init();

        curl_setopt_array(
            $this->_curl,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_FRESH_CONNECT  => true,
                CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
                CURLOPT_USERPWD        => $username . ':' . $password,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/plain',
                ],
            ]
        );
    }

    public function __destruct()
    {
        curl_close(
            $this->_curl
        );
    }

    private function _prepare(
        string $url,
        string $method = 'POST',
        array $data = []
    ) {

        curl_setopt(
            $this->_curl,
            CURLOPT_URL,
            $this->_protocol . '://' . $this->_host . ':' . $this->_port . $url
        );

        curl_setopt(
            $this->_curl,
            CURLOPT_CUSTOMREQUEST,
            $method
        );

        if ($method == 'POST' && $data)
        {
            curl_setopt(
                $this->_curl,
                CURLOPT_POSTFIELDS,
                json_encode(
                    $data
                )
            );
        }
    }

    private function _execute(
        bool $json = true
    ): ?array
    {
        $response = curl_exec(
            $this->_curl
        );

        $errorNumber = curl_errno(
            $this->_curl
        );

        $errorText = curl_error(
            $this->_curl
        );

        if ($response)
        {
            return $json ? json_decode($response, true) : $response;
        }

        return null;
    }

    public function getBlockCount(): ?int
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getblockcount',
                'params' => [],
                'id'     => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && is_int($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function getBalance(?string $address = null, ?int $minconf = null): ?float
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getbalance',
                'params' =>
                [
                    $address,
                    $minconf
                ],
                'id'     => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && is_float($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function getBlockHash(
        int $block
    ): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getblockhash',
                'params' =>
                [
                    $block
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && 64 == strlen($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function getBlock(
        string $hash
    ): mixed
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getblock',
                'params' =>
                [
                    $hash
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (!empty($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function getRawTransaction(
        string $txid,
        bool $decode = true
    ): mixed
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getrawtransaction',
                'params' =>
                [
                    $txid,
                    $decode
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (!empty($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function decodeRawTransaction(
        string $txid
    ): mixed
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'decoderawtransaction',
                'params' =>
                [
                    $txid
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (!empty($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function kevaFilter(
        string $namespace,
        ?string $regexp = '',
        ?int $maxage = 0,
        ?int $from = 0,
        ?int $nb = 0,
        ?bool $stat = false,
    ): mixed
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_filter',
                'params' =>
                (
                    $stat?
                    [
                        $namespace,
                        $regexp,
                        $maxage,
                        $from,
                        $nb,
                        'stat'
                    ]
                    :
                    [
                        $namespace,
                        $regexp,
                        $maxage,
                        $from,
                        $nb
                    ]
                ),
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (!empty($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function kevaGet(
        string $namespace,
        string $key
    ): ?array
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_get',
                'params' =>
                [
                    $namespace,
                    $key
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (
            !empty($response['result']) &&
            !empty($response['result']['key']) &&
            !empty($response['result']['value']) &&
            !empty($response['result']['height']) &&
            isset($response['result']['vout'])
        )
        {
            return $response['result'];
        }

        return null;
    }

    // Pay attention:
    // for some reasons, wallet hide namespaces from list where pending transaction exist
    // to get some data e.g. namespace name, use keva_get / _KEVA_NS_ with max height value instead of this method
    public function kevaListNamespaces(): ?array
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_list_namespaces',
                'params' => [],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (!empty($response['result']) && is_array($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function kevaPut(
        string $namespace,
        string $key,
        string $value
    ): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_put',
                'params' => [
                    $namespace,
                    $key,
                    $value
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (!empty($response['result']) && !empty($response['result']['txid']) && is_string($response['result']['txid']))
        {
            return $response['result']['txid'];
        }

        return null;
    }

    public function kevaPending(): ?array
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_pending',
                'params' => [],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (!empty($response['result']) && is_array($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function kevaNamespace(
        string $name
    ): ?array
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_namespace',
                'params' => [
                    $name
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (!empty($response['result']) && !empty($response['result']['txid']) && !empty($response['result']['namespaceId']))
        {
            return $response['result'];
        }

        return null;
    }

    public function getNewAddress(): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getnewaddress',
                'params' => [],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (!empty($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function getReceivedByAddress(string $address, int $minconf = 0): ?float
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getreceivedbyaddress',
                'params' =>
                [
                    $address,
                    $minconf
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && is_float($response['result']))
        {
            return $response['result'];
        }

        return null;
    }
}
