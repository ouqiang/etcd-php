<?php
/**
 * @author qiang.ou<qingqianludao@gmail.com>
 */

namespace Etcd\Tests;

use Etcd\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Etcd\Client;
     */
    protected $client;

    protected $key = '/test';

    protected $role = 'root';
    protected $user = 'root';
    protected $password = '123456';

    public function setUp()
    {
        $this->client = new Client();
        $this->client->setPretty(true);
    }

    public function testPutAndRange()
    {
        $value = 'testput';
        $this->client->put($this->key, $value);

        $body = $this->client->get($this->key);
        $this->assertArrayHasKey($this->key, $body);
        $this->assertEquals($value, $body[$this->key]);
    }

    public function testGetAllKeys()
    {
        $body = $this->client->getAllKeys();
        $this->assertNotEmpty($body);
    }

    public function testGetKeysWithPrefix()
    {
        $body = $this->client->getKeysWithPrefix('/');
        $this->assertNotEmpty($body);
    }

    public function testDeleteRange()
    {
        $this->client->del($this->key);
        $body = $this->client->get($this->key);
        $this->assertArrayNotHasKey($this->key, $body);
    }

    public function testGrant()
    {
        $body = $this->client->grant(3600);
        $this->assertArrayHasKey('ID', $body);
        $id = (int) $body['ID'];

        $body = $this->client->timeToLive($id);
        $this->assertArrayHasKey('ID', $body);

        $this->client->keepAlive($id);
        $this->assertArrayHasKey('ID', $body);

        $this->client->revoke($id);
    }

    public function testAddRole()
    {
        $this->client->addRole($this->role);
    }

    public function testAddUser()
    {
        $this->client->addUser($this->user, $this->password);
    }

    public function testChangeUserPassword()
    {
        $this->client->changeUserPassword($this->user, '456789');
        $this->client->changeUserPassword($this->user, $this->password);
    }

    public function testGrantUserRole()
    {
        $this->client->grantUserRole($this->user, $this->role);
    }

    public function testGetRole()
    {
        $this->client->getRole($this->role);
    }

    public function testRoleList()
    {
        $body = $this->client->roleList();
        if (!in_array($this->role, $body)) {
            $this->fail('role not exist');
        }
    }

    public function testGetUser()
    {
        $this->client->getUser($this->user);
    }

    public function testUserList()
    {
        $body = $this->client->userList();
        if (!in_array($this->user, $body)) {
            $this->fail('user not exist');
        }
    }

    public function testGrantRolePermission()
    {
        $this->client->grantRolePermission($this->role,
            Client::PERMISSION_READWRITE, '\0', 'z' );
    }

    public function testAuthenticate()
    {
        $this->client->authEnable();
        $token = $this->client->authenticate($this->user, $this->password);
        $this->client->setToken($token);
        $this->client->addUser('admin', '345678');
        $this->client->addRole('admin');
        $this->client->grantUserRole('admin', 'admin');

        $this->client->authDisable();
        $this->client->deleteRole('admin');
        $this->client->deleteUser('admin');
    }

    public function testRevokeRolePermission()
    {
        $this->client->revokeRolePermission($this->role, '\0', 'z');
    }

    public function testRevokeUserRole()
    {
        $this->client->revokeUserRole($this->user, $this->role);
    }

    public function testDeleteRole()
    {
        $this->client->deleteRole($this->role);
    }

    public function testDeleteUser()
    {
        $this->client->deleteUser($this->user);
    }
}