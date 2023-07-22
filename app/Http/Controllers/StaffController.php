<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Notification;
use App\Models\User;
use App\Models\ForemanStaff;
use Twilio\Rest\Client;
use App\Jobs\BookingEmailJob;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Exception;


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
    $staff->contact = $request->contact;
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
    $staff->contact = $request->contact;
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

  public function send_notification(Request $request)
  {
    $tokens = DeviceToken::all();
    foreach ($tokens as $token) {
      Notification::create(['user_id' => $token->user_id, 'model' => $token->model, 'notification' => $request->get('notification')]);
      $payload = array(
        'to' => $token->token,
        'sound' => 'default',
        'body' => $request->get('notification'),
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
    Session::flash('message', 'Notification sent successfuly');
    Session::flash('alert-class', 'alert-success');
    return Redirect::back();
  }

  public function notification()
  {
    return view('notification');
  }

  public function team_notification()
  {
    $foreman = User::with('staff')->whereHas("roles", function ($q) {
      $q->where("name", "Foreman");
    })->whereNotIn('name', ['NA', 'N/A'])->get();
    return view('team-notification', compact('foreman'));
  }

  public function send_team_notification(Request $request)
  {
    $msg = $request->get('notification');
    $foreman_id = $request->get('foreman_id');
    $account_sid = \config('const.twilio_sid');;
    $auth_token = \config('const.twilio_token');
    $twilio_number = "+16209129397";
    $client = new Client($account_sid, $auth_token);

    foreach ($foreman_id as $f) {
      $foreman = User::find($f);
      //send mail
      $details['to'] = $foreman->email;
      $details['subject'] = "Boxit Foundation's Notification";
      $details['body'] = $msg;
      dispatch(new BookingEmailJob($details));
      //send sms
      if (!empty($foreman->contact)) {
        try {
          $output_string = $msg;
          $res = $client->messages->create(
            $foreman->contact,
            array(
              'from' => $twilio_number,
              'body' => $output_string
            )
          );
        } catch (Exception $e) {
          $e->getMessage();
        }
      }
      //send app notification 
      $tokens = DeviceToken::where(array('user_id' => $foreman->id, 'model' => 'user'))->get();
      Notification::create(['user_id' => $foreman->id, 'model' => 'user', 'notification' => $msg]);
      foreach ($tokens as $token)
        $this->notify($token, $msg);

      foreach ($foreman->staff as $staff_data) {

        $details['to'] = $staff_data->email;
        $details['subject'] = "Boxit Foundation's Notification";
        $details['body'] = $msg;

        if (!empty($staff_data->contact)) {
          try {
            $output_string = $msg;
            $res = $client->messages->create(
              $staff_data->contact,
              array(
                'from' => $twilio_number,
                'body' => $output_string
              )
            );
          } catch (Exception $e) {
            $e->getMessage();
          }
        }
        $tokens = DeviceToken::where(array('user_id' => $staff_data->id, 'model' => 'staff'))->get();
        Notification::create(['user_id' => $staff_data->id, 'model' => 'staff', 'notification' => $msg]);
        foreach ($tokens as $token)
          $this->notify($token, $msg);
      }
    }
    Session::flash('message', 'Notification sent successfuly');
    Session::flash('alert-class', 'alert-success');
    return Redirect::back();
  }

  public function notify($token, $msg)
  {
    $payload = array(
      'to' => $token,
      'sound' => 'default',
      'body' => $msg
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
