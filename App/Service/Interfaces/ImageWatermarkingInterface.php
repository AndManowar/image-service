<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 18.04.18
 * Time: 13:19
 */

namespace App\Service\Interfaces;

use Intervention\Image\Image;

/**
 * Интерфейс функционала нанесения водяного знака
 *
 * Interface ImageResizeInterface
 * @package App\Service\Interfaces
 */
interface ImageWatermarkingInterface
{
    /**
     * Присвоение аттрибутов
     *
     * @param Image $file
     * @param Image $watermark
     * @return ImageWatermarkingInterface
     */
    public function setAttributes(Image $file, Image $watermark): ImageWatermarkingInterface;

    /**
     * Метод нанесения вотермарки
     *
     * @param string $position
     * @return void
     */
    public function setWatermark(string $position): void;
}
