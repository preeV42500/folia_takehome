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

    
    // Get all instances where a reminder occurs within a given date range based on all its recurrence rules
    public function getRemindersInDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        $occurrences = new Collection();   
        foreach ($this->recurrenceRules as $recurrenceRule) {
            $occurrences = $occurrences->merge($this->listOfReminderOccurrences($recurrenceRule, $startDate, $endDate));
        }

        return $occurrences;
    }

    // Get the a list of all the instances where the reminder occurs within the date range, and for a single recurrence rule  
    protected function listOfReminderOccurrences(RecurrenceRule $recurrenceRule, Carbon $startDate, Carbon $endDate): array
    {
        $doesReminderRepeat = false;
        $recurrenceStartDate = Carbon::parse($recurrenceRule->start_date);
        $recurrenceEndDate = Carbon::parse($recurrenceRule->end_date);
        $currentRecurrenceDate = $recurrenceStartDate;
        // if the occurrence will never happen between startDate and endDate 
        if ($recurrenceEndDate->lt($startDate) || $recurrenceStartDate->gt($endDate)) {
            return [];
        }
        $occurrences = [];
        // current recurrence date represents when the next occurrence will be
        // if the startDate of the range is greater than the initial start date of the occurrence, 
        // keep getting the next date where the reminder occurs until it's greater than or equal to startDate
        while ($startDate->gt($currentRecurrenceDate)) {
            $currentRecurrenceDate = $this->getNextRecurrenceDate($recurrenceRule, $currentRecurrenceDate);
        }
        // while recurrence is within the bounds of its end_date and endDate of search range 
        while($currentRecurrenceDate->lte($endDate) && $currentRecurrenceDate->lte($recurrenceEndDate)) {
            $occurrences[] = [
                'reminder_id' => $recurrenceRule->reminder_id,
                'title' => $recurrenceRule->reminder->title,
                'date' => $currentRecurrenceDate
            ];
            $currentRecurrenceDate = $this->getNextRecurrenceDate($recurrenceRule, $currentRecurrenceDate);
        }
      
        return $occurrences;
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
                // if day of month is less than current date's day, then add a month 
                // e.g. if the recurrence is on the 14th of a month and the current date is the 15th of the month,
                // the next recurrence would happen one month later on the 14th of the month
                if ($dayOfMonth < $date->day()) {
                    $date = $date->addMonth();
                }
                $nextDate = $date->day($dayOfMonth);
                return $nextDate;

            case 'yearly':
                $dayOfYear = $recurrenceRule->frequency;
                // if day of year is less than current date's day, then add an year
                if ($dayOfYear < $date->dayOfYear()) {
                    $date = $date->addYear();
                }
                $nextDate = $date->dayOfYear($dayOfYear);
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