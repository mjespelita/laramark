<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chatattachments extends Model
{
    /** @use HasFactory<\Database\Factories\ChatattachmentsFactory> */

    protected $fillable = ["chats_id","messages_id","original_name","stored_as","path","isTrash"];

    use HasFactory;

    public function messages()
    {
        return $this->belongsTo(Messages::class, 'chats_id');
    }
}
