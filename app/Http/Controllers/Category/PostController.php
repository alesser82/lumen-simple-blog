<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;

class PostController extends Controller
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

    public function index($id)
    {
        $data = Post::where('category_id', '=', $id)
                    ->get();
        
        return !$data->isEmpty() ? response()->json([
            'status' => 'OK',
            'data' => $data
        ]) : response()->json([
            'status' => 'ERROR',
            'message' => 'Not found.'
        ], 404);
    }
}
