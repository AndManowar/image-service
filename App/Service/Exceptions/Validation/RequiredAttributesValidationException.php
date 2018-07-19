<?php
/**
 * Created by PhpStorm.
 * User: manowartop
 * Date: 04.05.18
 * Time: 14:44
 */

namespace App\Service\Exceptions\Validation;

/**
 * Class ValidationRequiredAttributesException
 * @package App\Service\Exceptions\Validation
 */
class RequiredAttributesValidationException extends AbstractValidationException
{
    /**
     * ValidationRequiredAttributesException constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message, int $code = 500)
    {
        parent::__construct($message, $code);
    }
}