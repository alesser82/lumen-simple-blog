<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ProfileController extends Controller
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
            'data' => User::find($this->request->auth->id)
        ]);
    }

    public function update()
    {
        $data = User::find($this->request->auth->id);
        
        if (empty($data)) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Not Found.'
            ], 404);
        }
        
        $input = $this->request->all();

        $validator = Validator::make($input, [
            'name' => [
                'required', 'string', 'max:255'
            ],
            'email' => [
                'required', 'email', 'max:255', 'unique:users,email'
            ],
            'password' => [
                'nullable', 'string'
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

        if (!empty($input['password'])) {
            $input['password'] = password_hash(
                $input['password'], PASSWORD_BCRYPT
            );
        }

        $input['id'] = $this->request->auth->id;

        $updated = $data->update($input);
        
        return $updated ? response()->json([
            'status' => 'OK'
        ], 201) : response()->json([
            'status' => 'ERROR',
            'message' => 'Server is not ready.'
        ], 503);
    }

    public function destroy()
    {
        $data = User::find($this->request->auth->id);
        
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
