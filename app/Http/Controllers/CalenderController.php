<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\Draft;
use App\Models\ProjectSchedule;
use App\Models\Staff;
use App\Models\Notification;
use Twilio\Rest\Client;
use App\Jobs\BookingEmailJob;
use App\Models\DeviceToken;
use App\Models\Leaves;
use Exception;
use DB;
use DateTime;
use DateInterval;
use DatePeriod;
use Illuminate\Support\Facades\Auth;

class CalenderController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {

    $this->middleware('auth:app,staff');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function index()
  {
    $foreman = User::whereHas("roles", function ($q) {
      $q->where("name", "Foreman");
    })->whereNotIn('name', ['NA', 'N/A'])->get();
    $projects = Booking::orderBy('id', 'DESC')->get();
    $drafts = Draft::orderBy('id', 'DESC')->get();
    $staff = Staff::all();
    $schedules = ProjectSchedule::all();
    $latest_id = ProjectSchedule::latest()->first();
    if ($latest_id) {
      $latest_id = $latest_id->id;
    } else {
      $latest_id = 0;
    }
    return view('calender', compact('drafts', 'foreman', 'projects', 'schedules', 'staff', 'latest_id'));
  }
   
  public function checkLeave(Request $request)
  {
      $from_date =  date('Y-m-d', strtotime($request->get('from_date')));
      $to_date =  date('Y-m-d', strtotime($request->get('to_date')));
      $foreman_id = $request->get('foreman_id');
      $staff_ids  =$request->get('staff_id');
     $foreman_leaves = Leaves::where('user_id',$foreman_id)->where('user_type',1)->
    where(function($query) use ($from_date,$to_date){
      $query->where([['from_date','<=',$from_date],['to_date','>=',$to_date]]);
      $query->orwhereBetween('from_date',array($from_date,$to_date));
      $query->orWhereBetween('to_date',array($from_date,$to_date));})->get();

    if(count($foreman_leaves)>0)
    {
      $msg= ucfirst($foreman_leaves[0]->user_name)." is on leave for ". ($from_date==$to_date?$from_date:($foreman_leaves[0]->from_date.' to '.$foreman_leaves[0]->to_date));
       return json_encode(array("success"=>"true","msg"=>$msg));
    }
    if(empty($staff_ids))
    {
      return true;
    }
    $staff_leaves = Leaves::whereIn('user_id',$staff_ids)->where([['user_type',2]])->
    where(function($query) use ($from_date,$to_date){
      $query->where([['from_date','<=',$from_date],['to_date','>=',$to_date]]);
      $query->orwhereBetween('from_date',array($from_date,$to_date));
      $query->orWhereBetween('to_date',array($from_date,$to_date));})->get();
  if(count($staff_leaves)>0)
    {
      $msg= ucfirst($staff_leaves[0]->user_name)." is on leave for ". ($from_date==$to_date?$from_date:($staff_leaves[0]->from_date.' to '.$staff_leaves[0]->to_date));

       return json_encode(array("success"=>"true","msg"=>$msg));
    }
    return true;
  } 

  public function saveProjectSchedule(Request $request)
  {
    $begin = Date('Y-m-d',strtotime($request->get('start')));
    $end = Date('Y-m-d',strtotime($request->get('end')));
    // echo $begin;
    while (strtotime($begin) <= strtotime($end)) { 
     if (!empty($request->get('id'))) {
      $schedule = ProjectSchedule::find($request->get('id'));
    } else {
      $schedule = new ProjectSchedule;
    }
    $booking = Booking::where('address', $request->get('title'))->first();
    if (!empty($booking)) {
      $schedule->event_id = $booking->id;
      $schedule->type = 1;

    } else {
      $draft = Draft::where('address', $request->get('title'))->first();
      if (!empty($draft)) {
        $schedule->event_id = $draft->id;
        $schedule->type = 2;

      }
    }
    $schedule->project_name = $request->get('title');
    $schedule->slot = $request->get('slot');
    $schedule->foreman_id = $request->get('resource');
    $schedule->notes = $request->get('notes');
    $schedule->start =$begin.($request->get('slot')==1?'T07:00':'T12:00');
    $schedule->staff_id = $request->get('staff');
    $schedule->end = $begin.($request->get('slot')==1?'T13:00':'T18:00');;
    $schedule->save();
    $begin = date("Y-m-d", strtotime("+1 day", strtotime($begin)));

  }
  return true;

  }

  public function getStaff(Request $request)
  {
    $staff = User::with('staff')->find($request->get('foreman_id'))->staff->pluck('id');
    return $staff;
  }

  public function deleteProjectSchedule(Request $request)
  {
    $schedule = ProjectSchedule::where('id', $request->get('id'))->delete();;
  }

