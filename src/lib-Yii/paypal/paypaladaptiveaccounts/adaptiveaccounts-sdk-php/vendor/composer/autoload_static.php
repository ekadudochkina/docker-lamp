<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit157d83ad26fd9fd6d08b04b71cc32046
{
    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PayPal\\Types' => 
            array (
                0 => __DIR__ . '/../..' . '/lib',
            ),
            'PayPal\\Service' => 
            array (
                0 => __DIR__ . '/../..' . '/lib',
            ),
            'PayPal' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/sdk-core-php/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit157d83ad26fd9fd6d08b04b71cc32046::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
