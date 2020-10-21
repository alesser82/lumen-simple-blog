<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RefreshToken;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class AuthController extends Controller
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
    public function login(User $user) {

        $input = $this->request->all();

        $validator = Validator::make($input, [
            'email' => 'required|email',
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

        // Find the user by email
        $user = User::where('email', $input['email'])
                    ->limit(1)
                    ->first();

        if (!$user) {
            // You wil probably have some sort of helpers or whatever
            // to make sure that you have the same response format for
            // differents kind of responses. But let's return the 
            // below respose for now.
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Email does not exist.'
            ], 400);
        }
        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
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
            ], 200);
        }
        // Bad Request response
        return response()->json([
            'status' => 'ERROR',
            'message' => 'Email or password is wrong.'
        ], 400);
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

    public function refreshToken()
    {
        $input = $this->request->all();

        $validator = Validator::make($input, [
            'token' => 'required'
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

        $refreshToken = RefreshToken::where('token', '=', $input['token'])
                    ->limit(1)
                    ->first();

        if (empty($refreshToken)) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Not found.'
            ], 404);
        }

        $user = User::find($refreshToken->user_id);

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

        return response()->json([
            'status' => 'OK',
            'data' => $data
        ]);
    } 

    public function logout()
    {
        $data = RefreshToken::where(
            'user_id', '=', $this->request->auth->id
        )->delete();

        return response()->json([
            'status' => 'OK'
        ], 200);
    }
}
