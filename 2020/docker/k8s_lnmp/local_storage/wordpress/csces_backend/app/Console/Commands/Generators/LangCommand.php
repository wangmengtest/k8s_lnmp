<?php

namespace App\Console\Commands\Generators;

use App\Constants\ResponseCode;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

/**
 * 根据响应码常量类生成中文语言包
 * 提取常量的 code 码和注释
 * Class LangCommand
 * @package App\Console\Commands\Generators
 */
class LangCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:lang
        {component? : 组件名，不输入这使用全局的 ResponseCode}
        {--f=json : 输出格式, php, markdown, json }
        {--r : 反向操作，从 code.json 同步到常量文件 }
        {--t : 是否是翻译}
        {--c : 检查错误码是否有冲突}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据响应码常量类生成中文语言包';

    /**
     * 响应码常量类
     * @var string
     */
    protected $responseCodeClass = ResponseCode::class;

    /**
     * 翻译使用的 API
     * @var string
     */
    protected $translateApi = 'http://fanyi.youdao.com/translate?&doctype=json&type=ZH_CN2EN&i=';

    /**
     * 变量占位符, 英文翻译时使用
     * @var string
     */
    protected $placeholder = "{{";

    /**
     * 常量定义模板
     * @var string
     */
    const CONST_TPL = <<<EOF
    /** @msg */
    const @const = '@key';
