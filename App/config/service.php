<?php

/**
 * Shared configuration Service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * Свой Компонент Response
 */
$di->setShared('response', new \Topnlab\PhalconBase\v2\Components\ApiResponse());

/**
 * Свой Компонент Request
 */
$di->setShared('request', new \Topnlab\PhalconBase\v2\Components\ApiRequest());

/**
 * Класс - реализатор функционала кропа
 */
$di->setShared('imageCrop', function () {
    return new \App\Service\Classes\ImageCrop();
});

/**
 * Класс - реализатор функционала ресайза
 */
$di->setShared('imageResize', function () {
    return new \App\Service\Classes\ImageResize();
});

/**
 * Класс - реализатор функционала нанесения водяного знака
 */
$di->setShared('imageWatermarking', function () {
    return new \App\Service\Classes\ImageWatermarking();
});

