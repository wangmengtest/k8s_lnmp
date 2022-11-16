<?php

$finder = \PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__);

$rules = [
    '@PSR2'                               => true,

    // .连接符两边空格
    'concat_space'                        => [
        'spacing' => 'one',
    ],

    // 将双引号转换为简单字符串的单引号
    'single_quote'                        => [
        'strings_containing_single_quote_chars' => true
    ],

    // 类的属性方法必须用一个空行分隔
    'class_attributes_separation'         => [
        'elements' => ['const', 'method', 'property'],
    ],

    //PHP 常量 true、false 和 null 必须使用小写
    'lowercase_constants'                 => true,

    //Class static references self, static and parent MUST be in lower case.
    'lowercase_static_reference'          => true,

    //PHP 关键字必须都是小写
    'lowercase_keywords'                  => true,

    //每个属性和方法都必须指定作用域是 public、protected 还是 private，abstract 和 final 必须位于作用域关键字之前，static 必须位于作用域之后
    'visibility_required'                 => [
        'elements' => ['property', 'method']
    ],

    //使用 new 新建实例时后面都应该带上括号
    'new_with_braces'                     => true,

    //T_OBJECT_OPERATOR (->) 两端不应有空格
    'object_operator_without_whitespace'  => true,

    //phpdoc 标量类型声明时应该使用 int 而不是 integer，bool 而不是 boolean，float 而不是 real 或者 double
    'phpdoc_scalar'                       => true,
    'array_syntax'                        => ['syntax' => 'short'],
    'combine_consecutive_unsets'          => true,   //多个unset，合并成一个
    'no_useless_else'                     => true,  //删除无用的else
    'no_useless_return'                   => true,  //删除函数末尾无用的return
    'no_empty_phpdoc'                     => true,  // 删除空注释
    'no_empty_statement'                  => true,  //删除多余的分号
    'no_leading_namespace_whitespace'     => true,  //删除namespace声明行包含前导空格
    'no_spaces_inside_parenthesis'        => true,  //删除括号后内两端的空格
    'no_trailing_whitespace'              => true,  //删除非空白行末尾的空白
    'no_unused_imports'                   => true,  //删除未使用的use语句
    'no_whitespace_before_comma_in_array' => true,  //删除数组声明中，每个逗号前的空格
    'no_whitespace_in_blank_line'         => true,  //删除空白行末尾的空白
    'ternary_operator_spaces'             => true,  //标准化三元运算的格式
    'ternary_to_null_coalescing'          => true,  //尽可能使用null合并运算符??。需要PHP> = 7.0。
    'whitespace_after_comma_in_array'     => true, // 在数组声明中，每个逗号后必须有一个空格
    'trim_array_spaces'                   => true,  //删除数组首或尾随单行空格
    'array_indentation'                   => true,  //数组的每个元素必须缩进一次
];

return \PhpCsFixer\Config::create()
    ->setRules($rules)
    ->setFinder($finder);
