<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FrequencyValidationRule implements Rule
{
    /**
     * The type of the recurrence.
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new rule instance.
     *
     * @param  string  $type
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    // Determine if validation passes
    public function passes($attribute, $value): bool
    {    
        switch ($this->type) {
            case 'daily':
                return $value === 1;
            case 'weekly':
                return $value >= 1 && $value <= 7;
            case 'monthly':
                return $value >= 1 && $value <= 31;
            case 'yearly':
                return $value >= 1 && $value <= 365;
            case 'custom':
                return true;
            default:
                return false;
        }
    }

   
    public function message(): string
    {
        switch ($this->type) {
            case 'daily':
                return 'The :attribute must be an integer equal to 1 for the ' . {$this->type} . ' recurrence type.';
            case 'weekly':
                return 'The :attribute must be an integer between 1 and 7 for the ' . {$this->type} . ' recurrence type.';
            case 'monthly':
                return 'The :attribute must be an integer between 1 and 31 for the ' . {$this->type} . ' recurrence type.';
            case 'yearly':
                return 'The :attribute must be an integer between 1 and 365 for the ' . {$this->type} . ' recurrence type.';
            default:
                return 'The :attribute has an invalid value.';
        }
    }
}