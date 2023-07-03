<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Staff;
use DB;
use App\Models\ProjectSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{


    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (Auth::guard('app')->attempt($request->only(['email', 'password']))) {

                $user = User::where('email', $request->email)->first();
                $user->model = 'user';
            } elseif (Auth::guard('staff')->attempt($request->only(['email', 'password']))) {
                $user = Staff::where('email', $request->email)->first();
                $user->model = 'staff';
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }
            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function eventData(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'id' => 'required',
                    'model' => 'required',
                    'date' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            if ($request->get('model') == 'user') {
                $data =  ProjectSchedule::with('foreman')->where('foreman_id', $request->get('id'))->whereDate('start', date('Y-m-d', strtotime($request->get('date'))))->orderBy('slot', 'ASC')->get();
            } elseif ($request->get('model') == 'staff') {
                $data =  ProjectSchedule::with('foreman')->whereDate('start', date('Y-m-d', strtotime($request->get('date'))))->whereJsonContains('staff_id', $request->get('id'))->orderBy('slot', 'ASC')->get();
            } else {
                $data = [];
            }

            return response()->json([
                'status' => true,
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
