<?php
/**
 * @author  ouqiang<qingqianludao@gmail.com>
 */

namespace Etcd;


class Lease extends Base
{
    const URI_GRANT = 'lease/grant';
    const URI_REVOKE = 'kv/lease/revoke';
    const URI_KEEPALIVE = 'lease/keepalive';
    const URI_TIMETOLIVE = 'kv/lease/timetolive';

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
}