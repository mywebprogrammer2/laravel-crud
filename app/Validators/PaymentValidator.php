<?php

namespace App\Validators;

use \Prettus\Validator\Contracts\ValidatorInterface;
use \Prettus\Validator\LaravelValidator;

/**
 * Class PaymentValidator.
 *
 * @package namespace App\Validators;
 */
class PaymentValidator extends LaravelValidator
{
    /**
     * Validation Rules
     *
     * @var array
     */
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'payment_date' => 'required|date',
            'amount_paid' => 'required|min:0',
            'payment_method' => 'required|string',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'payment_date' => 'required|date',
            'amount_paid' => 'required|min:0',
            'payment_method' => 'required|string',
        ],
    ];
}
