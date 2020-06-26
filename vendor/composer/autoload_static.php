<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitabc1858ae72986c6370bfc2b430567ac
{
    public static $prefixLengthsPsr4 = array (
        'J' => 
        array (
            'Jobs\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Jobs\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitabc1858ae72986c6370bfc2b430567ac::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitabc1858ae72986c6370bfc2b430567ac::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
