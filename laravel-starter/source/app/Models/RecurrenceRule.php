<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurrenceRule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['reminder_id', 'type', 'frequency', 'start_date', 'end_date'];

    
    public function reminder()
    {
        return $this->belongsTo(Reminder::class);
    }
}