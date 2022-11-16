<?php

namespace App\Component\common\src\services;

use App\Component\common\src\services\exports\ExportCsv;
use App\Component\common\src\services\exports\ExportExcel;
use App\Component\common\src\services\exports\ExportInterface;

/*

配置格式， 不配置使用默认值
export:
  default_driver: my
  csv:
    use_cache: false
    max_cache_row: 100
  excel:
    font: '宋体'
    ext: '.xlsx'
    type: Excel2007
  my: # 自定义导出配置， 下面的配置会传给 class 的构造函数
    class: App\Component\common\src\services\exports\ExportCsv # 自定义导出类
    use_cache: true
    max_cache_row: 100


 */

/**
 *  导出文件代理类
 * Class DownloadService
 * @package App\Component\common\src\services
 *
 * @method ExportInterface putRow(array $row)
 * @method ExportInterface putRows(array $rows)
 * @method void download(callable $callable = null)
 * @method void close()
 */
class ExportProxyService
{
    /**
     * @var ExportInterface
     */
    protected $driver;

    protected $drivers = [
        'csv'   => ExportCsv::class,
        'excel' => ExportExcel::class
    ];

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/5/14
     *
     * @param string|null $driverType
     *
     * @return ExportProxyService
     * @throws \Exception
     */
    public function initDriver(string $driverType = null)
    {
        // 获取配置
        $driverType   = $driverType ?: vss_config('export.default_driver', 'csv');
        $driverConfig = vss_config('export.' . $driverType, []);

        $driverClass = $this->drivers[$driverType] ?? null;
        if ($driverClass) {
            $this->driver = new $driverClass($driverConfig);
            return $this;
        }

        if (!$driverConfig || !isset($driverConfig['class'])) {
            throw new \Exception('导出配置错误，驱动类型不存在: ' . $driverType);
        }

        $driverClass = $driverConfig['class'];

        if (!class_exists($driverClass)) {
            throw new \Exception('导出配置错误，驱动类不存在:' . $driverClass);
        }

        $this->driver = new $driverClass($driverConfig);

        if (!($this->driver instanceof ExportInterface)) {
            throw new \Exception('导出配置错误,无效的驱动类:' . $driverClass);
        }

        return $this;
    }

    /**
     * 设置导出初始化信息
     * @auther yaming.feng@vhall.com
     * @date 2021/5/18
     *
     * @param string        $filePath 导出文件路径， 不用加文件扩展名， 文件扩展名会根据使用的导出方式，自动添加
     * @param callable|null $callback 回到函数，可以根据使用的导出驱动，做自定义配置
     *
     * @return $this
     * @throws \Exception
     */
    public function init(string $filePath, callable $callback = null)
    {
        if (!$this->driver) {
            $this->initDriver();
        }

        // 删除文件扩展名，根据不同的驱动回自动增加
        if (($index = strrpos($filePath, '.')) !== false) {
            $filePath = substr($filePath, 0, $index);
        }

        $this->driver->init($filePath, $callback);
        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        if (!$this->driver) {
            throw new \Exception('请先调用 init 函数');
        }

        if (method_exists($this->driver, $name)) {
            call_user_func_array([$this->driver, $name], $arguments);
        }
        return $this;
    }
}
