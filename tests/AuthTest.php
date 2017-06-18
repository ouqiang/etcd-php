<?php
/**
 * @author  ouqiang<qingqianludao@gmail.com>
 */

namespace Etcd\Tests;


use Etcd\Client;
use Etcd\Token;

class AuthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Etcd\Auth;
     */
    protected $auth;

    /**
     * @var string
     */
    protected $role = 'root';
    protected $user = 'root';
    protected $password = '123456';

    public function setUp()
    {
        $client = new Client();
        $this->auth = $client->auth();
        $this->auth->setPretty(true);
    }

    public function testAddRole()
    {
        $this->auth->addRole($this->role);
    }

    public function testAddUser()
    {
        $this->auth->addUser($this->user, $this->password);
    }

    public function testChangeUserPassword()
    {
        $this->auth->changeUserPassword($this->user, '456789');
        $this->auth->changeUserPassword($this->user, $this->password);
    }

    public function testGrantUserRole()
    {
        $this->auth->grantUserRole($this->user, $this->role);
    }

    public function testGetRole()
    {
        $this->auth->getRole($this->role);
    }

    public function testRoleList()
    {
        $body = $this->auth->roleList();
        if (!in_array($this->role, $body)) {
            $this->fail('role not exist');
        }
    }

    public function testGetUser()
    {
        $this->auth->getUser($this->user);
    }

    public function testUserList()
    {
        $body = $this->auth->userList();
        if (!in_array($this->user, $body)) {
            $this->fail('user not exist');
        }
    }

    public function testGrantRolePermission()
    {
        $this->auth->grantRolePermission($this->role,
            \Etcd\Auth::PERMISSION_READWRITE, '\0', 'z' );
    }

    public function testAuthenticate()
    {
        $this->auth->enable();
        $this->auth->authenticate($this->user, $this->password);
        $this->auth->addUser('admin', '345678');
        $this->auth->addRole('admin');
        $this->auth->grantUserRole('admin', 'admin');

        $this->auth->disable();
        $this->auth->deleteRole('admin');
        $this->auth->deleteUser('admin');
    }

    public function testRevokeRolePermission()
    {
        $this->auth->revokeRolePermission($this->role, '\0', 'z');
    }

    public function testRevokeUserRole()
    {
        $this->auth->revokeUserRole($this->user, $this->role);
    }

    public function testDeleteRole()
    {
        $this->auth->deleteRole($this->role);
    }

    public function testDeleteUser()
    {
        $this->auth->deleteUser($this->user);
    }
}