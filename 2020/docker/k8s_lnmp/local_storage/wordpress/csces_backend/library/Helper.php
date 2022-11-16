<?php

class Helper
{
    /**
     * 是否是微信浏览器
     *
     * @date 2019年04月28日15:27:23 2019-04-28 15:07:16
     * @return bool
     */
    public static function isWechat()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }

        return false;
    }

    /**
     * 用户名、邮箱、手机账号中间字符串以*隐藏
     *
     * @date 2019年04月28日15:27:23
     *
     * @param $str
     *
     * @return string
     */
    public static function hideEmail($str)
    {
        if (strpos($str, '@')) {
            $email_array = explode('@', $str);
            $prevfix = (strlen($email_array[0]) < 4) ? '' : substr($str, 0, 3); //邮箱前缀
            $count = 0;
            $str = preg_replace('/([\.\d\w+_-]{0,100})@/', '***@', $str, -1, $count);
            $rs = $prevfix . $str;
        }

        return $rs;
    }

    /**
     * 把秒数转换为时分秒的格式
     *
     * @date 2019年04月28日15:27:23
     *
     * @param int $times 秒
     *
     * @return string
     */
    public static function secToTime($times)
    {
        $result = '00:00:00';
        if ($times > 0) {
            $hour = floor($times / 3600);
            $hour = str_pad((string)$hour, 2, '0', STR_PAD_LEFT);
            $minute = floor(($times - 3600 * $hour) / 60);
            $minute = str_pad((string)$minute, 2, '0', STR_PAD_LEFT);
            $second = floor((($times - 3600 * $hour) - 60 * $minute) % 60);
            $second = str_pad((string)$second, 2, '0', STR_PAD_LEFT);
            $result = $hour . ':' . $minute . ':' . $second;
        }

        return $result;
    }

    /**
     * 字节单位转换,保留的小数位数,默认为2位
     *
     * @date 2019年04月28日15:27:23
     *
     * @param $size
     * @param int $digits
     *
     * @return string
     */
    public static function calc($size, $digits = 2)
    {
        if ($size == 0) {
            return $size . 'KB';
        }
        $unit = ['K', 'M', 'G', 'T', 'P'];
        $base = 1024;
        $i = floor(log((float)$size, $base));
        $n = count($unit);
        if ($i >= $n) {
            $i = $n - 1;
        }

        return round($size / pow($base, $i), $digits) . $unit[$i] . 'B';
    }

    /**
     * 带宽单位转换,保留的小数位数,默认为2位
     *
     * @date 2019年04月28日15:27:23
     *
     * @param $size
     * @param int $digits
     *
     * @return string
     */
    public static function bandwidthCalc($size, $digits = 2)
    {
        if ($size == 0) {
            return $size . 'Kbps';
        }
        $unit = ['K', 'M', 'G', 'T', 'P'];
        $base = 1000;
        $i = floor(log((float)$size, $base));
        $n = count($unit);
        if ($i >= $n) {
            $i = $n - 1;
        }

        return round($size / pow($base, $i), $digits) . $unit[$i] . 'bps';
    }

    /**
     * 检查邮箱格式
     *
     * @date 2019年04月28日15:27:23
     *
     * @param string $email
     *
     * @return bool
     */
    public static function checkEmail(string $email):bool
    {
        if (preg_match('/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([.a-zA-Z0-9_-])+([.a-zA-Z0-9_-]+)+([.a-zA-Z0-9_-])$/', $email)) {
            return true;
        }

        return false;
    }

    /**
     * 检查手机号码格式
     *
     * @date 2019年04月28日15:27:23
     *
     * @param string $phone
     *
     * @return bool
     */
    public static function checkPhone(string $phone):bool
    {
        if (preg_match("/1[3-9]{1}\d{9}$/", $phone)) {
            return true;
        }

        return false;
    }

    /**
     * 是否满足用户名格式
     *
     * @date 2019年04月28日15:27:23
     *
     * @param $username
     * @param int $min
     * @param int $max
     *
     * @return bool
     */
    public static function isUsername($username, $min = 6, $max = 16)
    {
        if (!$username) {
            return false;
        }
        $strlen = strlen($username);
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9]+$/', $username)) {
            return false;
        } elseif ($max < $strlen || $strlen < $min) {
            return false;
        }

        return true;
    }

    /**
     * 是否满足密码格式
     *
     * @date 2019年04月28日15:27:23
     *
     * @param $value
     * @param int $minLen
     * @param int $maxLen
     *
     * @return bool|int
     */
    public static function isPassword($value, $minLen = 8, $maxLen = 16)
    {
        if (!$value) {
            return false;
        }
        $v = trim($value);
        if (empty($v)) {
            return false;
        }
        $match = '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{' . $minLen . ',' . $maxLen . '}$/';

        return preg_match($match, $v);
    }

    /**
     * 是否是移动设备访问
     *
     * @date 2019年04月28日15:27:23
     * @return bool
     */
    public static function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        //此条摘自TPM智能切换模板引擎，适合TPM开发
        if (isset($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT']) {
            return true;
        }

        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) { //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        }
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
            $mobileAgents = [
                '240x320',
                'acer',
                'acoon',
                'acs-',
                'abacho',
                'ahong',
                'airness',
                'alcatel',
                'amoi',
                'android',
                'anywhereyougo.com',
                'applewebkit/525',
                'applewebkit/532',
                'asus',
                'audio',
                'au-mic',
                'avantogo',
                'becker',
                'benq',
                'bilbo',
                'bird',
                'blackberry',
                'blazer',
                'bleu',
                'cdm-',
                'compal',
                'coolpad',
                'danger',
                'dbtel',
                'dopod',
                'elaine',
                'eric',
                'etouch',
                'fly ',
                'fly_',
                'fly-',
                'go.web',
                'goodaccess',
                'gradiente',
                'grundig',
                'haier',
                'hedy',
                'hitachi',
                'htc',
                'huawei',
                'hutchison',
                'inno',
                'ipad',
                'ipaq',
                'ipod',
                'jbrowser',
                'kddi',
                'kgt',
                'kwc',
                'lenovo',
                'lg ',
                'lg2',
                'lg3',
                'lg4',
                'lg5',
                'lg7',
                'lg8',
                'lg9',
                'lg-',
                'lge-',
                'lge9',
                'longcos',
                'maemo',
                'mercator',
                'meridian',
                'micromax',
                'midp',
                'mini',
                'mitsu',
                'mmm',
                'mmp',
                'mobi',
                'mot-',
                'moto',
                'nec-',
                'netfront',
                'newgen',
                'nexian',
                'nf-browser',
                'nintendo',
                'nitro',
                'nokia',
                'nook',
                'novarra',
                'obigo',
                'palm',
                'panasonic',
                'pantech',
                'philips',
                'phone',
                'pg-',
                'playstation',
                'pocket',
                'pt-',
                'qc-',
                'qtek',
                'rover',
                'sagem',
                'sama',
                'samu',
                'sanyo',
                'samsung',
                'sch-',
                'scooter',
                'sec-',
                'sendo',
                'sgh-',
                'sharp',
                'siemens',
                'sie-',
                'softbank',
                'sony',
                'spice',
                'sprint',
                'spv',
                'symbian',
                'talkabout',
                'tcl-',
                'teleca',
                'telit',
                'tianyu',
                'tim-',
                'toshiba',
                'tsm',
                'up.browser',
                'utec',
                'utstar',
                'verykool',
                'virgin',
                'vk-',
                'voda',
                'voxtel',
                'vx',
                'wap',
                'wellco',
                'wig browser',
                'wii',
                'windows ce',
                'wireless',
                'xda',
                'xde',
                'xiaomi',
                'zte',
            ];
            foreach ($mobileAgents as $device) {
                if (stristr($userAgent, $device)) {
                    return true;
                }
            }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
                && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false
                    || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取完整TimeStamp
     * @param $timeString
     * @param string $defaultTail
     * @return false|mixed|string
     */
    public static function fillFullTimeStamp($timeString, $unix=false, $defaultTail=" 00:00:00"){
        $timeString = $timeString ?: date("Y-m-d");

        if (strlen($timeString) == strlen(date('Y-m-d H:i:s'))) {
            return $unix ? strtotime($timeString) : $timeString;
        }

        $fullTimeString = strtotime($timeString . $defaultTail);

        return $unix ? $fullTimeString : date('Y-m-d H:i:s', $fullTimeString);
    }


    public static function dumpSql()
    {
        \Illuminate\Support\Facades\DB::listen(function ($query) {
            $i = 0;
            $bindings = $query->bindings;
            $rawSql = preg_replace_callback('/\?/', function ($matches) use ($bindings, &$i) {
                $item = isset($bindings[$i]) ? $bindings[$i] : $matches[0];
                $i++;
                return gettype($item) == 'string' ? "'$item'" : $item;
            }, $query->sql);

            dump($rawSql);
        });

}

}
