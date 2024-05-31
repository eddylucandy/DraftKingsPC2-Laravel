<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use Illuminate\Http\Request;

class PredictionController extends Controller {

    public function makePredictions(Request $request)
    {
        // Obtener las predicciones más recientes para cada jugador
        $latestPredictions = Prediction::with(['footballPlayer', 'footballPlayer.stats'])
            ->latest()
            ->get()
            ->groupBy('football_player_id')
            ->map(function ($playerPredictions) {
                return $playerPredictions->first();
            });

        if ($latestPredictions->isEmpty()) {
            return response()->json([
                'message' => 'no hay predicciones',
                'status' => '404',
            ], 404);
        }

        // Formatear los datos
        $formattedPredictions = $latestPredictions->map(function ($prediction) {
            $player = $prediction->footballPlayer;
            $latestStat = $player->latestStat;

            return [
                'player_name' => $player->name,
                'image_url' => $player->image_url,
                'score' => $latestStat ? $latestStat->score : null,
                'fantasy_market_value' => $latestStat ? $latestStat->fantasy_market_value : null,
                'value_predict' => $prediction->value_predict,
                'price_predict' => $prediction->price_predict,
            ];
        });

        return response()->json($formattedPredictions->values(), 200);
    }

}
