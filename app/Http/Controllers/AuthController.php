<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function login(Request $request)
    {

        $validator = Validator::make($request->only('username', 'password'), [
            'username' => 'required|string|email|max:150',
            'password' => 'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('username', 'password');

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        try {
            $token = JWTAuth::attempt($credentials);

            if (!$token) {
                return response()->json(['error' => 'Password incorrect'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Couldn`t create token'], 500);
        }

        $userProfile = $user->userProfile;
        $group = $user->groups()->first();

        $userData = [
            'user_id' => $user->id,
            'role' => $user->role,
            'first_name' => $userProfile->first_name,
            'email' => $user->username,
            'created_at' => $user->created_at->toDateTimeString(),
            'group_id' => $group ? $group->id : null
        ];

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $userData
        ], 200);
    }


    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthday' => 'required|date',
            'team_name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $team = Team::where('name', $request->input('team_name'))->first();

            if ($team) {
                return response()->json(['success' => false, 'message' => 'Team already exists'], 400);
            }

            $user = User::create([
                'username' => $request->input('username'),
                'password' => Hash::make($request->input('password')),
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'birthday' => $request->input('birthday'),
            ]);

            Team::create([
                'name' => $request->input('team_name'),
                'user_id' => $user->id,
                'group_id' => null
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'User registered successfully'], 200);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Registration failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }



    public function refreshToken(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }

        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $newToken = $token->refresh();
            return response()->json(['token' => $newToken]);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token error: ' . $e->getMessage()], 500);
        }
    }


    public function getUserDetails(Request $request)
    {

        try {
            $user = JWTAuth::parseToken()->authenticate();

            // Comprobar si el user_id proporcionado coincide con el del usuario autenticado
            if ($user->id != $request->user_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            // Obtener el perfil del usuario
            $userProfile = $user->userProfile()->first();

            // Comprobar si existe un perfil
            if (!$userProfile) {
                return response()->json(['error' => 'User profile not found'], 404);
            }

            // Preparar los datos para enviar
            $userData = [
                'first_name' => $userProfile->first_name,
                'last_name' => $userProfile->last_name,
                'email' => $user->username,
                'birthday' => $userProfile->birthday,
            ];

            return response()->json(['user' => $userData]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }


    public function test(Request $request)
    {
        return response()->json(["message" => "API test PC Camilo"]);
    }


    public function logout(Request $request)
    {
        try {
            $token = $request->header('Authorization');

            JWTAuth::setToken($token)->invalidate();

            return response()->json(['success' => true, 'message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'message' => 'Failed to logout, please try again', 'error' => $e->getMessage()], 500);
        }
    }


    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:5',
            'new_password' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully'], 200);
    }

}
