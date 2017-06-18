<?php
/**
 *
 *
 * @author  ouqiang<qingqianludao@gmail.com>
 */

namespace Etcd\Tests;


use Etcd\Client;

class LeaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Etcd\Lease;
     */
    protected $lease;

    public function setUp()
    {
        $client = new Client();
        $this->lease = $client->lease();
        $this->lease->setPretty(true);
    }

    public function testGrant()
    {
        $body = $this->lease->grant(3600);
        $this->assertArrayHasKey('ID', $body);
        $id = (int) $body['ID'];

        $body = $this->lease->timeToLive($id);
        $this->assertArrayHasKey('ID', $body);

        $this->lease->keepAlive($id);
        $this->assertArrayHasKey('ID', $body);

        $this->lease->revoke($id);
    }
}