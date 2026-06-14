<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model SearchLog: menyimpan riwayat setiap query yang dicari.
 */
class SearchLog extends Model
{
    protected $fillable = [
        'query',
        'result_count',
        'execution_time',
        'user_id',
    ];

    protected $casts = [
        'execution_time' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}