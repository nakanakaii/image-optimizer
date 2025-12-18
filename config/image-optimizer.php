<?php

// config for Joshembling/ImageOptimizer
return [
    /**
     * Intervention Image v3 driver: "gd" or "imagick".
     */
    /*
    |--------------------------------------------------------------------------
    | Intervention Driver
    |--------------------------------------------------------------------------
    |
    | Which Intervention Image driver to use.
    |
    | Supported: "gd", "imagick"
    |
    */
    'driver' => env('IMAGE_OPTIMIZER_DRIVER', 'gd'),

    /**
     * Quality settings (0-100) for lossy encoders.
     */
    /*
    |--------------------------------------------------------------------------
    | Output Quality
    |--------------------------------------------------------------------------
    |
    | Quality settings (0-100) used when encoding lossy formats.
    |
    */
    'quality' => [
        'jpeg' => (int) env('IMAGE_OPTIMIZER_QUALITY_JPEG', 70),
        'webp' => (int) env('IMAGE_OPTIMIZER_QUALITY_WEBP', 75),
        'avif' => (int) env('IMAGE_OPTIMIZER_QUALITY_AVIF', 50),
    ],
];
