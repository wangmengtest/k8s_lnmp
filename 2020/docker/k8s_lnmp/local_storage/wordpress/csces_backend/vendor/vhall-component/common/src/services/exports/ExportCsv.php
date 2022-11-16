<?php

namespace vhallComponent\common\services\exports;

/**
 * 通过先写入内存，在同步到文件中，来提升写入速度
 * 经测试这种方式能比直接写文件提升 100+ 倍的速度
 *
 * Class ExportCsv
 * @package vhallComponent\common\services\exports
 */
class ExportCsv implements ExportInterface
{
    protected $fp;

    protected $memoryFp;

    protected $maxCacheRow; // 默认写入缓存的函数, 到达指定的行数后再同步到文件中

    protected $useCache = true; // 是否使用缓存方式

    protected $cacheRow = 0; // 已经写入内存的行数

    protected $isClose = false;

    protected $ext = '.csv';

    protected $filePath;

    public function __construct(array $config = [])
    {
        // 默认缓存 1000 行, 可以在配置中配置，通过数据的列数和数据的大小估算要写入的行数，要注意内存的使用
        $this->maxCacheRow = $config['max_cache_row'] ?? 1000;
        $this->useCache    = $config['use_cache'] ?? true;
    }

    public function init(string $filePath, callable $callback = null): ExportInterface
    {
        $this->filePath = $filePath . $this->ext;
        $this->fp       = fopen($this->filePath, 'w');
        fwrite($this->fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $this->useCache && $this->memoryFp = fopen('php://memory', 'w');

        if ($callback && is_callable($callback)) {
            $callback($this->fp);
        }
        return $this;
    }

    public function putRow(array $row): ExportInterface
    {
        fputcsv($this->useCache ? $this->memoryFp : $this->fp, $row);
        $this->cacheRow++;

        if ($this->useCache && $this->cacheRow % $this->maxCacheRow == 0) {
            // 同步数据到文件中
            $this->syncToFile();
        }

        return $this;
    }

    public function putRows(array $rows): ExportInterface
    {
        foreach ($rows as $row) {
            $this->putRow($row);
        }
        return $this;
    }

    public function close()
    {
        if ($this->isClose) {
            return;
        }

        $this->syncToFile();
        $this->isClose = true;

        $this->fp && fclose($this->fp);
        $this->memoryFp && fclose($this->memoryFp);
    }

    public function __destruct()
    {
        if (!$this->isClose) {
            $this->close();
        }
    }

    /**
     * 下载
     * @auther yaming.feng@vhall.com
     * @date 2021/6/8
     *
     * @param callable|null $callback
     */
    public function download(callable $callback = null)
    {
        $fileName = basename($this->filePath);

        Header("Content-type: application/octet-stream;charset=utf-8");
        Header("Content-Disposition:attachment;filename=" . $fileName);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        Header('Expires:0');
        Header('Pragma:public');

        if ($callback) {
            call_user_func($callback, $this->memoryFp);
        }

        $output = fopen('php://output', 'wb+');
        if ($this->useCache && $this->cacheRow < $this->maxCacheRow) {
            fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            $this->cacheToFp($output);
        } else {
            $this->close();
            fwrite($output, file_get_contents($this->filePath));
        }
        @unlink($this->filePath);
    }

    /**
     * 谨慎使用一下非接口函数，应尽量通过配置设置，
     * 使用一下方法，会导致不能自由切换导出格式
     */

    protected function cacheToFp($fp)
    {
        // 移动指针到文件头
        rewind($this->memoryFp);

        // 将内存的数据复制到文件中
        stream_copy_to_stream($this->memoryFp, $fp);

        // 清空内存中的数据
        ftruncate($this->memoryFp, 0);
    }

    /**
     * 将内存中的数据同步到文件中
     * @auther yaming.feng@vhall.com
     * @date 2021/5/17
     */
    protected function syncToFile()
    {
        if (!$this->useCache || !$this->fp || !$this->memoryFp) {
            return $this;
        }

        $this->cacheToFp($this->fp);

        return $this;
    }

    /**
     * 手动设置最大缓存行数
     * @auther yaming.feng@vhall.com
     * @date 2021/5/17
     *
     * @param $rowCount
     *
     * @return $this
     */
    public function setMaxCacheRow(bool $rowCount)
    {
        $this->maxCacheRow = $rowCount;
        return $this;
    }

    /**
     * 是否使用缓存
     * @auther yaming.feng@vhall.com
     * @date 2021/5/17
     *
     * @param bool $useCache
     *
     * @return $this
     */
    public function setUseCache(bool $useCache)
    {
        $this->useCache = $useCache;
        return $this;
    }
}
