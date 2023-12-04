<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Booking;
use App\Models\DeviceToken;
use App\Models\Notification;
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
                if(User::find($request->get('id'))->hasRole('Admin'))
                {
                    $data =  ProjectSchedule::with('foreman')->where('foreman_id', $request->get('id'))->whereDate('start', date('Y-m-d', strtotime($request->get('date'))))->orderBy('slot', 'ASC')->get();

                }else{
                    $data =  ProjectSchedule::with('foreman')->whereDate('start', date('Y-m-d', strtotime($request->get('date'))))->orderBy('slot', 'ASC')->get();

                }
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

    public function notificationData(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required',
                    'model' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            
            $data=Notification::where(['user_id'=>$request->get('user_id'),'model'=>$request->get('model')])->get();

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

    public function bookingData(Request $request)
    {
        // try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'id' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $schedule = ProjectSchedule::with('foreman')->find($request->get('id'));
            $id = $schedule->event_id;
            $booking = Booking::find($id);
            $booking_data = $booking->BookingData->sortBy('department_id');
            $data['bcn'] = !empty($booking->bcn) ? $booking->bcn : "N/A";
            $data['address'] = $booking->address;
            $data['building_company'] = $booking_data[0]->department_id == "1" ? $booking_data[0]->contact->title : "NA";
            $data['floor_type'] = !empty($booking->floor_type) ? $booking->floor_type : "N/A";
            $data['floor_area'] = !empty($booking->floor_area) ? $booking->floor_area : "N/A";
            $data['booking_notes'] = !empty($booking->notes) ? $booking->notes : "N/A";
            $data['notes'] = !empty($schedule->notes) ? $schedule->notes : "N/A";
            $data['staff_assigned'] = implode(" • ", Staff::whereIn('id', $schedule->staff_id)->get()->pluck('name')->toArray());
            $data['foreman_assigned'] = $schedule->foreman->name;

            return response()->json([
                'status' => true,
                'data' => $data
            ], 200, [], JSON_UNESCAPED_UNICODE);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => $th->getMessage()
        //     ], 500);
        // }
    }

    public function saveToken(Request $request)
    {
        $matchThese = ['user_id' => $request->get('id'), 'model' => $request->get('model')];
        DeviceToken::updateOrCreate($matchThese, ['token' => $request->get('token')]);
        return true;
    }
}
