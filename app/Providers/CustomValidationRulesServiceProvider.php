<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class CustomValidationRulesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //

        Validator::extend('required_if_not_customer', function ($attribute, $value, $parameters, $validator) {
            if (!auth()->user()->hasRole('Customer')) {
                return !empty($value);
            }
            return true;
        });

        Validator::replacer('required_if_not_customer', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The ' . $attribute . ' field is required.');
        });
    }
}
