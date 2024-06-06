<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'title', 'start_time', 'end_time'];

    
    // Get the recurrence rules for a reminder
    public function recurrenceRules()
    {
        return $this->hasMany(RecurrenceRule::class);
    }

    
    // Get the keywords for a reminder
    public function keywords()
    {
        return $this->hasMany(Keyword::class);
    }

    
    // Get reminders for a given date range based on their recurrence rules
    public function getRemindersForDateRange(Carbon $startDate, Carbon $endDate): Collection 
    {
        // TODO
    }
}