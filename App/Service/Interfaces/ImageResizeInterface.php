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
 * Интерфейс функционала ресайза
 *
 * Interface ImageResizeInterface
 * @package App\Service\Interfaces
 */
interface ImageResizeInterface
{
    /**
     * Присвоение аттрибутов
     *
     * @param Image $file
     * @return ImageResizeInterface
     */
    public function setAttributes(Image $file): ImageResizeInterface;

    /**
     * Функционал ресайза
     *
     * @param int $width
     * @param int $height
     * @param string|null $mode
     * @param string $position
     * @return void
     */
    public function resize(int $width, int $height, string $mode = null, string $position): void;
}
