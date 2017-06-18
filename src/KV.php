<?php
/**
 * @author  ouqiang<qingqianludao@gmail.com>
 */

namespace Etcd;


class KV extends Base
{
    const URI_PUT = 'kv/put';
    const URI_RANGE = 'kv/range';
    const URI_DELETE_RANGE = 'kv/deleterange';
    const URI_TXN = 'kv/txn';
    const URI_COMPACTION = 'kv/compaction';

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