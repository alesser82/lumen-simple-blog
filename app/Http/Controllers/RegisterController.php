<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RefreshToken;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Ramsey\Uuid\Uuid;

class RegisterController extends Controller
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

    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     * 
     * @param  \App\User   $user 
     * @return mixed
     */
    public function index(User $user) 
    {
        $input = $this->request->all();

        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required',
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

        $input['id'] = Uuid::uuid4()->toString();
        $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);

        $dateNow = date('Y-m-d H:i:s');

        try {
            $user = User::create($input);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Server is not ready.'
            ], 503);
        }

        $user->created_at = $dateNow;
        $user->updated_at = $dateNow;

        $authPayload = $this->jwt($user);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $authPayload['refresh_token']
        ]);

        return response()->json([
            'status' => 'OK',
            'data' => [
                'authorization' => $authPayload,
                'user' => $user
            ]
        ], 201);
    }

    /**
     * Create a new token.
     * 
     * @param  \App\User   $user
     * @return string
     */
    protected function jwt(User $user) {
        $tokenPayload = [
            'iss' => env('APP_NAME'), // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 60 * 5, // Expiration time,
            'claims' => $user
        ];

        $token = JWT::encode($tokenPayload, env('JWT_SECRET'));

        $data = [
            'token' => $token,
            'expired_at' => date('Y-m-d H:i:s', $tokenPayload['exp'])
        ];

        $refreshPayload = $tokenPayload;
        $refreshPayload['exp'] = time() + 60 * 240;

        $refreshToken = JWT::encode($tokenPayload, env('JWT_REFRESH_SECRET'));

        $data['refresh_token'] = $refreshToken;
        
        // As you can see we are passing `JWT_SECRET` as the second parameter that will 
        // be used to decode the token in the future.
        return $data;
    }

}
