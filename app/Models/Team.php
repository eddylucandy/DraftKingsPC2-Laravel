<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'group_id', 'user_id'
    ];

    public function players() {
        return $this->belongsToMany(FootballPlayer::class, 'player_teams', 'team_id', 'player_id');
    }
}
