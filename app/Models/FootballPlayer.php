<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FootballPlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'real_team', 'foot', 'image_url', 'fantasy_position',
        'position_short_name', 'position_group', 'age', 'shirt_number', 'nationality'
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'player_teams', 'player_id', 'team_id');
    }

    public function stats()
    {
        return $this->hasMany(PlayerStat::class, 'football_player_id');
    }

    public function latestStat()
    {
        return $this->hasOne(PlayerStat::class, 'football_player_id')->latestOfMany();
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'football_player_id');
    }
}
