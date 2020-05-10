<?php

namespace Fmujie\BaiduFace;

use Illuminate\Support\ServiceProvider;

class BaiduFaceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
     public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/config/laravel-baidu-face.php' => config_path('laravel-baidu-face.php'), // 发布配置文件到 laravel 的config 下
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function register()
    {
         //
    }
}
