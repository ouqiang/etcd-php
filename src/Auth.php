<?php
/**
 * @author  ouqiang<qingqianludao@gmail.com>
 */

namespace Etcd;


class Auth extends Base
{
    // Role
    const URI_AUTH_ROLE_ADD = 'auth/role/add';
    const URI_AUTH_ROLE_GET = 'auth/role/get';
    const URI_AUTH_ROLE_DELETE = 'auth/role/delete';
    const URI_AUTH_ROLE_LIST = 'auth/role/list';

    // Authenticate
    const URI_AUTH_ENABLE = 'auth/enable';
    const URI_AUTH_DISABLE = 'auth/disable';
    const URI_AUTH_AUTHENTICATE = 'auth/authenticate';

    // User
    const URI_AUTH_USER_ADD = 'auth/user/add';
    const URI_AUTH_USER_GET = 'auth/user/get';
    const URI_AUTH_USER_DELETE = 'auth/user/delete';
    const URI_AUTH_USER_CHANGE_PASSWORD = 'auth/user/changepw';
    const URI_AUTH_USER_LIST = 'auth/user/list';

    const URI_AUTH_ROLE_GRANT = 'auth/role/grant';
    const URI_AUTH_ROLE_REVOKE = 'auth/role/revoke';


    const URI_AUTH_USER_GRANT = 'auth/user/grant';
    const URI_AUTH_USER_REVOKE = 'auth/user/revoke';


    const PERMISSION_READ = 0;
    const PERMISSION_WRITE = 1;
    const PERMISSION_READWRITE = 2;

    /**
     * enable authentication
     *
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function enable() 
    {
        $body = $this->request(self::URI_AUTH_ENABLE);
        Token::set(null);

        return $body;
    }

    /**
     * enable authentication
     *
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function disable()
    {
        $body = $this->request(self::URI_AUTH_DISABLE);
        Token::set(null);

        return $body;
    }

    /**
     * @param  string $user
     * @param  string $password
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function authenticate($user, $password)
    {
        $params = [
            'name' => $user,
            'password' => $password,
        ];

        $body = $this->request(self::URI_AUTH_AUTHENTICATE, $params);
        if (isset($body['token'])) {
            Token::set($body['token']);
        }
        if ($this->pretty && isset($body['token'])) {
            return $body['token'];
        }

        return $body;
    }


    /**
     * add a new role.
     *
     * @param string $name
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function addRole($name)
    {
        $params = [
            'name' => $name,
        ];

        $body = $this->request(self::URI_AUTH_ROLE_ADD, $params);

        return $body;
    }

    /**
     * get detailed role information.
     *
     * @param  string $role
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function getRole($role)
    {
        $params = [
            'role' => $role,
        ];

        $body = $this->request(self::URI_AUTH_ROLE_GET, $params);
        $body = $this->decodeBodyForFields(
            $body,
            'perm',
            ['key', 'range_end',]
        );
        if ($this->pretty && isset($body['perm'])) {
            return $body['perm'];
        }

        return $body;
    }

    /**
     * delete a specified role.
     *
     * @param  string $role
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function deleteRole($role)
    {
        $params = [
            'role' => $role,
        ];

        $body = $this->request(self::URI_AUTH_ROLE_DELETE, $params);

        return $body;
    }

    /**
     * get lists of all roles
     *
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function roleList()
    {
        $body = $this->request(self::URI_AUTH_ROLE_LIST);

        if ($this->pretty && isset($body['roles'])) {
            return $body['roles'];
        }

        return $body;
    }


    /**
     * add a new user
     *
     * @param string $user
     * @param string $password
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function addUser($user, $password)
    {
        $params = [
            'name' => $user,
            'password' => $password,
        ];

        $body = $this->request(self::URI_AUTH_USER_ADD, $params);

        return $body;
    }

    /**
     * get detailed user information
     *
     * @param  string $user
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function getUser($user)
    {
        $params = [
            'name' => $user,
        ];

        $body = $this->request(self::URI_AUTH_USER_GET, $params);
        if ($this->pretty && isset($body['roles'])) {
            return $body['roles'];
        }

        return $body;
    }

    /**
     * deletes a specified user
     *
     * @param string $user
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function deleteUser($user)
    {
        $params = [
            'name' => $user,
        ];

        $body = $this->request(self::URI_AUTH_USER_DELETE, $params);

        return $body;
    }

    /**
     * get a list of all users.
     *
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function userList() 
    {
        $body = $this->request(self::URI_AUTH_USER_LIST);
        if ($this->pretty && isset($body['users'])) {
            return $body['users'];
        }

        return $body;
    }

    /**
     * change the password of a specified user.
     *
     * @param string $user
     * @param string $password
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function changeUserPassword($user, $password)
    {
        $params = [
            'name' => $user,
            'password' => $password,
        ];

        $body = $this->request(self::URI_AUTH_USER_CHANGE_PASSWORD, $params);

        return $body;
    }

    /**
     *
     *
     * @param string      $role
     * @param int         $permType
     * @param string      $key
     * @param string|null $rangeEnd
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function grantRolePermission($role, $permType, $key, $rangeEnd = null)
    {
        $params = [
            'name' => $role,
            'perm' => [
                'permType' => $permType,
                'key' => base64_encode($key),
            ],
        ];
        if ($rangeEnd !== null) {
            $params['perm']['range_end'] = base64_encode($rangeEnd);
        }

        $body = $this->request(self::URI_AUTH_ROLE_GRANT, $params);

        return $body;
    }

    /**
     * revoke a key or range permission of a specified role.
     *
     * @param string      $role
     * @param string      $key
     * @param string|null $rangeEnd
     */
    public function revokeRolePermission($role, $key, $rangeEnd = null)
    {
        $params = [
            'role' => $role,
            'key' => $key,
        ];
        if ($rangeEnd !== null) {
            $params['range_end'] = $rangeEnd;
        }

        $body = $this->request(self::URI_AUTH_ROLE_REVOKE, $params);

        return $body;
    }

    /**
     * grants a role to a specified user.
     *
     * @param  string $user
     * @param  string $role
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function grantUserRole($user, $role)
    {
        $params = [
            'user' => $user,
            'role' => $role,
        ];

        $body = $this->request(self::URI_AUTH_USER_GRANT, $params);

        return $body;
    }

    /**
     * revoke a role of specified user.
     *
     * @param string $user
     * @param string $role
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function revokeUserRole($user, $role)
    {
        $params = [
            'name' => $user,
            'role' => $role,
        ];

        $body = $this->request(self::URI_AUTH_USER_REVOKE, $params);

        return $body;
    }
}