  public function modalData(Request $request)
  {
    $schedule = ProjectSchedule::find($request->get('id'));
    $id = $schedule->event_id;
    if($schedule->type==1)
    {
    $booking = Booking::find($id);
    $booking_data = $booking->BookingData->sortBy('department_id');
    }else{
    $booking = Draft::find($id);
    $booking_data = $booking->DraftData->sortBy('department_id');
    }
    $html = '<div class="container">
                                <div class="row">
                                <div class="col-md-4">
                                    <div class="info-txt">
                                        <span>BCN</span>
                                        <p id="bcn">' . (!empty($booking->bcn) ? $booking->bcn : "N/A") . '</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-txt">
                                        <span>Address</span>
                                        <p id="booking_address" style="text-decoration:underline;cursor:pointer">' . $booking->address . '</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-txt">
                                        <span>Building Company</span>
                                        <p id="building_company">' . ($booking_data[0]->department_id == "1" ? $booking_data[0]->contact->title : "N/A") . '</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-txt">
                                        <span>Floor Type</span>
                                        <p id="floor_type">' . (!empty($booking->floor_type) ? $booking->floor_type : "N/A") . '</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-txt">
                                        <span>Floor Area</span>
                                        <p id="floor_area">' . (!empty($booking->floor_area) ? $booking->floor_area : "N/A") . '</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info-txt">
                                        <span>Notes</span>
                                        <p id="booking_notes">' . (!empty($booking->notes) ? $booking->notes : "N/A") . '</p>
                                    </div>
                                </div>
                            </div>
                            <div class="status-txt">
                                <span>Status</span>
                                <div class="card-new " style="margin-top: 12px;padding: 15px;">
                                <div class="row">
								<div class="col-md-6" style="border-right: 1px solid #E7E7E7;">
                                ';
    foreach ($booking_data->slice(1, (int)count($booking_data) / 2) as $res) {
      $booking_date = $res->date;
      $title = $res->department->title . ($res->service != '' ? ' (' . $res->service . ')' : '') . ($res->reorder_no != '0' ? ' (Reorder' . $res->reorder_no . ')' : '');
      switch ($res->status) {
        case '0':
          $class = "pending-txt";
          $status = "Pending";
          break;
        case '1':
          $class = "confirmed-txt";
          $status = "Confirmed";
          break;
        case '2':
          $class = "cancelled-txt";
          $status = "On hold";
          break;
        default:
          $class = "";
          $status = "";
      }

      $html .= '<div class="steel  pop-flex ' . $class . '">
										<p>' . $title . '</p>
										<span>' . date('d/m/Y h:i A', strtotime($booking_date)) . ' - ' . $status . '</span>
									</div>
									';
    }
    $html .=        '</div><div class="col-md-6">';
    foreach ($booking_data->slice(((int)count($booking_data) / 2) + 1) as $res) {
      $title = $res->department->title . ($res->service != '' ? ' (' . $res->service . ')' : '') . ($res->reorder_no != '0' ? ' (Reorder' . $res->reorder_no . ')' : '');
      $booking_date = $res->date;
      switch ($res->status) {
        case '0':
          $class = "pending-txt";
          $status = "Pending";
          break;
        case '1':
          $class = "confirmed-txt";
          $status = "Confirmed";
          break;
        case '2':
          $class = "cancelled-txt";
          $status = "On hold";

          break;
        default:
          $class = "";
          $status = "";
      }
      $html .= '			<div class="pods ' . $class . ' pop-flex">
										<p>' . $title . '</p>
										<span>' . date('d/m/Y h:i A', strtotime($booking_date)) . ' - ' . $status . '</span>
									</div>';
    }

    $html .= '</div></div></div></div></div>';

    return $html;
  }

  public function check_cron()
  {
    $bookings = ProjectSchedule::whereDate('start', '=', \Carbon\Carbon::tomorrow())->get();
    // dd($bookings);
    $account_sid = \config('const.twilio_sid');;
    $auth_token = \config('const.twilio_token');
    $twilio_number = "+16209129397";
    $client = new Client($account_sid, $auth_token);

    foreach ($bookings as $booking) {
      $address = $booking->project_name;
      $slot = $booking->slot == 1 ? 'AM' : 'PM';
      $foreman_name = $booking->foreman?->name;
      if (empty($booking->staff_id)) {
        $booking->staff_id = [];
      }
      $staff_names = implode(", ", Staff::whereIn('id', $booking->staff_id)->get()->pluck('name')->toArray());;
      $custom = !empty($staff_names) ? "with $staff_names" : "";
      $msg = "Hi $foreman_name,\nThe location to report tomorrow is $address in the $slot $custom";
      //send mail
      $details['to'] = $booking->foreman->email;
      $details['subject'] = "Boxit Foundation's Reminder";
      $details['body'] = $msg;
      dispatch(new BookingEmailJob($details));
      \Log::info("Mail sent!");

      //send sms
      if (!empty($booking->foreman->contact)) {
        try {
          $output_string = $msg;
          $res = $client->messages->create(
            $booking->foreman->contact,
            array(
              'from' => $twilio_number,
              'body' => $output_string
            )
          );
          \Log::info("Message sent!");
        } catch (Exception $e) {
          $e->getMessage();
        }
      }
      //send app notification 
      $tokens = DeviceToken::where(array('user_id' => $booking->foreman_id, 'model' => 'user'))->get();
      Notification::create(['user_id' => $booking->foreman_id, 'model' => 'user', 'notification' => $msg]);
      foreach ($tokens as $token)
        $this->notify($token, $msg);

      foreach ($booking->staff_id as $staff) {
        $staff_data = Staff::find($staff);
        $staff_name = $staff_data?->name;
        $custom = "under $foreman_name";
        $msg = "Hi $staff_name,\nThe location to report tomorrow is $address in the $slot $custom";

        $details['to'] = $staff_data->email;
        $details['subject'] = "Boxit Foundation's Reminder";
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
            \Log::info("Message sent!");
          } catch (Exception $e) {
            $e->getMessage();
          }
        }
        $tokens = DeviceToken::where(array('user_id' => $staff, 'model' => 'staff'))->get();
        Notification::create(['user_id' => $staff, 'model' => 'staff', 'notification' => $msg]);
        foreach ($tokens as $token)
          $this->notify($token, $msg);
      }
    }
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
