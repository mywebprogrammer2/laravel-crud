<?php

namespace App\Validators;

use \Prettus\Validator\Contracts\ValidatorInterface;
use \Prettus\Validator\LaravelValidator;

/**
 * Class InvoiceValidator.
 *
 * @package namespace App\Validators;
 */
class InvoiceValidator extends LaravelValidator
{
    /**
     * Validation Rules
     *
     * @var array
     */
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'project_id' => 'required',
            'due_date' => 'required|date',
            'items' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'project_id' => 'required',
            'due_date' => 'required|date',
            'items' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required',
        ],
    ];

    protected $messages = [
        'project_id.required' => 'Project must be provided',
    ];

}
