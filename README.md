> [!IMPORTANT]
> This version targets **Filament v4** and **intervention/image v3**.  
> For Filament v3 + intervention/image v2, use an older release of this package.

# Optimize your Filament images before they reach your database.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/joshembling/image-optimizer.svg?style=flat-square)](https://packagist.org/packages/joshembling/image-optimizer)
[![Total Downloads](https://img.shields.io/packagist/dt/joshembling/image-optimizer.svg?style=flat-square)](https://packagist.org/packages/joshembling/image-optimizer)

When you currently upload an image using the native Filament component `FileUpload`, the original file is saved without any compression or conversion.

Additionally, if you upload an image and use conversions with `SpatieMediaLibraryFileUpload`, the original file is saved with its corresponding versions provided on your model. 

What if you'd rather convert and reduce the image(s) before reaching your database/S3 bucket? Especially in the case where you know you'll never need to save the original image sizes the user has uploaded.

🤳 **This is where Filament Image Optimizer comes in**. 

You use the same components as you have been doing and have access to two additional methods for maximum optimization, saving you a lot of disk space in the process. 🎉

## Contents

- [Contents](#contents)
- [Installation](#installation)
- [Usage](#usage)
	- [Optimizing Images](#optimizing-images)
	- [Resizing Images](#resizing-images)
	- [Combining Methods](#combining-methods)
	- [Multiple Images](#multiple-images)
	- [Examples](#examples)
	- [Debugging](#debugging)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Credits](#credits)
- [Licence](#license)

## Installation

You can install the package via composer:

```bash
composer require joshembling/image-optimizer
```

## Usage

### Filament version

You must be using **Filament v4.x**.

### Server

[GD Library](https://www.php.net/manual/en/image.installation.php) (or Imagick) must be installed on your server.

### Optimizing images

Use the package component:

`````php
use Joshembling\ImageOptimizer\Components\FileUpload;

FileUpload::make('attachment')
    ->image()
    ->optimize('webp');
`````

### Resizing images

`````php
use Joshembling\ImageOptimizer\Components\FileUpload;

FileUpload::make('attachment')
    ->image()
    ->resize(50);
`````

### Add maximum width and/or height

`````php
use Joshembling\ImageOptimizer\Components\FileUpload;

FileUpload::make('attachment')
    ->image()
    ->maxImageWidth(1024)
    ->maxImageHeight(768);
`````

### Combining methods

`````php
use Joshembling\ImageOptimizer\Components\FileUpload;

FileUpload::make('attachment')
    ->image()
    ->optimize('webp')
    ->maxImageWidth(1024)
    ->maxImageHeight(768)
    ->resize(50);
``````
