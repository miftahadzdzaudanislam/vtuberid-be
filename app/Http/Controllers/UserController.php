<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use function Pest\Laravel\delete;

class UserController extends Controller
{
    // Index Users
    public function index() {
        $user = User::all();

        if ($user->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Resource data user not found!'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all users',
            'date' => $user
        ], 200);
    }

    // Delete user
    public function destroy(string $id) {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'resource data user not found!',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ], 200);
    }
}
