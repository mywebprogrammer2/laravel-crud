<?php

namespace App\Validators;

use \Prettus\Validator\Contracts\ValidatorInterface;
use \Prettus\Validator\LaravelValidator;

/**
 * Class CustomerValidator.
 *
 * @package namespace App\Validators;
 */
class CustomerValidator extends LaravelValidator
{
    /**
     * Validation Rules
     *
     * @var array
     */
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,:id|max:255',
        ],
    ];
}
