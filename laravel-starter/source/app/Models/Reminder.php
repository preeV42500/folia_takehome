<?php

namespace App\Models;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    
    // Get reminders within a given date range based on their recurrence rules
    public function isReminderInDateRange(Carbon $startDate, Carbon $endDate): bool 
    {
        // if a reminder occurs within the given date range based on any of its recurrence rules, return true 

        foreach ($this->recurrenceRules as $recurrenceRule) {
            if (doesRepeat($recurrenceRule, $startDate, $endDate)) {
                return true;
            }
        }

        return false;
    }

    // Check if the reminder re-occurs based on a given recurrence rule and the input date range
    protected function doesRepeat(RecurrenceRule $recurrenceRule, Carbon $startDate, Carbon $endDate): bool 
    {
        $doesReminderRepeat = false;
        $currentRecurrenceDate = $recurrenceRule->start_date;

        // current recurrence date represents when the next recurrence is supposed to occur
        // if the startDate of the range is greater than the initial start date of the recurrence, 
        // keep getting the next date where the reminder occurs until it's greater than or equal to startDate
        while ($startDate->gt($currentRecurrenceDate)) {
            $currentRecurrenceDate = $this->getNextRecurrenceDate($recurrenceRule, $currentRecurrenceDate);
        }

        // if currRecurrenceDate is less than or equal to the recurrence rule's end_date and is less and or equal to 
        // the endDate of the given range, mark doesReminderRepeat as true 

        if ($currentRecurrenceDate->lte($recurrenceRule->end_date) && $currentRecurrenceDate->lte($endDate)) {
            $doesReminderRepeat = true;
        }

        return $doesReminderRepeat;
    }
   
    // Use the current date and recurrence rule to get the date of the next recurrence 
    protected function getNextRecurrenceDate(RecurrenceRule $recurrenceRule, Carbon $date): Carbon 
    {
        switch ($recurrenceRule->type) {
            case 'daily':
                return $date->addDay();

            case 'weekly':
                $dayOfWeek = $recurrenceRule->frequency;
                $currentDayOfWeek = $date->dayOfWeek;
                // number of days to 'adjust' current date by to get the day of the week specified by the recurrence rule
                $daysDiff = ($dayOfWeek - $currentDayOfWeek + 7) % 7;   
                return $date->addDays($daysDiff);

            case 'monthly':
                $dayOfMonth = $recurrenceRule->frequency;
                $nextMonth = $date->addMonth();
                $nextDate = $nextMonth->day($dayOfMonth);
                return $nextDate;

            case 'yearly':
                $dayOfYear = $recurrenceRule->frequency;
                $nextYear = $date->addYear();
                $nextDate = $nextYear->dayOfYear($dayOfYear);
                return $nextDate;


            case 'custom':
                $daysDiff = $recurrenceRule->frequency;
                return $date->addDays($daysDiff);

            default:
                return $date->addDay();
        }
    }
     /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:Y-m-d H:i:s A',
            'end_time' => 'datetime:Y-m-d H:i:s A',
        ];
    }
}