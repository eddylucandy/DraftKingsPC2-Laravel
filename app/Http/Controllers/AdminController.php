<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    public function getAllUsers()
    {
        $users = User::with(['userProfile', 'groups', 'teams'])->get();

        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'first_name' => $user->userProfile->first_name,
                'group_name' => optional($user->groups->first())->name,
                'team_name' => optional($user->teams->first())->name,
            ];
        });

        return response()->json($formattedUsers, 200);
    }


    public function getAllGroups()
    {
        $groups = Group::all();

        return response()->json($groups, 200);
    }




    public function updateUserData(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*.id' => 'required|exists:users,id',
            'users.*.role' => 'required|string|in:user,admin',
            'users.*.group_name' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->input('users') as $userData) {
                $user = User::findOrFail($userData['id']);
                $user->role = $userData['role'];
                $user->save();

                if (isset($userData['group_name'])) {
                    $group = Group::firstOrCreate(['name' => $userData['group_name']]);
                    $user->groups()->syncWithoutDetaching([$group->id]);
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Datos actualizados correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Actualización de datos fallida', 'error' => $e->getMessage()], 500);
        }
    }

}
