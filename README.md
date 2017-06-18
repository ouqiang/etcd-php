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
git clone https://github.com/ouqiang/etcd-php
cd etcd-php
composer install
```

Usage
------------

```php
<?php
require 'vendor/autoload.php';

/*********** kv api ***********/

$client = new \Etcd\Client();
$kv = $client->kv();

// set value
$kv->put('redis', '127.0.0.1:6379');

// set value and return previous value
$kv->put('redis', '127.0.0.1:6579', ['prev_kv' => true]);

// set value with lease
$kv->put('redis', '127.0.0.1:6579', ['lease' => 7587822882194199413]);

// get key value
$client->get('redis');

// delete key
$kv->del('redis');

// compaction
$kv->compaction(7);


/************ lease api *****************/
$lease = $client->lease();

$lease->grant(3600);

// grant with ID
$lease->grant(3600, 7587822882194199413);

// revokes a lease
$lease->revoke(7587822882194199413);

// keeps the lease alive
$lease->keepAlive(7587822882194199413);

// retrieves lease information
$lease->timeToLive(7587822882194199413);

```
