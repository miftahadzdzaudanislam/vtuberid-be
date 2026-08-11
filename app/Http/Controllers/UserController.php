<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

use function Pest\Laravel\delete;

class UserController extends Controller
{
    // Index Users
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50);

        $users = QueryBuilder::for(User::class)
            ->allowedFilters(
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama', 'like', "%{$value}%");
                    $query->orWhere('email', 'like', "%{$value}%");
                }),
                AllowedFilter::exact('role'),
            )->orderBy('created_at', 'asc')
            ->paginate($limit)
            ->appends($request->query());

        if ($users->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No data user found!'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all users',
            'data' => $users->items(),
            'paggination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem()
            ]
        ], 200);
    }

    // Delete user
    public function destroy(string $id)
    {
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
