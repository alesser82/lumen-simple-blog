<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;

class UserController extends Controller
{
    private $request;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function profile()
    {
        return response()->json([
            'status' => 'OK',
            'data' => User::find($this->request->auth->id)
        ]);
    }

    public function show($id)
    {
        $data = User::find($id);

        return !empty($data) ? response()->json([
            'status' => 'OK',
            'data' => $data
        ]) : response()->json([
            'status' => 'ERROR',
            'message' => 'Not Found.'
        ], 404);
    }

    public function posts($id)
    {
        $posts = Post::where('user_id', '=', $id)
                        ->get();

        return !$posts->isEmpty() ? response()->json([
            'status' => 'OK',
            'data' => $posts
        ]) : response()->json([
            'status' => 'Error',
            'message' => 'Not found.'
        ], 404);
    }
}
