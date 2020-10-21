<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;

class CategoryController extends Controller
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

    public function index()
    {
        return response()->json([
            'status' => 'OK',
            'data' => Category::get()
        ]);
    }

    public function show($id)
    {
        $data = Category::find($id);

        return !empty($data) ? response()->json([
            'status' => 'OK',
            'data' => $data
        ]) : response()->json([
            'status' => 'ERROR',
            'message' => 'Not found.'
        ], 404);
    }

    public function store()
    {
        $input = $this->request->all();

        $validator = Validator::make($input, [
            'id' => [
                'required', 'uuid'
            ],
            'name' => [
                'required', 'string', 'max:200',
                function ($attribute, $value, $fail) {
                    $slug = Str::slug($value);
                    $data = Category::where('slug', '=', $slug)
                                ->limit(1)
                                ->first();

                    if (!empty($data)) {
                        $fail($attribute.' has been used.');
                    }
                },
            ]
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

        $category = Category::find($input['id']);

        if (!empty($category)) {
            return response()->json([
                'status' => 'OK'
            ], 201);
        }

        $input['slug'] = Str::slug($input['name']);
        $input['user_id'] = $this->request->auth->id;

        try {
            Category::create($input);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Server is not ready.'
            ], 503);
        }

        return response()->json([
            'status' => 'OK',
        ]);
    }

    public function update($id)
    {
        $data = Category::find($id);

        if (empty($data)) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Not found.'
            ], 404);
        }

        $input = $this->request->all();

        $validator = Validator::make($input, [
            'name' => [
                'required', 'string', 'max:200',
                function ($attribute, $value, $fail) use ($id) {
                    $slug = Str::slug($value);
                    $data = Category::where('slug', '=', $slug)
                                ->where('id', $id)
                                ->limit(1)
                                ->first();

                    if (!empty($data)) {
                        $fail($attribute.' has been used.');
                    }
                },
            ]
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

        $input['slug'] = Str::slug($input['name']);

        return $data->update($input) ? response()->json([
            'status' => 'OK',
        ]) : response()->json([
            'status' => 'ERROR',
            'message' => 'Server is not ready.'
        ], 503);
    }

    public function destroy($id)
    {
        $data = Category::find($id);

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
