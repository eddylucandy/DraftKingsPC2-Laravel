<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'football_player_id', 'real_market_value', 'fantasy_market_value', 'value_fluctuation',
        'average_rating', 'score', 'points_from_penultimate_match', 'games_played', 'minutes_played',
        'goals', 'assists', 'assists_no_goal', 'balls_into_box', 'clearances', 'dribbles', 'shots_on_goal',
        'balls_recovered', 'possession_losses', 'penalties_missed', 'goals_conceded', 'red_cards', 'set_pieces',
        'penalties_scored', 'yellow_cards', 'second_yellow_cards', 'penalties_won', 'penalties_saved',
        'own_goals', 'created_at'
    ];

    public function player()
    {
        return $this->belongsTo(FootballPlayer::class, 'football_player_id');
    }
}
