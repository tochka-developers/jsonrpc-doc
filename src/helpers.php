<?php

if (!function_exists('is_lumen')) {
    /**
     * Check lumen framework
     *
     * @return boolean
     */
    function is_lumen()
    {
        return (bool)preg_match('/Lumen/iu', app()->version());
    }
}

if (!function_exists('getVersion')) {
    /**
     * Check framework version
     *
     * @return boolean
     */
    function getVersion()
    {
        return preg_replace('/.*(([0-9]\.[0-9])[0-9]*).*/ui', '$2', app()->version());
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return base_path('public').($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}


if (! function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}