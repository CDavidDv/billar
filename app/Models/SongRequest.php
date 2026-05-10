<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SongRequest extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'status',
        'votes',
        'ip_address',
    ];

    protected $casts = [
        'votes' => 'integer',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_PLAYED = 'played';

    const STATUS_REJECTED = 'rejected';
}
