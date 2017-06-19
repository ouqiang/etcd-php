# etcd-php
PHP client for Etcd v3

[![Build Status](https://travis-ci.org/ouqiang/etcd-php.png)](https://travis-ci.org/ouqiang/etcd-php)

[documentation](https://github.com/ouqiang/etcd-php/wiki)

Requirements
------------
* PHP5.5+
* Composer


Installation
------------
```shell
composer require start-point/etcd-php
```

Usage
------------

```php
<?php
require 'vendor/autoload.php';
$client = new \Etcd\Client('127.0.0.1:2379');

/*********** kv ***********/
// set value
$client->put('redis', '127.0.0.1:6379');

// set value and return previous value
$client->put('redis', '127.0.0.1:6579', ['prev_kv' => true]);

// set value with lease
$client->put('redis', '127.0.0.1:6579', ['lease' => 7587822882194199413]);

// get key value
$client->get('redis');

// delete key
$client->del('redis');

// compaction
$client->compaction(7);


/************ lease *****************/

$client->grant(3600);

// grant with ID
$client->grant(3600, 7587822882194199413);

// revoke a lease
$client->revoke(7587822882194199413);

// keep the lease alive
$client->keepAlive(7587822882194199413);

// retrieve lease information
$client->timeToLive(7587822882194199413);


/************ auth role user **************/

// enable authentication
$client->authEnable();

// disable authentication
$client->authDisable();

// get auth token
$client->authenticate('user', 'password');

// set auth token
$client->setToken($token);

// clear auth token
$client->clearToken();

// add a new role
$client->addRole('root');

// get detailed role information
$client->getRole('root');

// delete a specified role
$client->deleteRole('root');

// get lists of all roles
$client->roleList();

// add a new user
$client->addUser('user', 'password');

// get detailed user information
$client->getUser('root');

// delete a specified user
$client->deleteUser('root');

// get a list of all users.
$client->userList();

// change the password of a specified user
$client->changeUserPassword('user', 'new password');

// grant a role to a specified user
$client->grantUserRole('user', 'role');

// revoke a role of specified user
$client->revokeUserRole('user', 'role');

// grant a permission of a specified key or range to a specified role
$client->grantRolePermission('admin', \Etcd\Client::PERMISSION_READWRITE, 'redis');

// revoke a key or range permission of a specified role
$client->revokeRolePermission('admin', 'redis');
```
