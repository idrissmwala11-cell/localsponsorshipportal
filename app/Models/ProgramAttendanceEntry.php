<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramAttendanceEntry extends Model
{
    protected $fillable = [
        'program_attendance_session_id',
        'participant_id',
        'is_present',
    ];

    protected $casts = [
        'is_present' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(ProgramAttendanceSession::class, 'program_attendance_session_id');
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
