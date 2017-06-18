<?php
/**
 * @see  https://github.com/coreos/etcd/blob/master/etcdserver/etcdserverpb/rpc.proto
 * @author  ouqiang<qingqianludao@gmail.com>
 */

namespace Etcd;

use GuzzleHttp\Client as HttpClient;

class Client
{
    /**
     * @var string host:port
     */
    protected $server;
    /**
     * @var string api version
     */
    protected $version;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    public function __construct($server = '127.0.0.1:2379', $version = 'v3alpha')
    {
        $this->server = rtrim($server);
        if (strpos($this->server, 'http') !== 0) {
            $this->server = 'http://' . $this->server;
        }
        $this->version = trim($version);

        $baseUri = sprintf('%s/%s/', $this->server, $this->version);
        $this->httpClient = new HttpClient(
            [
                'base_uri' => $baseUri,
                'timeout'  => 30,
            ]
        );
    }


    public function kv()
    {
        return new KV($this->httpClient);
    }

    public function lease()
    {
        return new Lease($this->httpClient);
    }

}