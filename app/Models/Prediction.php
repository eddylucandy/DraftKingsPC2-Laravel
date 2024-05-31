<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'football_player_id',
        'value_predict',
        'price_predict'
    ];

    public function footballPlayer()
    {
        return $this->belongsTo(FootballPlayer::class, 'football_player_id');
    }
}