EOF;

    /**
     * 常量定义模板
     * @var string
     */
    const CONST_REG_TPL = '/(\/\*\*)(.*)?(\*\/[\s]*?const @key)/';

    /**
     * 中文语言包路径
     * @var string
     */
    protected $langPath;

    public function __construct()
    {
        $this->langPath = resource_path('lang/zh/code.php');
        return parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!is_dir(component_path('language-pack'))) {
            $this->error('请安装语言包组件: language-pack');
            return 0;
        }

        try {
            $component = $this->argument('component');
            $fmt       = $this->option('f');
            $translate = $this->option('t');
            $check     = $this->option('c');
            $reverse   = $this->option('r');

            $this->initResponseCodeClass($component);

            // 语言包翻译
            $translate ? $this->translate($fmt) :
                // 反向操作， 通过 code.json 生成常量文件
                ($reverse ? $this->generatorLangConst() :
                    // 通过常量文件生成 code 文件
                    $this->generatorLang($check, $fmt));

            $this->info('success');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        return 0;
    }

    /**
     * @param string|null $component
     *
     * @throws Exception
     * @author yaming.feng@vhall.com
     * @since  2021/7/12
     */
    public function initResponseCodeClass($component)
    {
        if (!$component) {
            return;
        }

        $path = component_path(Str::snake($component, '-'));
        if (!is_dir($path)) {
            throw new Exception('组件不存在: ' . $component);
        }

        $responseClass = $path . '/src/constants/ResponseCode.php';
        if (!is_file($responseClass)) {
            throw new Exception('组件不存在响应码常量文件: ' . str_replace(base_path(), '', $responseClass));
        }

        $componentName           = Str::camel($component);
        $this->responseCodeClass = "\\vhallComponent\\$componentName\\constants\\ResponseCode";
    }

    /**
     * 根据中文语言包翻译出英文语言包
     * @auther yaming.feng@vhall.com
     * @date 2021/5/31
     * @throws Exception
     */
    protected function translate($outFmt)
    {
        $zhLang = require_once($this->langPath);
        if (empty($zhLang) || !is_array($zhLang)) {
            return;
        }

        // 替换下占位符，翻译时 : 会出问题
        foreach ($zhLang as $code => $val) {
            $zhLang[$code] = preg_replace('/(.*)(:)([\w_]+)(.*)/', '$1' . $this->placeholder . '$3$4', $val);
        }

        $enLang = [];
        foreach ($zhLang as $code => $text) {
            $text          = $this->callTranslateApi($text);
            $enLang[$code] = str_replace($this->placeholder, ':', $text);
        }

        $path = component_path('language-pack/en/code.json');
        $this->output($path, $outFmt, $enLang);
    }

    /**
     * 调用翻译 API
     * @auther yaming.feng@vhall.com
     * @date 2021/5/31
     *
     * @param $text
     *
     * @return false|mixed
     */
    protected function callTranslateApi($text)
    {
        $api     = $this->translateApi . $text;
        $content = Http::get($api)->json();
        if (!isset($content['errorCode']) || $content['errorCode'] != 0) {
            $this->error('translate error:' . var_export($content, true));
            return false;
        }

        return $content['translateResult'][0][0]['tgt'];
    }

    /**
     *  生成中文语言包
     * @auther yaming.feng@vhall.com
     * @date 2021/5/31
     * @throws ReflectionException
     * @throws Exception
     */
    protected function generatorLang($check, $outFmt)
    {
        $langMap = $this->getLangMap();

        if ($check) {
            return;
        }

        // 从 code.json 中获取
        $jsonMap = require_once $this->langPath;

        $diffMap = array_diff_assoc($jsonMap, $langMap);
        if ($diffMap) {
            $this->info('存在冲突错误码:' . PHP_EOL . var_export($diffMap, true));
            $ok = $this->confirm("code.json 和常量配置有冲突，是否覆盖？", false);
            if (!$ok) {
                $this->info('可以使用: php artisan generator:lang --r 将 code.json 中修改同步到常量文件中');
                return;
            }
        }

        $path = component_path('language-pack/zh/code.json');
        $this->output($path, $outFmt, $langMap);
    }

    /**
     * 生成常量文件
     * @author fym
     * @since  2021/7/22
     */
    protected function generatorLangConst()
    {
        // 从常量中获取
        $langMap = $this->getLangMap();

        // 从 code.json 中获取
        $jsonMap = require_once $this->langPath;

        $langMap = array_diff_assoc($jsonMap, $langMap);

        $filePath = (new ReflectionClass($this->responseCodeClass))->getFileName();
        $content  = file_get_contents($filePath);

        $addCount    = 0;
        $updateCount = 0;
        foreach ($langMap as $key => $val) {
            $this->editConst($content, $key, $val, $addCount, $updateCount);
        }

        file_put_contents($filePath, $content);

        $this->info("新增: $addCount, 修改: $updateCount");
        $this->info('提交前，请手动格式化常量文件');
    }

    /**
     * 从常量文件中读取所有 code map
     * @return array
     * @throws ReflectionException
     * @author fym
     * @since  2021/7/22
     */
    protected function getLangMap()
    {
        $class     = new ReflectionClass($this->responseCodeClass);
        $constants = $class->getConstants();

        $langMap = [];
        foreach ($constants as $const => $code) {
            $constant = new ReflectionClassConstant($this->responseCodeClass, $const);
            $docText  = $this->getDocText($constant->getDocComment());

            if (isset($langMap[$code])) {
                throw new Exception("错误码重复: $code");
            }

            $langMap[$code] = $docText;
        }
        return $langMap;
    }

    /**
     * 新增或修改常量
     *
     * @param string $content
     * @param string $key
     * @param string $val
     * @param int    $addCount
     * @param int    $updateCount
     *
     * @since  2021/7/22
     * @author fym
     */
    protected function editConst(string &$content, string $key, string $val, int &$addCount, int &$updateCount)
    {
        // 前缀匹配
        $prefix = $key;
        while (true) {
            if (strrpos($content, $prefix) !== false || strpos($prefix, '.') == false) {
                break;
            }
            $prefix = substr($prefix, 0, strrpos($prefix, '.'));
        }

        // 修改了 key 的描述
        if ($prefix == $key) {
            $reg     = str_replace('@key', strtoupper(
                str_replace('.', '_', $key)
            ), self::CONST_REG_TPL);
            $content = preg_replace($reg, "$1 $val $3", $content);
            $updateCount++;
            return;
        }

        // 新增的 key
        // 获取要插入的位置
        $index = strrpos($content, $prefix . '.');
        if ($index === false) {
            $index = strrpos($content, '}');
        } else {
            $index += strpos(substr($content, $index), ';') + 1;
        }

        $const = str_replace(['@msg', '@key', '@const'], [
            $val,
            $key,
            strtoupper(str_replace('.', '_', $key))
        ], self::CONST_TPL);

        $content = substr($content, 0, $index) . PHP_EOL . PHP_EOL . $const . PHP_EOL . substr($content, $index);
        $addCount++;
    }

    /**
     * 获得注释内容
     * @auther yaming.feng@vhall.com
     * @date 2021/5/31
     *
     * @param string $doc
     *
     * @return string
     */
    protected function getDocText(string $doc): string
    {
        $text = str_replace(['/*', '*/', '*', '@var', 'int'], '', $doc);
        if (($i = strripos($text, 'TODO')) !== false) {
            $text = substr($text, 0, $i);
        }
        return trim($text);
    }

    /**
     * 输出文件
     * @auther yaming.feng@vhall.com
     * @date 2021/6/7
     *
     * @param string $path
     * @param string $outFmt
     * @param array  $langMap
     *
     * @throws Exception
     */
    protected function output(string $path, string $outFmt, array $langMap)
    {
        $method = "put{$outFmt}LangContent";

        if (!method_exists($this, $method)) {
            throw new Exception("输出格式不支持: " . $outFmt);
        }
        call_user_func([$this, $method], $path, $langMap);
    }

    /**
     * 构造语言包内容
     * @auther yaming.feng@vhall.com
     * @date 2021/5/31
     *
     * @param string $path
     * @param array  $langMap
     *
     */
    protected function putPhpLangContent(string $path, array $langMap)
    {
        $langContent = var_export($langMap, true);
        $content     = '<?php' . PHP_EOL
            . 'return'
            . str_replace(['array', '(', ')', '  '], ['', '[', ']', '    '], $langContent)
            . ';' . PHP_EOL;

        file_put_contents($path, $content);
    }

    /**
     * 输出 markdown 格式
     * @auther yaming.feng@vhall.com
     * @date 2021/6/7
     *
     * @param string $path 输出路径
     * @param array  $langMap
     *
     */
    protected function putMarkdownLangContent(string $path, array $langMap)
    {
        $path = str_replace('.php', '.md', $path);

        $content = "| 错误码 | 描述 |" . PHP_EOL;
        $content .= "| :----- | ---- |" . PHP_EOL;

        $rowTpl = "|%s|%s|";

        foreach ($langMap as $code => $msg) {
            $content .= sprintf($rowTpl, rtrim($code, '?'), $msg) . PHP_EOL;
        }

        file_put_contents($path, $content);
    }

    /**
     * 输出 json 格式
     * @auther yaming.feng@vhall.com
     * @date 2021/6/11
     *
     * @param string $path
     * @param array  $langMap
     */
    protected function putJsonLangContent(string $path, array $langMap)
    {
        $path = str_replace('.php', '.json', $path);

        $json = json_encode($langMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        file_put_contents($path, $json);
    }
}
