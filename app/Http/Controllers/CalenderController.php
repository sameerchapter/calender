<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\ProjectSchedule;
use App\Models\Staff;
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
        $projects = Booking::all();
        $staff = Staff::all();
        $schedules = ProjectSchedule::all();
        $schedules = ProjectSchedule::all();
        $latest_id = ProjectSchedule::latest()->first();
        if ($latest_id) {
            $latest_id = $latest_id->id;
        } else {
            $latest_id = 0;
        }
        return view('calender', compact('foreman', 'projects', 'schedules', 'staff', 'latest_id'));
    }

    public function saveProjectSchedule(Request $request)
    {
        if (!empty($request->get('id'))) {
            $schedule = ProjectSchedule::find($request->get('id'));
        } else {
            $schedule = new ProjectSchedule;
        }
        $booking = Booking::where('address', $request->get('title'))->first();
        if (!empty($booking)) {
            $schedule->event_id = $booking->id;
        }
        $schedule->project_name = $request->get('title');
        $schedule->slot = $request->get('slot');
        $schedule->foreman_id = $request->get('resource');
        $schedule->notes = $request->get('notes');
        $schedule->start = $request->get('start');
        $schedule->staff_id = $request->get('staff');
        $schedule->end = $request->get('end');
        $schedule->save();
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
        $id = ProjectSchedule::find($request->get('id'))->event_id;
        $booking = Booking::find($id);
        $booking_data = $booking->BookingData->sortBy('department_id');
        $html = '<div class="container">
                                <div class="row">
                                <div class="col-md-4">
                                    <div class="info-txt">
                                        <span>BCN</span>
                                        <p id="bcn">'.(!empty($booking->bcn)?$booking->bcn:"NA").'</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-txt">
                                        <span>Address</span>
                                        <p id="booking_address" style="text-decoration:underline;cursor:pointer">'.$booking->bcn.'</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-txt">
                                        <span>Building Company</span>
                                        <p id="building_company">'.($booking_data[0]->department_id == "1" ? $booking_data[0]->contact->title : "NA").'</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-txt">
                                        <span>Floor Type</span>
                                        <p id="floor_type">'.$booking->floor_type.'</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-txt">
                                        <span>Floor Area</span>
                                        <p id="floor_area">'.$booking->floor_area.'</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info-txt">
                                        <span>Notes</span>
                                        <p id="booking_notes">'.$booking->notes.'</p>
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
}
