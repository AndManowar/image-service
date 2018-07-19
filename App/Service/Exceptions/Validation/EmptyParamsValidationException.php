<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 04.05.18
 * Time: 14:35
 */

namespace App\Service\Exceptions\Validation;

/**
 * Class ValidationEmptyParamsException
 * @package App\Service\Exceptions\Validation
 */
class EmptyParamsValidationException extends AbstractValidationException
{
    /**
     * Конструктор
     *
     * @param string $message
     * @param int $code
     * @return void
     */
    public function __construct(string $message, int $code = 500)
    {
        parent::__construct($message, $code);
    }
}