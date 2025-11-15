<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messagereads extends Model
{
    /** @use HasFactory<\Database\Factories\MessagereadsFactory> */

    protected $fillable = ["users_id","messages_id","read_at","isTrash"];

    use HasFactory;

    public function users()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function messages()
    {
        return $this->belongsTo(Messages::class, 'messages_id');
    }
}
