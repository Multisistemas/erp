<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit98ce2df3b856a60bb76652f23db588f4
{
    public static $fallbackDirsPsr0 = array (
        0 => __DIR__ . '/..' . '/opauth/google',
    );

    public static $classMap = array (
        'Opauth' => __DIR__ . '/..' . '/opauth/opauth/lib/Opauth/Opauth.php',
        'OpauthStrategy' => __DIR__ . '/..' . '/opauth/opauth/lib/Opauth/OpauthStrategy.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->fallbackDirsPsr0 = ComposerStaticInit98ce2df3b856a60bb76652f23db588f4::$fallbackDirsPsr0;
            $loader->classMap = ComposerStaticInit98ce2df3b856a60bb76652f23db588f4::$classMap;

        }, null, ClassLoader::class);
    }
}
