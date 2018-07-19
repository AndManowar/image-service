<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 12.04.18
 * Time: 15:18
 */

namespace App\Validators;

use App\Service\Classes\ImageResize;
use App\Service\Classes\ImageWatermarking;
use App\Service\Exceptions\Validation\EmptyParamsValidationException;
use App\Service\Exceptions\Validation\InvalidAttributesValidationException;
use App\Service\Exceptions\Validation\InvalidValueValidationException;
use App\Service\Exceptions\Validation\RequiredAttributesValidationException;
use App\Service\ImageService;
use InvalidArgumentException;

/**
 * Валидатор параметров, переданных в ImageService
 *
 * Class ParamsValidator
 * @package App\Validators
 */
class ImageServiceValidator
{
    /**
     * Правило валидации - строка
     *
     * @const
     */
    const RULE_STRING = 'string';

    /**
     * Правило валидации - целое число
     *
     * @const
     */
    const RULE_INTEGER = 'integer';

    /**
     * Правило валидации - список допустимых значений
     *
     * @const
     */
    const RULE_RANGE = 'range';

    /**
     * Массив возможных ключей параметра ImageService->operations для валидации и так же обязательные
     * параметры операций resize, crop и watermark.
     *
     * @var array
     */
    private $operationsKeysList = [
        ImageService::OPERATION_CROP      => [
            ImageService::HEIGHT_KEY,
            ImageService::WIDTH_KEY
        ],
        ImageService::OPERATION_RESIZE    => [
            ImageService::HEIGHT_KEY,
            ImageService::WIDTH_KEY,
        ],
        ImageService::OPERATION_WATERMARK => [
            ImageService::WM_POSITION_KEY
        ]
    ];

    /**
     * Правила валидации аттрибутов операций
     * в случае с RULE_RANGE => [
     *              'attribute' => [
     *                  possible values...
     *         ]
     * ]
     *
     * @var array
     */
    private $validationRules = [
        self::RULE_STRING  => [
            ImageService::WM_POSITION_KEY
        ],
        self::RULE_INTEGER => [
            ImageService::WIDTH_KEY,
            ImageService::HEIGHT_KEY
        ],
        self::RULE_RANGE   => [
            ImageService::WM_POSITION_KEY => [
                ImageWatermarking::POSITION_CENTER,
                ImageWatermarking::POSITION_SPLIT_ROWS
            ],
            ImageService::RESIZE_MODE     => [
                ImageResize::RESIZE_TYPE_RESIZE_CANVAS,
                ImageResize::RESIZE_TYPE_FIT
            ],
            ImageService::RESIZE_ANCHOR   => [
                ImageResize::POSITION_CENTER,
                ImageResize::POSITION_BOTTOM_LEFT,
                ImageResize::POSITION_BOTTOM_RIGHT,
                ImageResize::POSITION_BOTTOM,
                ImageResize::POSITION_TOP,
                ImageResize::POSITION_TOP_LEFT,
                ImageResize::POSITION_TOP_RIGHT,
                ImageResize::POSITION_LEFT,
                ImageResize::POSITION_RIGHT
            ]
        ]
    ];

    /**
     * Валидация параметров массива operations
     *
     * @param array $data
     * @return void
     * @throws EmptyParamsValidationException
     * @throws InvalidAttributesValidationException
     * @throws InvalidValueValidationException
     * @throws RequiredAttributesValidationException
     */
    public function validateParams(array $data): void
    {
        // Если нету никаких операций - эррор
        if (!$data[ImageService::OPERATION_WATERMARK] && !$data['operations']) {
            throw new EmptyParamsValidationException('You must add at least one operation');
        }

        $this->validateWatermarkAndItsParams($data);

        if ($data['operations']) {
            // Валидируем параметры $data['operations']
            $this->validateOperationsData($data['operations']);
        }
    }

    /**
     * Валидируем водяной знак и наличие параметров с ключем операции watermark
     *
     * @param array $data
     * @return void
     * @throws InvalidAttributesValidationException
     */
    private function validateWatermarkAndItsParams(array $data): void
    {
        // Если пришел вотермарк и не пришли параметры его нанесения
        if ($data[ImageService::OPERATION_WATERMARK] && !$data['operations']) {
            throw new InvalidAttributesValidationException('Missing operations[watermark] parameters but the watermark file came');
        }

        //Если передали параметры нанесения вотермарки, но не передали файл - эррор
        if (!$data[ImageService::OPERATION_WATERMARK] && isset($data['operations'][ImageService::OPERATION_WATERMARK])) {
            throw new InvalidAttributesValidationException('Missing watermark file but the operations in the array came');
        }
    }

