<?php
//2|hzOzxqDMrSJzeJ7TekUOoiIyfmQxq60qFzfT6Wrl
//6|WMkXkHSmI1zFHmkXGRiQzrcIOxLMORCo8Onn5WP3
//9|Lq4f4IazGeiO30g7zmkCUZLE8ETacFkYWxXn0vkY
//10|iVlnkdx9sHrffbSUKdkoLFyOYB7lDjGXtnmpH7aC
namespace App\Http\Controllers;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                //Set data
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = Hash::make($request->password); //encrypt password
                $user->save();
                DB::commit();
                return $this->getResponse201('user account', 'created', $user);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (!$validator->fails()) {
            $user = User::where('email', '=', $request->email)->first();
            if (isset($user->id)) {
                if (Hash::check($request->password, $user->password)) {
                    //Create token
                    $token = $user->createToken('auth_token')->plainTextToken;
                    return response()->json([
                        'message' => "Successful authentication",
                        'access_token' => $token,
                    ], 200);
                } else { //Invalid credentials
                    return $this->getResponse401();
                }
            } else { //User not found
                return $this->getResponse401();
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function userProfile()
    {
        return $this->getResponse200(auth()->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); //Revoke all tokens
        return response()->json([
            'message' => "Logout successful"
        ], 200);
    }


    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                //Set data
                $user = auth()->user();
                $user = User::find($user->id);
                $user->password = Hash::make($request->password); //encrypt password
                $user->save();
                $request->user()->tokens()->delete(); //Revoke current token
                DB::commit();
                return $this->getResponse201('Password', 'Update', $user);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }

    }

    public function test(Request $request){
        return $this->getResponse200([]);
    }

}
