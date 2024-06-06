<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['reminder_id', 'keyword'];

    
    public function reminder()
    {
        return $this->belongsTo(Reminder::class);
    }
}