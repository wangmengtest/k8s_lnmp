<?php

namespace VhallDisk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Support\Facades\Log;

class Request
{
    /**
     * @var
     */
    protected $file;

    /** headers
     * @var
     */
    protected $headers = [];

    /**request method
     * @var
     */
    protected $method;

    /**request url
     * @var mixed
     */
    protected $url;

    protected $timestamp;
    protected $config;

    //2018-04-02
    protected $content = '';

    public function __construct($config = null)
    {
        $this->config = $config ? : config('filesystems.disks.vhall');
        $this->url = $config['domain'];
        $this->timestamp = time();
    }

    public function request($method, $url)
    {
        $this->method = strtoupper($method);
        $this->url = $this->url . '/' . $url;
        return $this;
    }


    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws UploadException
     */
    public function send()
    {
        $client = new Client();

        $url = $this->url;

        $body = null;

        //2018-04-02
        if ($this->file && $this->method === 'PUT') {
            $body = $this->file;
        }else if($this->content){
            $body = $this->content;
        }

        $request = new Psr7\Request(
            $this->method,
            $url,
            $this->headers,
            $body
        );
        //分发处统一处理验证部分
        $authHeader = $this->headerSign(
            $this->method,
            $request->getUri()->getPath()
        );

        foreach ($authHeader as $head => $value) {
            $request = $request->withHeader($head, $value);
        }
        try {
            $response = $client->send($request);
        } catch (RequestException $e) {
            throw new UploadException($e->getMessage());
        }
        $responseBody = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == 200 && isset($responseBody['err_code'])) {
            //Log::info('上传接口请求错误！', $responseBody['err_code']);
            Log::info('上传接口请求错误！', $responseBody);
            throw new UploadException('上传接口请求错误！', $responseBody['err_code']);
        }
        return $responseBody;
    }

    // 处理额外的请求头信息

    /**
     * @param $method
     * @param $getPath
     * @return array
     */
    private function headerSign($method, $getPath)
    {
        $data = [$this->timestamp, $this->config['password']];
        $sign = md5(implode('&', $data));

        return [
            'Authorization' => $this->config['username'] . ':' . $sign,
            'Date' => $this->timestamp
        ];
    }

    public function withHeaders($headers)
    {
        if (is_array($headers)) {
            foreach ($headers as $header => $value) {
                $this->withHeader($header, $value);
            }
        }
        return $this;
    }

    public function withHeader($header, $value)
    {
        $header = strtolower(trim($header));
        $this->headers[$header] = $value;
        return $this;
    }

    public function withFile($stream)
    {
        $stream = Psr7\Utils::streamFor($stream);
        $this->file = $stream;
        return $this;
    }

    public function withContent($content)
    {
        $this->content = $content;
        return $this;
    }
}
