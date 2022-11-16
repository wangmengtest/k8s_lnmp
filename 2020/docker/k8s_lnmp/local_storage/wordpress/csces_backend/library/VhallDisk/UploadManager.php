<?php

namespace VhallDisk;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class UploadManager
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $path 上传的指定路径
     * @param $content 上传的内容
     * @param array $params header参数
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function upload($path, $content, $params = [])
    {
        //\Log::info('测试上传');
        if (!$content) {
            throw new \Exception('content can not be empty！');
        }
        $params = Arr::add($params, 'path', $path);

        //2018-04-02
        if(isset($params['appendFile']) && $params['appendFile']){
            return $this->request->request('PUT', 'files/upload')
                ->withHeaders($params)
                ->withContent($content)
                ->send();
        }else{
            $stream = Psr7\Utils::streamFor($content);
            return $this->request->request('PUT', 'files/upload')
                ->withHeaders($params)
                ->withFile($stream)
                ->send();
        }
    }

    /**
     * @param $path
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function read($path)
    {
        $url = 'files/read';
        \Log::info('读取文件！！！');
        $path = ltrim($path, '\\/');
        $response = $this->request->request('GET', $url)
            ->withHeader('path', $path)
            ->send();
        return $response;
    }

    /**
     * @param $path
     * @return bool
     * @throws UploadException
     */
    public function has($path)
    {
        $url = 'files/exists';
        $path = ltrim($path, '\\/');
        try {
            $result = $this->request->request('GET', $url)
                ->withHeader('path', $path)
                ->send();
        } catch (UploadException $e) {
            return false;
        }
        return true;
    }



    /**
     * @param $path
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getMetadata($path, $argvs = array())
    {
        Log::info('$argvs: '.var_export($argvs,TRUE));die;

        $url = 'files/metadata?_type=' . ($argvs['_type'] ?? NULL);

        $path = ltrim($path, '\\/');
        $response = $this->request->request('GET', $url)
            ->withHeader('path', $path)
            ->send();
        return $response['data'];
    }



    /**
     * @param $path
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function info($argvs = array())
    {
        $url = 'files/info?_type=' . ($argvs['_type'] ?? NULL);
        $path = ltrim($argvs['name'], '\\/');
        $response = $this->request->request('GET', $url)
            ->withHeader('path', $path)
            ->send();
        return $response['data'];
    }


    public function uploadedFilesize($argvs = array())
    {

        $url = 'files/uploadedFilesize?_type=' . ($argvs['_type'] ?? NULL);

        $path = ltrim($argvs['path'], '\\/');
        $response = $this->request->request('GET', $url)
            ->withHeader('path', $path)
            ->send();

        return $response['data'];
    }



    /**
     * @param $path
     * @return bool
     */
    public function createDir($path)
    {
        $url = 'files/dir';
        $path = ltrim($path, '\\/');
        $response = $this->request->request('POST', $url)
            ->withHeader('path', $path)
            ->send();
        return $response['success'];
    }

    /**
     * @param $path
     * @return bool
     */
    public function deleteDir($path)
    {
        return $this->delete($path);
    }

    /**
     * @param $path
     * @return bool
     */
    public function delete($path)
    {
        $url = 'files/delete';
        $path = ltrim($path, '\\/');
        $response = $this->request->request('POST', $url)
            ->withHeader('path', $path)
            ->send();
        return $response['success'];
    }
}
