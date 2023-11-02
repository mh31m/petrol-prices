<?php

if (!function_exists('logging')) {
    function logging($message, $level = 'info')
    {
        \Illuminate\Support\Facades\Log::$level($message);
    }
}