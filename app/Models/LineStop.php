<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineStop extends Model
{
    protected $table = 'linestop';
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'line_id',
        'stop_id',
        'sequence'
    ];

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('line_id', $this->line_id)
            ->where('stop_id', $this->stop_id);
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class, 'line_id', 'line_id');
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(Stop::class, 'stop_id', 'stop_id');
    }
}
