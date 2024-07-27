<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
