<?php

namespace App\Http\Controllers;

use App\Models\FootballPlayer;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class PlayersController extends Controller
{

    public function getMyPlayers(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Cargar los equipos del usuario con los jugadores y sus estadísticas más recientes
            $teams = $user->teams()->with(['players.stats' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])->get();

            $players = $teams->pluck('players')->flatten();

            $sortedPlayers = $players->sort(function ($a, $b) {
                $positions = [
                    'POR' => 1,
                    'CEN' => 2, 'LD' => 2, 'LI' => 2,
                    'MC' => 3, 'MCO' => 3, 'PIV' => 3,
                    'DEL' => 4, 'EI' => 4, 'ED' => 4
                ];

                $positionA = $positions[$a->position_short_name] ?? 999;
                $positionB = $positions[$b->position_short_name] ?? 999;

                return $positionA <=> $positionB;
            });

            // Formatear los datos de los jugadores y sus estadísticas más recientes
            $formattedPlayers = $sortedPlayers->map(function ($player) {
                $latestStat = $player->stats->first(); // Obtener la estadística más reciente
                return [
                    'id' => $player->id,
                    'name' => $player->name,
                    'real_team' => $player->real_team,
                    'foot' => $player->foot,
                    'image_url' => $player->image_url,
                    'position_short_name' => $player->position_short_name,
                    'position_group' => $player->position_group,
                    'age' => $player->age,
                    'shirt_number' => $player->shirt_number,
                    'nationality' => $player->nationality,
                    'latest_stat' => $latestStat // Incluir la estadística más reciente
                ];
            });

            return response()->json([
                'success' => true,
                'players' => $formattedPlayers->values()->all()
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is absent'], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }


    public function getAverage(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $teams = $user->teams()->with('players.stats')->get();
            $players = $teams->pluck('players')->flatten();

            $allStats = $players->pluck('stats')->flatten();

            // Calcular la media de average_rating
            $totalRatings = $allStats->sum('average_rating');
            $countRatings = $allStats->count();

            $averageRating = $countRatings > 0 ? ($totalRatings / $countRatings) * 10 : 0;

            return response()->json([
                'success' => true,
                'average_rating' => round($averageRating, 2)
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is absent'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }


    public function getPointsPlayer(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $teams = $user->teams()->with(['players.latestStat'])->get();
            $players = $teams->pluck('players')->flatten();

            $totalPoints = $players->reduce(function ($sum, $player) {
                $latestStat = $player->latestStat;
                if ($latestStat) {
                    return $sum + $latestStat->points_from_penultimate_match;
                }
                return $sum;
            }, 0);

            return response()->json([
                'success' => true,
                'total_points' => $totalPoints
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is absent'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }



    public function getTotalPointsPlayer(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $teams = $user->teams()->with('players.stats')->get();
            $players = $teams->pluck('players')->flatten();

            $totalPoints = $players->reduce(function ($sum, $player) {
                $playerPoints = $player->stats->sum('points_from_penultimate_match');
                return $sum + $playerPoints;
            }, 0);

            return response()->json([
                'success' => true,
                'total_points' => $totalPoints
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is absent'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }



    public function getUserTeamAndGroup(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $team = $user->teams()->first();
            $group = $user->groups()->first();

            return response()->json([
                'success' => true,
                'team_name' => $team ? $team->name : null,
                'group_name' => $group ? $group->name : null
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is absent'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }


    public function deletePlayer(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($user->id != $request->user_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $team = Team::where('user_id', $request->user_id)
                ->where('group_id', $request->group_id)
                ->first();

            if (!$team) {
                return response()->json(['error' => 'Team not found'], 404);
            }


            // Intentar desvincular el jugador del equipo
            if ($team->players()->detach($request->player_id)) {
                return response()->json(['success' => true, 'message' => 'Player successfully removed from the team'], 200);
            } else {
                return response()->json(['error' => 'Player not found or not linked to the team'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }


    public function getAllPlayersNotMine(Request $request)
    {
        try {
            // Validar que el user_id esté presente en la solicitud
            if (!$request->has('user_id')) {
                return response()->json(['error' => 'User ID is required'], 400);
            }

            $userId = $request->input('user_id');

            // Obtener el usuario por ID
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Obtener los IDs de los equipos del usuario
            $teamIds = $user->teams()->pluck('id')->toArray();

            // Obtener los IDs de los jugadores que están vinculados a los equipos del usuario
            $playerIds = FootballPlayer::whereHas('teams', function ($query) use ($teamIds) {
                $query->whereIn('teams.id', $teamIds);
            })->pluck('id')->toArray();

            // Obtener los jugadores que no están en los IDs obtenidos y cargar su último stat
            $players = FootballPlayer::whereNotIn('id', $playerIds)
                ->with('latestStat')
                ->get();

            return response()->json([
                'success' => true,
                'players' => $players
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }


    public function buyPlayer(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($user->id != $request->user_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $team = Team::where('user_id', $request->user_id)
                ->where('group_id', $request->group_id)
                ->first();

            if (!$team) {
                return response()->json(['error' => 'Team not found'], 404);
            }

            // Comprobar si el jugador ya está vinculado al equipo
            $isAlreadyMember = $team->players()->where('id', $request->player_id)->exists();
            if ($isAlreadyMember) {
                return response()->json(['error' => 'Player already part of the team'], 409);
            }

            // Obtener la posición del jugador
            $player = FootballPlayer::find($request->player_id);
            if (!$player) {
                return response()->json(['error' => 'Player not found'], 404);
            }

            // Vincular el jugador al equipo
            $team->players()->attach($request->player_id);

            return response()->json([
                'success' => true,
                'message' => 'Player successfully added to the team'
            ], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }


}
