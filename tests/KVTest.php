<?php
/**
 * @author  ouqiang<qingqianludao@gmail.com>
 */

namespace Etcd\Tests;

use Etcd\Client;

class KVTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \Etcd\KV;
     */
    protected $kv;
    protected $key = '/test';

    public function setUp()
    {
        $client = new Client();
        $this->kv = $client->kv();
        $this->kv->setPretty(true);
    }

    public function testPutAndRange()
    {
        $value = 'testput';
        $this->kv->put($this->key, $value);

        $body = $this->kv->get($this->key);
        $this->assertArrayHasKey($this->key, $body);
        $this->assertEquals($value, $body[$this->key]);
    }

    public function testDeleteRange()
    {
        $this->kv->del($this->key);
        $body = $this->kv->get($this->key);
        $this->assertArrayNotHasKey($this->key, $body);
    }
}