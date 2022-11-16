<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 忽略 notice 异常
        if(vss_config('debug')){
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING );
        }else{
            error_reporting(E_ERROR);
        }

        $this->validatorExtend();
    }

    protected function validatorExtend()
    {
        Validator::extend('mobile', function ($attribute, $value, $parameters, $validator) {
            $pattern = '/^1[3456789]{1}\d{9}$/';
            $res     = preg_match($pattern, $value);
            return $res > 0;
        });

        Validator::extend('email', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[A-Za-z\d]+([-_.][A-Za-z\d]+)*@([A-Za-z\d]+[-.])+[A-Za-z\d]{2,4}$/', $value);
        });
    }
}
