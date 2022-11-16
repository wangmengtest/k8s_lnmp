<?php

namespace App\Component\common\src\services\exports;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * 导出 Excel
 * Class ExportExcel
 * @package App\Component\common\src\services\exports
 */
class ExportExcel implements ExportInterface
{
    /**
     * @var Spreadsheet $fp
     */
    protected $fp;

    protected $isClose = false;

    protected $filePath;

    protected $rowIndex = 0;

    protected $font;  // 字体

    protected $type; // 导出文件的类型     Xlsx   CSV

    protected $ext;  // 导出文件的扩展名   .xlsx       .csv

    public function __construct(array $config = [])
    {
        $this->font = $config['font'] ?? '宋体';
        $this->type = $config['type'] ?? 'Xlsx';
        $this->ext  = $config['ext'] ?? '.xlsx';
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/5/14
     *
     * @param string        $filePath 文件名，或文件路径
     * @param callable|null $callback
     *
     * @return ExportInterface
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function init(string $filePath, callable $callback = null): ExportInterface
    {
        $this->fp = new Spreadsheet();
        File::setUseUploadTempDirectory(true);

        $fileName = basename($filePath);

        //设置Excel基本属性
        $this->fp->getProperties()->setTitle($fileName);
        $this->fp->getProperties()->setSubject($fileName);
        $this->fp->getDefaultStyle()->getFont()->setName($this->font);
        $this->fp->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        if (is_callable($callback)) {
            call_user_func($callback, $this->fp);
        }

        $this->filePath = $filePath . $this->ext;

        return $this;
    }

    public function setHeader($header): ExportInterface
    {
        //设置Excel表头
        foreach (array_slice(range('A', 'Z'), 0, count($header)) as $key => $value) {
            //表头数据
            $this->fp->getActiveSheet()->setCellValue($value . '1', $header[$key]);
            //表头字体粗体
            $this->fp->getActiveSheet()->getStyle($value . '1')->getFont()->setBold(true);
        }

        $this->rowIndex++;

        return $this;
    }

    public function putRow(array $row): ExportInterface
    {
        $row = array_values($row);
        if ($this->rowIndex == 0) {
            return $this->setHeader($row);
        }

        $this->rowIndex++;

        foreach (array_slice(range('A', 'Z'), 0, count($row)) as $key => $value) {
            $this->fp->getActiveSheet()->setCellValue($value . $this->rowIndex, $row[$key]);
        }

        return $this;
    }

    public function putRows($rows): ExportInterface
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

        $objWriter = IOFactory::createWriter($this->fp, $this->type);
        $objWriter->save($this->filePath);

        $this->isClose = true;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/6/8
     *
     * @param callable|null $callable
     *
     * @return mixed|void
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function download(callable $callable = null)
    {
        $this->isClose = true;

        $fileName = basename($this->filePath);

        //下载文件
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        if ($callable) {
            call_user_func($callable, $this->fp);
        }

        $objWriter = IOFactory::createWriter($this->fp, 'Xlsx');
        $objWriter->save('php://output');
    }
}

