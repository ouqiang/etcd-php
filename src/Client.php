<?php
/**
 * @see  https://github.com/coreos/etcd/blob/master/etcdserver/etcdserverpb/rpc.proto
 * @author  ouqiang<qingqianludao@gmail.com>
 */

namespace Etcd;

use GuzzleHttp\Client as HttpClient;
use Etcd\Exceptions\ConnectionException;

class Client
{
    // KV
    const URI_PUT = 'kv/put';
    const URI_RANGE = 'kv/range';
    const URI_DELETE_RANGE = 'kv/deleterange';
    const URI_TXN = 'kv/txn';
    const URI_COMPACTION = 'kv/compaction';

    // Lease
    const URI_GRANT = 'lease/grant';
    const URI_REVOKE = 'kv/lease/revoke';
    const URI_KEEPALIVE = 'lease/keepalive';
    const URI_TIMETOLIVE = 'kv/lease/timetolive';

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

    /**
     * @var bool 友好输出, 只返回所需字段
     */
    protected $pretty = false;

    /**
     * @var string|null auth token
     */
    protected $token = null;

    public function __construct($servers = '127.0.0.1:2379', $version = 'v3alpha')
    {
        $servers = is_string($servers) ? [$servers] : $servers;
        if (!is_array($servers)) {
            throw new ConnectionException('Server Hosts format is invalid.');
        }

        foreach ($servers as $server) {
            $connected = $this->connect($server, $version);
            if ($connected) {
                return;
            }
        }
        throw new ConnectionException('No etcd server can connect.');
    }

    public function connect($server, $version)
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

