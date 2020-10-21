<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

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

    public function index(Post $post)
    {
        return response()->json([
            'status' => 'OK',
            'data' => Post::get()
        ], 200);
    }

    public function store()
    {
        $input = $this->request->all();

        $validator = Validator::make($input, [
            'id' => [
                'required', 'string', 'uuid'
            ],
            'title' => [
                'required', 'string', 'max:255',
                function ($attribute, $value, $fail) {
                    $slug = Str::slug($value);
                    $data = Post::where('slug', '=', $slug)
                                ->limit(1)
                                ->first();

                    if (!empty($data)) {
                        $fail($attribute.' has been used.');
                    }
                },
            ],
            'summary' => [
                'required', 'string', 'max:60000'
            ],
            'content' => 'required|max:60000',
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->get('*');
            
            foreach ($messages as $key => $value) {
                $messages[$key] = \implode(' ', $value);
            }
            
            return response()->json([
                'status' => 'ERROR',
                'message' => $messages
            ], 400);
        }

        $post = Post::find($input['id']);

        if (!empty($post)) {
            return response()->json([
                'status' => 'OK'
            ], 201);
        }

        $input['slug'] = Str::slug($input['title']);
        $input['user_id'] = $this->request->auth->id;

        try {
            Post::create($input);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Server not ready.'
            ], 500);
        }

        return response()->json([
            'status' => 'OK'
        ], 201);
    }

    public function show($id)
    {
        $data = Post::find($id);

        return !empty($data) ? response()->json([
            'status' => 'OK',
            'data' => $data
        ]) : response()->json([
            'status' => 'ERROR',
            'message' => 'Not Found.'
        ], 404);
    }

    public function update($id)
    {
        $data = Post::find($id);

        if (empty($data)) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Not Found.'
            ], 404);
        }

        $input = $this->request->all();

        $validator = Validator::make($input, [
            'title' => [
                'required', 'string', 'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    $slug = Str::slug($value);
                    $data = Post::where('slug', '=', $slug)
                                ->where('id', '!=', $id)
                                ->limit(1)
                                ->first();

                    if (!empty($data)) {
                        $fail($attribute.' has been used.');
                    }
                },
            ],
            'summary' => [
                'required', 'string', 'max:60000'
            ],
            'content' => 'required|max:60000',
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->get('*');
            
            foreach ($messages as $key => $value) {
                $messages[$key] = \implode(' ', $value);
            }
            
            return response()->json([
                'status' => 'ERROR',
                'message' => $messages
            ], 400);
        }

        $input['id'] = $id;

        $input['slug'] = Str::slug($input['title']);
        
        return $data->update($input) ? response()->json([
            'status' => 'OK'
        ], 201) : response()->json([
            'status' => 'ERROR',
            'message' => 'Server is not ready.'
        ], 503);
    }

    public function destroy($id)
    {
        $data = Post::find($id);

        if (empty($data)) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Not Found.'
            ], 404);
        }

        return $data->delete() ? response()->json([
            'status' => 'OK'
        ], 200) : response()->json([
            'status' => 'ERROR',
            'message' => 'Server is not ready.'
        ], 503);
    }
}
