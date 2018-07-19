<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 13.04.18
 * Time: 13:02
 */

namespace App\Service\Classes;

use App\Service\Interfaces\ImageWatermarkingInterface;
use Intervention\Image\Image;

/**
 * Класс для нанесения вотермарки
 *
 * Class ImageWatermarking
 * @package App\Service\Classes
 */
class ImageWatermarking implements ImageWatermarkingInterface
{
    /**
     * Позиция вотермарки - по центру картинки
     *
     * @const
     */
    const POSITION_CENTER = 'center';

    /**
     * Позиция вотермарки - по углам
     *
     * @const
     */
    const POSITION_SPLIT_ROWS = 'split-rows';

    /**
     * Отступ от края до водяного знака(задается для одной стороны в %)
     *
     * @const
     */
    const SIDE_INDENTION = 10;

    /**
     * Файл для проведения операции
     *
     * @var Image
     */
    private $file;

    /**
     * Картинка - водяной знак
     *
     * @var Image
     */
    private $watermark;

    /**
     * Массив с позициями вотермарки при типе позиционирования self::POSITION_SPLIT_ROWS
     *
     * @var array
     */
    private $splitRowsWatermarkPositionsList = [
        'top-left',
        'top-right',
        'bottom-left',
        'bottom-right'
    ];

    /**
     * Присвоение аттрибутов
     *
     * @param Image $file
     * @param Image $watermark
     * @return ImageWatermarkingInterface
     */
    public function setAttributes(Image $file, Image $watermark): ImageWatermarkingInterface
    {
        $this->file = $file;
        $this->watermark = $watermark;

        return $this;
    }

    /**
     * Метод нанесения вотермарки
     *
     * @param string $position
     * @return void
     */
    public function setWatermark(string $position): void
    {
        $this->scaleWatermark($position);

        if ($position === self::POSITION_SPLIT_ROWS) {
            foreach ($this->splitRowsWatermarkPositionsList as $positionItem) {
                $this->file->insert($this->watermark, $positionItem);
            }
        } else {
            $this->file->insert($this->watermark, $position);
        }
    }

    /**
     * Масштабирование вотермарки, исходя из масштаба картинки и места нанесения
     *
     * @param string $position
     * @return void
     */
    private function scaleWatermark(string $position): void
    {
        // Если позиционируем по центру - растягиваем WM по ширине картинки учитывая отступы self::SIDE_INDENTION
        if ($position === self::POSITION_CENTER) {
            $scaleValue = (100 - self::SIDE_INDENTION * 2);
        } else { // Если позиция в 2 ряда - растяваем по ширине/2 с учетом отступов self::SIDE_INDENTION
            $scaleValue = (50 - self::SIDE_INDENTION * 2);
        }

        $fix_size = $this->file->width() * $scaleValue / 100;

        // Вычисление коэфициента масштабирования с учетом горизонтальности или вертикальности картинки
        $k = min($fix_size / $this->watermark->width(), $fix_size / $this->watermark->height());

        // Перемасштабирование вотермарки с учетом новых размеров
        $this->watermark->resize($this->watermark->width() * $k, $this->watermark->height() * $k);
    }
}
