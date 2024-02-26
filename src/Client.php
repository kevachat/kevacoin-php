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
        if ($response = curl_exec($this->_curl))
        {
            return $json ? json_decode($response, true) : $response;
        }

        return null;
    }

    public function getError(?int &$code = null): mixed
    {
        $code = curl_errno(
            $this->_curl
        );

        return curl_error(
            $this->_curl
        );
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

    public function getBalance(?string $account = null, ?int $minconf = null): ?float
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getbalance',
                'params' =>
                [
                    $account,
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

    /*
     * keva_put "namespace" "key" "value" "address"
     *
     * Insert or update a key value pair in the given namespace.
     *
     * Arguments:
     * 1. "namespace" (string, required) the namespace to insert the key to
     * 2. "key"       (string, required) value for the key
     * 3. "value"     (string, required) value for the name
     * 4. "address"   (string, optional) transfer the namespace to the given address (Version 0.16.7.0 or above)
     *
     * Result:
     * "txid"         (string) the keva_put's txid
     */
    public function kevaPut(
        string  $namespace,
        string  $key,
        string  $value,
        # ?string $address = null // disabled as not stable
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
                    $value,
                    # $address // disabled as not stable
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

    public function getNewAddress(?string $account = null, $address_type = null): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getnewaddress',
                'params' =>
                [
                    $account,
                    $address_type
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

    public function getReceivedByAddress(string $address, ?int $minconf = 0): ?float
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

    public function getReceivedByAccount(string $account, ?int $minconf = 0): ?float
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getreceivedbyaccount',
                'params' =>
                [
                    $account,
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

    public function sendToAddress(
        string  $address,
        float   $amount,
        ?string $comment = null,
        ?string $comment_to = null,
        ?bool   $subtractfeefromamount = false,
                $replaceable = null,
                $conf_target = null,
                $estimate_mode = null
    ): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'sendtoaddress',
                'params' =>
                [
                    $address,
                    $amount,
                    $comment,
                    $comment_to,
                    $subtractfeefromamount,
                    $replaceable,
                    $conf_target,
                    $estimate_mode
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

    public function sendFrom(
        string  $fromaccount,
        string  $toaddress,
        float   $amount,
        ?int    $minconf = null,
        ?string $comment = null,
        ?string $comment_to = null
    ): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'sendfrom',
                'params' =>
                [
                    $fromaccount,
                    $toaddress,
                    $amount,
                    $minconf,
                    $comment,
                    $comment_to
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

    public function getAccount(string $address): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getaccount',
                'params' =>
                [
                    $address
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && is_string($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function getAccountAddress(string $account): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getaccountaddress',
                'params' =>
                [
                    $account
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && is_string($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function getAddressesByAccount(string $account): ?array
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'getaddressesbyaccount',
                'params' =>
                [
                    $account
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && is_array($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function listAccounts(): ?array
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'listaccounts',
                'params' => [],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && is_array($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    /*
     * keva_group_filter ("namespaceId" ("initiator" "regexp" ("from" ("nb" ("stat")))))
     *
     * Scan and list keys matching a regular expression.
     *
     * Arguments:
     * 1. "namespace"   (string) namespace Id
     * 2. "initiator"   (string, optional) Options are "all", "self" and "other", default is "all". "all": all the namespaces, whose participation in the group is initiated by this namespace or other namespaces. "self": only the namespace whose participation is initiated by this namespace. "other": only the namespace whose participation is initiated by other namespaces.
     * 3. "regexp"      (string, optional) filter keys with this regexp
     * 4. "maxage"      (numeric, optional, default=96000) only consider names updated in the last "maxage" blocks; 0 means all names
     * 5. "from"        (numeric, optional, default=0) return from this position onward; index starts at 0
     * 6. "nb"          (numeric, optional, default=0) return only "nb" entries; 0 means all
     * 7. "stat"        (string, optional) if set to the string "stat", print statistics instead of returning the names
     *
     * Result:
     * [
     * {
     *     "key": xxxxx,            (string) the requested key
     *     "value": xxxxx,          (string) the key's current value
     *     "txid": xxxxx,           (string) the key's last update tx
     *     "height": xxxxx,         (numeric) the key's last update height
     * },
     * ...
     * ]
     */
    public function kevaGroupFilter(
        string  $namespace,
        ?string $initiator = 'all',
        ?string $regexp = '',
        ?int    $maxage = 0,
        ?int    $from = 0,
        ?int    $nb = 0,
        # ?string $stat = null, // disabled as not stable
    ): ?array
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_group_filter',
                'params' =>
                [
                    $namespace,
                    $initiator,
                    $regexp,
                    $maxage,
                    $from,
                    $nb,
                    # $stat // disabled as not stable
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && is_array($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    public function kevaGroupGet(
        string $namespace,
        string $key
    ): ?array
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_group_get',
                'params' =>
                [
                    $namespace,
                    $key
                ],
                'id' => $this->_id
            ]
        );

        $response = $this->_execute();

        if (isset($response['result']) && is_array($response['result']))
        {
            return $response['result'];
        }

        return null;
    }

    /*
     * Join the other namespace, so that the data in both namespaces can be combined. See keva_group_leave.
     *
     * Arguments:
     * 1. "my_namespace"         (string, required) the namespace to join to <other_namespace>
     * 2. "other_namespace"      (string, required) the target namespace to join to
     *
     * Result:
     * "txid"                    (string) the keva_put's txid
     */
    public function kevaGroupJoin(
        string $source,
        string $target
    ): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_group_join',
                'params' =>
                [
                    $source,
                    $target
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

    /*
     * Leave the other namespace so that the data are not to be combined with it. See keva_group_join.
     *
     * Arguments:
     * 1. "my_namespace"    (string, required) the namespace to leave <other_namespace>
     * 2. "other_namespace" (string, required) the target namespace to leave
     *
     * Result:
     * "txid"               (string) the keva_put's txid
     */
    public function kevaGroupLeave(
        string $source,
        string $target
    ): ?string
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_group_leave',
                'params' =>
                [
                    $source,
                    $target
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

    /*
     * List namespaces that are in the same group as the given namespace.
     *
     * Arguments:
     * 1. "namespace"   (string) namespace Id
     * 2. "maxage"      (numeric, optional, default=96000) only consider namespaces updated in the last "maxage" blocks; 0 means all namespaces
     * 3. "from"        (numeric, optional, default=0) return from this position onward; index starts at 0
     * 4. "nb"          (numeric, optional, default=0) return only "nb" entries; 0 means all
     * 5. "stat"        (string, optional) if set to the string "stat", print statistics instead of returning the names
     *
     * Result:
     * [
     * {
     *     "key": xxxxx,            (string) the requested key
     *     "value": xxxxx,          (string) the key's current value
     *     "txid": xxxxx,           (string) the key's last update tx
     *     "height": xxxxx,         (numeric) the key's last update height
     * },
     * ...
     * ]
     */
    public function kevaGroupShow(
        string  $namespace,
        ?int    $maxage = 0,
        ?int    $from = 0,
        ?int    $nb = 0,
        ?string $stat = null
    ): ?array
    {
        $this->_id++;

        $this->_prepare(
            '',
            'POST',
            [
                'method' => 'keva_group_show',
                'params' =>
                [
                    $namespace,
                    $maxage,
                    $from,
                    $nb,
                    $stat
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
}
