<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 18.04.18
 * Time: 13:15
 */

namespace App\Service\Interfaces;

use Intervention\Image\Image;

/**
 * Интерфейс функционала кропанья
 *
 * Interface ImageCropInterface
 * @package App\Service\Interfaces
 */
interface ImageCropInterface
{
    /**
     * Присвоение аттрибутов
     *
     * @param Image $file
     * @return ImageCropInterface
     */
    public function setAttributes(Image $file): ImageCropInterface;

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
    public function crop(int $width, int $height, int $x = null, int $y = null): void;
}
