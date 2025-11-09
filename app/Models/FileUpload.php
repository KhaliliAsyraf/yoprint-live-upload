<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $fillable = [
        'original_name',
        'path',
        'checksum',
        'status',
        'error',
        'uploaded_at',
        'processed_at'
    ];

    protected $dates = ['uploaded_at', 'processed_at'];
}
