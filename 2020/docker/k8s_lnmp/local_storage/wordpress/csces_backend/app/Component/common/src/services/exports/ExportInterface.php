<?php

namespace App\Component\common\src\services\exports;

/**
 * 数据导出接口
 * Interface ExportInterface
 * @package App\Component\common\src\services\exports
 */
interface ExportInterface
{
    /**
     * 构造函数，接收配置信息
     * ExportInterface constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = []);

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/5/14
     *
     * @param string        $filePath
     * @param callable|null $callback
     *
     * @return $this
     */
    public function init(string $filePath, callable $callback = null): self;

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/2/1
     *
     * @param array $row 一行数据
     *
     * @return $this
     */
    public function putRow(array $row): self;

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/2/1
     *
     * @param array $rows 多行数据
     *
     * @return $this
     */
    public function putRows(array $rows): self;

    /**
     * 关闭文件
     * @auther yaming.feng@vhall.com
     * @date 2021/5/14
     * @return mixed
     */
    public function close();

    /**
     * 自动关闭文件
     */
    public function __destruct();

    /**
     * 下载
     * @auther yaming.feng@vhall.com
     * @date 2021/6/8
     *
     * @param callable|null $callback
     *
     */
    public function download(callable $callback = null);
}
