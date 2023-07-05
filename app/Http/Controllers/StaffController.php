<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\User;
use App\Models\ForemanStaff;

class StaffController extends Controller
{

    public function index()
    {
        $staffs = Staff::all();
        return view('staff', compact('staffs'));
    }

    public function edit_staff(Request $request)
    {
        return  Staff::find($request->get('id'));
    }

    public function staffs(Request $request)
    {
        $search = $request->get('search');
        $staffs = Staff::where('name', 'like', $search . '%')->get();
        return view('stafftable', compact('staffs'))->render();
    }

    public function add_staff(Request $request)
    {
        $staff = new Staff();
        $staff->name = $request->name;
        $staff->password = bcrypt($request->password);
        $staff->email = $request->email;
        $staff->save();
        $staff = $staff->fresh();
        $staff->assignRole('Staff');
        return true;
    }



    public function update_staff(Request $request)
    {
        $staff = Staff::find($request->get('id'));
        $staff->name = $request->name;
        if (!empty($request->password)) {
            $staff->password = bcrypt($request->password);
        }
        $staff->email = $request->email;
        $staff->save();

        return  true;
    }

    public function assign_team()
    {
        $foreman = User::with('staff')->whereHas("roles", function ($q) {
            $q->where("name", "Foreman");
        })->whereNotIn('name', ['NA', 'N/A'])->get();
        $staff = Staff::all();
        return view('assignteam', compact('foreman', 'staff'));
    }

    public function save_team(Request $request)
    {
        $foreman_id = $request->get('foreman_id');
        $staff_ids = $request->get('staff_id');
        ForemanStaff::where(array('foreman_id' => $foreman_id))->delete();
        foreach ($staff_ids as $staff_id) {
            ForemanStaff::create(array('foreman_id' => $foreman_id, 'staff_id' => $staff_id));
        }
        return true;
    }

    public function send_notification()
    {
        $tokens = DeviceToken::all();
        foreach ($tokens as $token) {
            $payload = array(
                'to' => $token->token,
                'sound' => 'default',
                'body' => 'hello',
            );

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://exp.host/--/api/v2/push/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Accept-Encoding: gzip, deflate",
                    "Content-Type: application/json",
                    "cache-control: no-cache",
                    "host: exp.host"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                echo $response;
            }
        }
    }

    public function notification()
    {
        return view('notification');
    }
}
