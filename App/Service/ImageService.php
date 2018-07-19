<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 12.04.18
 * Time: 11:45
 */

namespace App\Service;

use App\Service\Classes\ImageResize;
use App\Service\Interfaces\ImageCropInterface;
use App\Service\Interfaces\ImageResizeInterface;
use App\Service\Interfaces\ImageWatermarkingInterface;
use http\Exception\InvalidArgumentException;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Exception\NotWritableException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use App\Validators\ImageServiceValidator;
use Phalcon\Http\Request\File;

/**
 * Сервис для операции над картинками (ресайз/вотермарк/кроп)
 *
 * Class ImageService
 * @package App\Service
 */
class ImageService
{
    /**
     * Параметр $this->operations - операция crop
     *
     * @const
     */
    const OPERATION_CROP = 'crop';

    /**
     * Параметр $this->operations - операция resize
     *
     * @const
     */
    const OPERATION_RESIZE = 'resize';

    /**
     * Операция нанесения водяного знака
     *
     * @const
     */
    const OPERATION_WATERMARK = 'watermark';

    /**
     * Ключ массива с параметром ширины
     *
     * @const
     */
    const WIDTH_KEY = 'width';

    /**
     * Ключ массива с параметром высоты
     *
     * @const
     */
    const HEIGHT_KEY = 'height';

    /**
     * Тип ресайза (resizeCanvas, fit)
     *
     * @const
     */
    const RESIZE_MODE = 'mode';

    /**
     * Ключ с позицией WM
     *
     * @const
     */
    const WM_POSITION_KEY = 'position';

    /**
     * Позиция ресайза
     *
     * @const
     */
    const RESIZE_ANCHOR = 'anchor';

    /**
     * Массив приоритета операций
     *
     * @var array
     */
    private $operationsPriorityList = [
        self::OPERATION_RESIZE,
        self::OPERATION_CROP,
        self::OPERATION_WATERMARK
    ];

    /**
     * Оригинал файла для обработки(обязательный параметр)
     *
     * @var Image
     */
    private $file;

    /**
     * Файл с вотермаркой(необязательный параметр)
     *
     * @var null|Image
     */
    private $watermark;

    /**
     * Файл с вотермаркой типа File
     *
     * @var null|File
     */
    private $watermarkFile;

    /**
     * Массив параметров для операций(необязательный параметр)
     *
     * $operations = [
     *     'crop' => [
     *          'width'  => 100
     *          'height' => 200
     *          ...
     *      ],
     *     'resize' => [
     *          'width'  => 200
     *          'height' => 250
     *          'mode' => 'fit' || 'resizeCanvas' || null
     *          'anchor' => 'bottom' || 'top' || ...
     *          ...
     *    ],
     *    'watermark' => [
     *          'position' => 'center'
     *           ...
     *   ]
     * ];
     *
     * @var array
     */
    private $operations;

    /**
     * Реализатор функционала кропа
     *
     * @var ImageCropInterface
     */
    private $imageCrop;

    /**
     * Реализатор функционала ресайза
     *
     * @var ImageResizeInterface
     */
    private $imageResize;

    /**
     * Реализатор функционала нанесения водяного знака
     *
     * @var ImageWatermarkingInterface
     */
    private $imageWatermark;

    /**
     * ImageService constructor.
     *
     * @param File $file
     * @param File|null $watermarkFile
     * @param array|null $operations
     * @param ImageCropInterface $imageCrop
     * @param ImageResizeInterface $imageResize
     * @param ImageWatermarkingInterface $imageWatermarking
     * @return void
     */
    public function __construct(
        File $file,
        File $watermarkFile = null,
        array $operations = null,
        ImageCropInterface $imageCrop,
        ImageResizeInterface $imageResize,
        ImageWatermarkingInterface $imageWatermarking
    )
    {
        $this->file = $file;
        $this->watermarkFile = $watermarkFile;
        $this->operations = $operations;
        $this->imageCrop = $imageCrop;
        $this->imageResize = $imageResize;
        $this->imageWatermark = $imageWatermarking;
    }

    /**
     * Обработка изображения
     *
     * @return Image
     * @throws NotWritableException|InvalidArgumentException
     */
    public function getProcessedImage(): Image
    {
        // Выполняем операции по порятку из массива приоритетов $this->operationsPriorityList
        foreach ($this->operationsPriorityList as $operation) {
            // Если операция из $this->operationsPriorityList не пришла параметром в $this->operations - пропускаем
            if (!array_key_exists($operation, $this->operations)) {
                continue;
            }
            $this->doOperation($operation);
        }

        return $this->file;
    }

    /**
     * Валидация параметров запроса
     *
     * @param ImageServiceValidator $paramsValidator
     * @return void
     * @throws Exceptions\Validation\EmptyParamsValidationException
     * @throws Exceptions\Validation\InvalidAttributesValidationException
     * @throws Exceptions\Validation\InvalidValueValidationException
     * @throws Exceptions\Validation\RequiredAttributesValidationException
     */
    public function validateParams(ImageServiceValidator $paramsValidator): void
    {
        // Если есть ошибки валидации запроса - кидаем экзепшн
        $paramsValidator->validateParams($this->getAttributes());

        // Присваиваем картинки (кидает экзепшн)
        $this->loadParamsFromRequest();
    }

    /**
     * Присваиваем картинки из полученных входящих параметров
     *
     * @return void
     * @throws NotReadableException
     */
    private function loadParamsFromRequest(): void
    {
        $imageManager = new ImageManager();

        $this->file = $imageManager->make($this->file->getTempName());

        if ($this->watermarkFile) {
            $this->watermark = $imageManager->make($this->watermarkFile->getTempName());
        }
    }

    /**
     * Выполнение операций над картинкой
     *
     * @param string $operation
     * @return void
     * @throws InvalidArgumentException
     */
    private function doOperation(string $operation): void
    {
        switch ($operation) {
            case self::OPERATION_RESIZE:
                $this->imageResize->setAttributes($this->file)->resize(
                    $this->operations[self::OPERATION_RESIZE][self::WIDTH_KEY],
                    $this->operations[self::OPERATION_RESIZE][self::HEIGHT_KEY],
                    $this->operations[self::OPERATION_RESIZE][self::RESIZE_MODE] ?? null,
                    $this->operations[self::OPERATION_RESIZE][self::RESIZE_ANCHOR] ?? ImageResize::POSITION_CENTER
                );
                break;

            case self::OPERATION_CROP:
                $this->imageCrop->setAttributes($this->file)->crop(
                    $this->operations[self::OPERATION_CROP][self::WIDTH_KEY],
                    $this->operations[self::OPERATION_CROP][self::HEIGHT_KEY]);
                break;

            case self::OPERATION_WATERMARK:
                if (!is_null($this->watermark)) {
                    $this->imageWatermark->setAttributes($this->file, $this->watermark)
                        ->setWatermark($this->operations[self::OPERATION_WATERMARK][self::WM_POSITION_KEY]);
                }
                break;

            default:
                throw new InvalidArgumentException('Invalid argument for operation');
                break;
        }
    }

    /**
     * Аттрибуты для валидации
     *
     * @return array
     */
    private function getAttributes(): array
    {
        return [
            'watermark'  => $this->watermarkFile,
            'operations' => $this->operations
        ];
    }
}
