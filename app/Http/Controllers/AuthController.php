<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class AuthController extends Controller
{
    //
    public function signup( Request $request){
      $validated =  $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password'
        ]);
        $username = strstr($request->email,'@',true);


        $user = User::create([
            'name' => $username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'c_password' => bcrypt($request->c_password),
        ]);

        return response()->json(['success' => true],200);

    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if(Auth::attempt($credentials)){
           $user = Auth::user();
            $token = $user->createToken('API Token')->accessToken;
            $response = [
                'success' => true,
                'accessToken' => $token,
                'userName' => $user->name,
            ];
           return response()->json($response,200);
        }else{
            $response = [
                'success' => false,
            ];
            return response()->json($response,401);
        }

    }

    public function logout(Request $request){
        $token = $request->user()->token();
        $token->revoke();
        return response(['message' => 'You have been successfully logged out.'], 200);
    }

}
