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
        })->whereNotIn('name',['NA','N/A'])->get();
        $projects=Booking::all();
        $staff=Staff::all();
        $schedules=ProjectSchedule::all();
        return view('calender',compact('foreman','projects','schedules','staff'));
    }

    public function saveProjectSchedule(Request $request)
    {
        $result=ProjectSchedule::where('event_id',$request->get('id'))->get();
        if(count($result)>0){          
        $schedule=ProjectSchedule::find($result[0]->id);
       }else{
        $schedule=new ProjectSchedule;
       }
       $schedule->event_id=$request->get('id');
       $schedule->project_name=$request->get('title');
       $schedule->slot=$request->get('slot');
       $schedule->foreman_id=$request->get('resource');
       $schedule->notes=$request->get('notes');
       $schedule->start=$request->get('start');
       $schedule->staff_id=$request->get('staff_id'); 
       $schedule->end=$request->get('end');
       $schedule->save();
       return true;
    }
    
    public function getStaff(Request $request)
    {
       $staff= User::with('staff')->find($request->get('foreman_id'))->staff->pluck('id'); 
       return $staff;
    }

    public function deleteProjectSchedule(Request $request)
    {
        $schedule=ProjectSchedule::where('event_id',$request->get('id'))->delete();;

    }
}
