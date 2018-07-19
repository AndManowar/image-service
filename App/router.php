<?php

use App\Service\ImageService;
use App\Validators\ImageServiceValidator;
use Topnlab\Common\v2\Reference\Image\DefinitionImagePreviewParams;
use Topnlab\PhalconBase\v2\Components\ApiRequest;
use Topnlab\PhalconBase\v2\Components\ApiResponse;

$app->post('/', function () use ($app) {

    $ser = new \App\FileValidatorService('image', [
        'maxSize' => 155555
    ]);

    if(!$ser->validateFiles()){
        echo '<pre>';
        print_r($ser->getErrors());
        die();
    }
    return 'success';

});

/**
 * Метод обработки изображений
 */
$app->post('/get-image', function () use ($app) {

    /**
     * @var ApiResponse $response
     * @var ApiRequest $request
     */
    $response = $app->response;
    $request = $app->request;

    $file = $request->getUploadedFiles()[0];
    $watermark = $request->getUploadedFiles()[1] ?? null;

    $operations = $request->getPost('operations');

    if (isset($operations[ImageService::OPERATION_RESIZE])) {
        $operations[ImageService::OPERATION_RESIZE] =
            DefinitionImagePreviewParams::getResizeParamsByKey($operations[ImageService::OPERATION_RESIZE]);
    }

    if (isset($operations[ImageService::OPERATION_WATERMARK])) {
        $operations[ImageService::OPERATION_WATERMARK] =
            DefinitionImagePreviewParams::getWatermarkPositionParamsByKey($operations[ImageService::OPERATION_WATERMARK]);
    }


    // Передаем параметры и классы - реализаторы функционала обработки картинок
    $imageService = new ImageService(
        $file,
        $watermark,
        $operations,
        $app->di->get('imageCrop'),
        $app->di->get('imageResize'),
        $app->di->get('imageWatermarking')
    );

    try {
        // Валидация
        $imageService->validateParams(new ImageServiceValidator());

        echo $imageService->getProcessedImage()->response($file->getExtension());

        return true;
    } catch (Exception $exception) {
        return $response->sendError($exception->getMessage());
    }
});
