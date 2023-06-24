<?php

namespace App\Http\Controllers;

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
        $foreman_id=$request->get('foreman_id');
        $staff_ids=$request->get('staff_id');
        ForemanStaff::where(array('foreman_id'=>$foreman_id))->delete();
        foreach($staff_ids as $staff_id)
        {
            ForemanStaff::create(array('foreman_id'=>$foreman_id,'staff_id'=>$staff_id));

        }
        return true;
        
    }
}
