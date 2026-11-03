<?php

namespace App\Helpers;

use Illuminate\Support\Facades\URL;

class SSLHelper
{
    /**
     * Get secure asset URL
     */
    public static function asset($path)
    {
        if (config('app.env') === 'production') {
            return secure_asset($path);
        }
        return asset($path);
    }

    /**
     * Get secure URL
     */
    public static function url($path = null)
    {
        if (config('app.env') === 'production') {
            return URL::secure($path);
        }
        return URL::to($path);
    }

    /**
     * Check if HTTPS is enabled
     */
    public static function isSecure()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
    }
}