<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 16.04.18
 * Time: 14:21
 */

namespace App\Service\Classes;

use App\Service\Interfaces\ImageCropInterface;
use Intervention\Image\Image;

/**
 * Класс для кропа картинки
 *
 * Class ImageCrop
 * @package App\Service\Classes
 */
class ImageCrop implements ImageCropInterface
{
    /**
     * Файл для проведения операции
     *
     * @var Image
     */
    private $file;

    /**
     * Присвоение аттрибутов
     *
     * @param Image $file
     * @return ImageCropInterface
     */
    public function setAttributes(Image $file): ImageCropInterface
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Метод кропа изображения
     * x и y - координаты верхнего левого угла,
     * если прямоугольный вырез. По умолчанию прямоугольная
     * часть будет центрирована на текущем изображении.
     *
     * @param int $width
     * @param int $height
     * @param int|null $x
     * @param int|null $y
     * @return void
     */
    public function crop(int $width, int $height, int $x = null, int $y = null): void
    {
        $this->file->crop($width, $height, $x, $y);
    }
}
