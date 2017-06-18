<?php
/**
 * @author  ouqiang<qingqianludao@gmail.com>
 */
namespace Etcd;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;

class Base {
    /**
     * @var HttpClient;
     */
    private $httpClient;

    /**
     * @var bool 友好输出, 只返回所需字段
     */
    protected $pretty = false;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function setPretty($enabled)
    {
        $this->pretty = $enabled;
    }


    /**
     * 发送HTTP请求
     *
     * @param  string $uri
     * @param  array  $params  请求必填参数
     * @param  array  $options 可选参数
     * @return array|BadResponseException
     */
    protected function request($uri, array $params, array $options = [])
    {
        if ($options) {
            $params = array_merge($params, $options);
        }
        $data = $this->encodeBody($params);
        $response = $this->httpClient->request('post', $uri, [
            'json' => $data,
        ]);
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
    protected function encodeBody(array $data)
    {

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = base64_encode($value);
            }
        }

        return $data;
    }

    /**
     * string类型key用base64解码, 需指定解码字段
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
}