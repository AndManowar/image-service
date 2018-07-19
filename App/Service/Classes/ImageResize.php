<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 16.04.18
 * Time: 14:21
 */

namespace App\Service\Classes;

use App\Service\Interfaces\ImageResizeInterface;
use Intervention\Image\Image;
use InvalidArgumentException;

/**
 * Класс для ресайза картинки
 *
 * Class ImageResize
 * @package App\Service\Classes
 */
class ImageResize implements ImageResizeInterface
{
    /**
     * Изменить размеры текущего изображения на заданную ширину и высоту.
     * Якорь может быть определен, чтобы определить,
     * с какой точки изображения будет происходить изменение размера.
     * Установите режим относительно добавления или вычитания
     * заданной ширины или высоты в фактические размеры изображения.
     * Вы также можете передать цвет фона для появляющейся области изображения.
     *
     * @const
     */
    const RESIZE_TYPE_RESIZE_CANVAS = 'resizeCanvas';

    /**
     * Комбинированная обрезка и изменение размера,
     * чтобы форматировать изображение в интеллектуальном режиме.
     * Метод найдет оптимальное соотношение сторон вашей ширины и высоты
     * на текущем изображении автоматически, вырезает его и изменяет его
     * размер до заданного размера.
     *
     * @const
     */
    const RESIZE_TYPE_FIT = 'fit';

    /**
     * Цвет фона при операции Resize Canvas
     *
     * @const
     */
    const RESIZE_CANVAS_BG_COLOR = '#ffffff';

    /**
     * Относительный режим изменения размера
     *
     * @const
     */
    const IS_RESIZE_CANVAS_RELATIVE = false;

    /**
     * Константы с описаниями позиций ресайза
     * При resizeCanvas - точка, из которой произойдет изменение размера изображения
     * При fit - положение, в котором будет располагаться вырез
     *
     * @const
     */
    const POSITION_TOP_LEFT = 'top-left';
    const POSITION_TOP_RIGHT = 'top-right';
    const POSITION_LEFT = 'left';
    const POSITION_CENTER = 'center';
    const POSITION_RIGHT = 'right';
    const POSITION_BOTTOM_LEFT = 'bottom-left';
    const POSITION_BOTTOM = 'bottom';
    const POSITION_BOTTOM_RIGHT = 'bottom-right';
    const POSITION_TOP = 'top';

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
     * @return ImageResizeInterface
     */
    public function setAttributes(Image $file): ImageResizeInterface
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Метод для ресайза картинки(пока заглушка)
     *
     * @param integer $width
     * @param integer $height
     * @param string|null $mode
     * @param string $position
     * @return void
     */
    public function resize(
        int $width,
        int $height,
        string $mode = null,
        string $position = self::POSITION_CENTER
    ): void
    {
        switch ($mode) {
            case null:
                $this->file->resize($width, $height);
                break;
            case self::RESIZE_TYPE_FIT:
                $this->fit($width, $height);
                break;
            case self::RESIZE_TYPE_RESIZE_CANVAS:
                $this->file->resizeCanvas($width,
                    $height,
                    $position,
                    self::IS_RESIZE_CANVAS_RELATIVE,
                    self::RESIZE_CANVAS_BG_COLOR
                );
                break;
            default:
                throw new InvalidArgumentException('Invalid resize mode given');
        }
    }

    /**
     * Ресайз картинки исходя из размеров (ресайз по меньшей стороне) + кроп до нужного размера
     *
     * @param integer $width
     * @param integer $height
     * @return void
     */
    private function fit($width, $height): void
    {
        $this->file->width() >= $this->file->height() ? $this->file->heighten($height) : $this->file->widen($width);
        $this->file->crop($width, $height);
    }
}
