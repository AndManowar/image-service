<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 04.05.18
 * Time: 14:46
 */

namespace App\Service\Exceptions\Validation;

/**
 * Class ValidationInvalidValueException
 * @package App\Service\Exceptions\Validation
 */
class InvalidValueValidationException extends AbstractValidationException
{
    /**
     * ValidationInvalidValueException constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message, int $code = 500)
    {
        parent::__construct($message, $code);
    }

}