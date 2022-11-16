<?php
/**
 * Created by PhpStorm.
 * User: zhangxz
 * Date: 2018/7/31
 * Time: 上午10:12
 */

namespace Vss\Utils;

use DateTime;
use Exception;
use Illuminate\Support\Str;

class DateUtil
{
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * 获取当前时间 （年-月-日）
     *
     * @return string
     */
    public static function getCurrentDate(): string
    {
        return (new DateTime())->format(self::DATE_FORMAT);
    }

    /**
     * 获取当前日期 (年-月-日  时:分:秒)
     *
     * @return string
     */
    public static function getCurrentDateTime(): string
    {
        return (new DateTime())->format(self::DATE_TIME_FORMAT);
    }

    /**
     * 获取当前日期 （时:分:秒）
     * @return string
     */
    public static function getCurrentTime(): string
    {
        return (new DateTime())->format(self::TIME_FORMAT);
    }

    /**
     * 获取当前年份
     *
     * @return string
     */
    public static function getYear(): string
    {
        return (new DateTime())->format("Y");
    }

    /**
     * 获取当前月份
     *
     * @return string
     */
    public static function getMonth(): string
    {
        return (new DateTime())->format("m");
    }

    /**
     * 获取当前日期
     *
     * @return string
     */
    public static function getDay()
    {
        return (new DateTime())->format("d");
    }

    /**
     * 时间戳转时间对象
     *
     * @param $time
     * @param $format
     * @return false|string
     */
    public static function timeToDate($time, $format)
    {
        return date($format, $time);
    }

    /**
     * 时间对象转时间戳
     *
     * @param $date
     *
     * @return int
     * @throws Exception
     */
    public static function dateToTime($date): int
    {
        return (new DateTime($date))->getTimestamp();
    }

    public static function secToTime($times): string
    {
        $result = '00:00:00';
        if ($times > 0) {
            $hour   = floor($times / 3600);
            $minute = floor(($times - 3600 * $hour) / 60);
            $second = floor((($times - 3600 * $hour) - 60 * $minute) % 60);
            $result = $hour . ':' . Str::padLeft($minute, 2, 0) . ':' . Str::padLeft($second, 2, 0);
        }
        return $result;
    }

    /**
     * 获取毫秒级别时间戳
     * @return int
     */
    public static function getMsUnixTime(): int
    {
        list($microTime, $time) = explode(' ', microtime());
        return intval((floatval($time) + floatval($microTime)) * 1000);
    }
}
