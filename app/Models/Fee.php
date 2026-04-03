<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable = [
        'student_id',
        'class_room_id',
        'type',
        'amount',
        'due_date',
        'status'
    ];

    // ✅ One Fee has many payments
    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    // ✅ One Fee has many notifications
    public function notifications()
    {
        return $this->hasMany(FeeNotification::class);
    }

    // Optional: Student relation
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Optional: ClassRoom relation
    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('paid_amount');
    }

    public function getOverdueDaysAttribute()
    {
        $paid = $this->paid_amount ?? 0;
        $remaining = $this->amount - $paid;

        if ($remaining <= 0) return 0;

        $dueDate = \Carbon\Carbon::parse($this->due_date);

        return $dueDate->isPast()
            ? $dueDate->diffInDays(now())
            : 0;
    }

    public function getRowStyleAttribute()
    {
        if ($this->overdue_days >= 60) {
            return ['class' => 'table-danger', 'style' => ''];
        } elseif ($this->overdue_days >= 45) {
            return ['class' => '', 'style' => 'background-color: #FFB6C1 !important;'];
        } elseif ($this->overdue_days >= 30) {
            return ['class' => '', 'style' => 'background-color: #FFF3CD !important;'];
        }

        return ['class' => '', 'style' => ''];
    }
}