    /**
     * Валидация операций над картинкой
     *
     * @param array $operations
     * @return void
     * @throws EmptyParamsValidationException
     * @throws InvalidAttributesValidationException
     * @throws InvalidValueValidationException
     * @throws RequiredAttributesValidationException
     */
    private function validateOperationsData(array $operations): void
    {
        // Не помещалось ;)
        $flippedArrayKeys = array_flip(array_keys($this->operationsKeysList));

        // Существует ли хотя бы один из ключей, необходимых для параметра ImageService->operations
        if (count(array_intersect_key($flippedArrayKeys, $operations)) === 0) {
            throw new EmptyParamsValidationException('You must add at least one operation');
        }

        foreach ($operations as $operationName => $operationAttributes) {
            // Если в массиве "левый" ключ - возвращаем эррор
            $this->checkForUnknownOperations($operationName);
            // Если во вложенном массиве нету необходимых параметров - возвращаем эррор
            $this->checkRequiredAttributes($operationName, $operationAttributes);

            foreach ($operationAttributes as $attribute => $value) {
                // Валидируем параметры каждой операции
                $this->validateValues($attribute, $value, $operationName);
            }
        }
    }

    /**
     * Валидация значений операций
     *
     * @param string $attribute
     * @param string $value
     * @param string $operationName
     * @return void
     * @throws InvalidArgumentException
     * @throws InvalidValueValidationException
     */
    private function validateValues(string $attribute, string $value, string $operationName): void
    {
        foreach ($this->validationRules as $ruleName => $ruleAttributes) {
            if (in_array($attribute, $ruleAttributes)
                || array_key_exists($attribute, $this->validationRules[$ruleName])
            ) {
                switch ($ruleName) {
                    case self::RULE_STRING:
                        $this->validateString($attribute, $value, $operationName, 'operation');
                        break;
                    case self::RULE_INTEGER:
                        $this->validateInteger($attribute, $value, $operationName, 'operation');
                        break;
                    case self::RULE_RANGE:
                        $this->validateRange($attribute, $value, $operationName, 'operation');
                        break;
                    default:
                        throw new InvalidArgumentException('Unknown validation rule name: ' . $ruleName);
                        break;
                }
            }
        }
    }

    /**
     * Проверка на наличие неизвестных операций массива $operations
     *
     * @param string $operationName
     * @return void
     * @throws InvalidArgumentException
     * @throws InvalidAttributesValidationException
     */
    private function checkForUnknownOperations(string $operationName): void
    {
        if (!in_array($operationName, array_keys($this->operationsKeysList))) {
            throw new InvalidAttributesValidationException("Unknown operation name: {$operationName}");
        }
    }

    /**
     * Проверка необходимых аттрибутов операции из массива $this->operationsKeysList
     *
     * @param string $operationName - операция
     * @param array $operationAttributes - аттрибуты операции
     * @return void
     * @throws RequiredAttributesValidationException
     */
    private function checkRequiredAttributes(string $operationName, array $operationAttributes): void
    {
        if (count(array_intersect_key(array_flip($this->operationsKeysList[$operationName]), $operationAttributes))
            !== count($this->operationsKeysList[$operationName])
        ) {
            // Получаем недостающие необходимые аттрибуты для проведения текущей операции
            $requiredAttributes = implode(
                ', ',
                array_keys(array_diff_key(array_flip($this->operationsKeysList[$operationName]), $operationAttributes))
            );

            throw new RequiredAttributesValidationException("Attribute operations[{$operationName}][{$requiredAttributes}] is required.");
        }
    }

    /**
     * Правило валидации - строка
     *
     * @param string $attribute
     * @param string $value
     * @param string $operationName
     * @param string $parent
     * @return void
     * @throws InvalidValueValidationException
     */
    private function validateString(string $attribute, string $value, string $operationName, string $parent): void
    {
        if (!is_string($value)) {
            throw new InvalidValueValidationException("Value {$parent}[{$operationName}][{$attribute}] must be a string");
        }
    }

    /**
     * Правило валидации - целое число
     *
     * @param string $attribute
     * @param string $value
     * @param string $operationName
     * @param string $parent
     * @return void
     * @throws InvalidValueValidationException
     */
    private function validateInteger(string $attribute, string $value, string $operationName, string $parent): void
    {
        if (!preg_match('/^\d+$/', $value)) {
            throw new InvalidValueValidationException("Value {$parent}[{$operationName}][{$attribute}] must be an integer");
        }
    }

    /**
     * Правило валидации - диапазон значений
     *
     * @param string $attribute
     * @param string $value
     * @param string $operationName
     * @param string $parent
     * @return void
     * @throws InvalidValueValidationException
     */
    private function validateRange(string $attribute, string $value, string $operationName, string $parent): void
    {
        if (!in_array($value, $this->validationRules[self::RULE_RANGE][$attribute])) {
            throw new InvalidValueValidationException(
                "Invalid value for {$parent}[{$operationName}][{$attribute}], allow values are: [" .
                join(', ', $this->validationRules[self::RULE_RANGE][$attribute]) . "]"
            );
        }
    }
}