        try {
            $this->userList();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function setPretty($enabled)
    {
        $this->pretty = $enabled;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function clearToken()
    {
        $this->token = null;
    }

    // region kv

    /**
     * Put puts the given key into the key-value store.
     * A put request increments the revision of the key-value
     * store\nand generates one event in the event history.
     *
     * @param string $key
     * @param string $value
     * @param array  $options 可选参数
     *        int64  lease
     *        bool   prev_kv
     *        bool   ignore_value
     *        bool   ignore_lease
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function put($key, $value, array $options = [])
    {
        $params = [
            'key' => $key,
            'value' => $value,
        ];

        $params = $this->encode($params);
        $options = $this->encode($options);
        $body = $this->request(self::URI_PUT, $params, $options);
        $body = $this->decodeBodyForFields(
            $body,
            'prev_kv',
            ['key', 'value',]
        );

        if (isset($body['prev_kv']) && $this->pretty) {
            return $this->convertFields($body['prev_kv']);
        }

        return $body;
    }

    /**
     * Gets the key or a range of keys
     *
     * @param  string $key
     * @param  array $options
     *         string range_end
     *         int    limit
     *         int    revision
     *         int    sort_order
     *         int    sort_target
     *         bool   serializable
     *         bool   keys_only
     *         bool   count_only
     *         int64  min_mod_revision
     *         int64  max_mod_revision
     *         int64  min_create_revision
     *         int64  max_create_revision
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function get($key, array $options = [])
    {
        $params = [
            'key' => $key,
        ];
        $params = $this->encode($params);
        $options = $this->encode($options);
        $body = $this->request(self::URI_RANGE, $params, $options);
        $body = $this->decodeBodyForFields(
            $body,
            'kvs',
            ['key', 'value',]
        );

        if (isset($body['kvs']) && $this->pretty) {
            return $this->convertFields($body['kvs']);
        }

        return $body;
    }

    /**
     * get all keys
     *
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function getAllKeys()
    {
        return $this->get("\0", ['range_end' => "\0"]);
    }

    /**
     * get all keys with prefix
     *
     * @param  string $prefix
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function getKeysWithPrefix($prefix)
    {
        $prefix = trim($prefix);
        if (!$prefix) {
            return [];
        }
        $lastIndex = strlen($prefix) - 1;
        $lastChar = $prefix[$lastIndex];
        $nextAsciiCode = ord($lastChar) + 1;
        $rangeEnd = $prefix;
        $rangeEnd[$lastIndex] = chr($nextAsciiCode);

        return $this->get($prefix, ['range_end' => $rangeEnd]);
    }

    /**
     * Removes the specified key or range of keys
     *
     * @param string $key
     * @param array  $options
     *        string range_end
     *        bool   prev_kv
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function del($key, array $options = [])
    {
        $params = [
            'key' => $key,
        ];
        $params = $this->encode($params);
        $options = $this->encode($options);
        $body = $this->request(self::URI_DELETE_RANGE, $params, $options);
        $body = $this->decodeBodyForFields(
            $body,
            'prev_kvs',
            ['key', 'value',]
        );

        if (isset($body['prev_kvs']) && $this->pretty) {
            return $this->convertFields($body['prev_kvs']);
        }

        return $body;
    }

    /**
     * Compact compacts the event history in the etcd key-value store.
     * The key-value\nstore should be periodically compacted
     * or the event history will continue to grow\nindefinitely.
     *
     * @param int64 $revision
     *
     * @param bool|false $physical
     *
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function compaction($revision, $physical = false)
    {
        $params = [
            'revision' => $revision,
            'physical' => $physical,
        ];

        $body = $this->request(self::URI_COMPACTION, $params);

        return $body;
    }

    // endregion kv

    // region lease

    /**
     * LeaseGrant creates a lease which expires if the server does not receive a
     * keepAlive\nwithin a given time to live period. All keys attached to the lease
     * will be expired and\ndeleted if the lease expires.
     * Each expired key generates a delete event in the event history.",
     *
     * @param int64 $ttl  TTL is the advisory time-to-live in seconds.
     * @param int64 $id   ID is the requested ID for the lease.
     *                    If ID is set to 0, the lessor chooses an ID.
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function grant($ttl, $id = 0)
    {
        $params = [
            'TTL' => $ttl,
            'ID' => $id,
        ];


        $body = $this->request(self::URI_GRANT, $params);

        return $body;
    }

    /**
     * revokes a lease. All keys attached to the lease will expire and be deleted.
     *
     * @param  int64 $id ID is the lease ID to revoke. When the ID is revoked,
     *               all associated keys will be deleted.
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function revoke($id)
    {
        $params = [
            'ID' => $id,
        ];

        $body = $this->request(self::URI_REVOKE, $params);

        return $body;
    }

    /**
     * keeps the lease alive by streaming keep alive requests
     * from the client\nto the server and streaming keep alive responses
     * from the server to the client.
     *
     * @param int64 $id  ID is the lease ID for the lease to keep alive.
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function keepAlive($id)
    {
        $params = [
            'ID' => $id,
        ];

        $body = $this->request(self::URI_KEEPALIVE, $params);

        if (!isset($body['result'])) {
            return $body;
        }
        // response "result" field, etcd bug?
        return [
            'ID' => $body['result']['ID'],
            'TTL' => $body['result']['TTL'],
        ];
    }

    /**
     * retrieves lease information.
     *
     * @param int64 $id ID is the lease ID for the lease.
     * @param bool|false $keys
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function timeToLive($id, $keys = false)
    {
        $params = [
            'ID' => $id,
            'keys' => $keys,
        ];

        $body = $this->request(self::URI_TIMETOLIVE, $params);

        if (isset($body['keys'])) {
            $body['keys'] = array_map(function($value) {
                return base64_decode($value);
            }, $body['keys']);
        }

        return $body;
    }

    // endregion lease

    // region auth

    /**
     * enable authentication
     *
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function authEnable()
    {
        $body = $this->request(self::URI_AUTH_ENABLE);
        $this->clearToken();

        return $body;
    }

    /**
     * disable authentication
     *
     * @return array|\GuzzleHttp\Exception\BadResponseException
     */
    public function authDisable()
    {
        $body = $this->request(self::URI_AUTH_DISABLE);
        $this->clearToken();

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
     * delete a specified user
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
     * grant a permission of a specified key or range to a specified role.
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
     * grant a role to a specified user.
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

    // endregion auth

    /**
     * 发送HTTP请求
     *
     * @param  string $uri
     * @param  array  $params  请求参数
     * @param  array  $options 可选参数
     * @return array|BadResponseException
     */
    protected function request($uri, array $params = [], array $options = [])
    {
        if ($options) {
            $params = array_merge($params, $options);
        }
        // 没有参数, 设置一个默认参数
        if (!$params) {
            $params['php-etcd-client'] = 1;
        }
        $data = [
            'json' => $params,
        ];
        if ($this->token) {
            $data['headers'] = ['Grpc-Metadata-Token' => $this->token];
        }

        $response = $this->httpClient->request('post', $uri, $data);
        $content = $response->getBody()->getContents();

        $body = json_decode($content, true);
        if ($this->pretty && isset($body['header'])) {
            unset($body['header']);
        }

        return $body;
    }

    /**
     * string类型key用base64编码
     *
     * @param array $data
     * @return array
     */
    protected function encode(array $data)
    {

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = base64_encode($value);
            }
        }

        return $data;
    }

    /**
     * 指定字段base64解码
     *
     * @param array  $body
     * @param string $bodyKey
     * @param array  $fields  需要解码的字段
     * @return array
     */
    protected function decodeBodyForFields(array $body, $bodyKey, array $fields)
    {
        if (!isset($body[$bodyKey])) {
            return $body;
        }
        $data = $body[$bodyKey];
        if (!isset($data[0])) {
            $data = array($data);
        }
        foreach ($data as $key => $value) {
            foreach ($fields as $field) {
                if (isset($value[$field])) {
                    $data[$key][$field] = base64_decode($value[$field]);
                }
            }
        }

        if (isset($body[$bodyKey][0])) {
            $body[$bodyKey] = $data;
        } else {
            $body[$bodyKey] = $data[0];
        }

        return $body;
    }

    protected function convertFields(array $data)
    {
        if (!isset($data[0])) {
            return $data['value'];
        }

        $map = [];
        foreach ($data as $index => $value) {
            $key = $value['key'];
            $map[$key] = $value['value'];
        }

        return $map;
    }
}

$client = new Client('47.91.208.228:2379');
