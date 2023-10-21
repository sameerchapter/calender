<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Leaves;
use App\Models\User;
use Exception;


class LeavesController extends Controller
{

  public function index()
  {
    $staffs = Staff::all();
    $foremans = User::whereHas("roles", function ($q) {
      $q->where("name", "Foreman");
    })->whereNotIn('name', ['NA', 'N/A'])->get();
    return view('leaves', compact('staffs','foremans'));
  }

  public function search(Request $request)
  {
    $search = $request->get('search');
    $staffs = Staff::where('name', 'like', $search . '%')->get();
    $foremans = User::where('name', 'like', $search . '%')->whereHas("roles", function ($q) {
      $q->where("name", "Foreman");
    })->whereNotIn('name', ['NA', 'N/A'])->get();
    return view('leavestable', compact('staffs','foremans'))->render();
  }

  public function get_leaves(Request $request){
    $user_id=$request->get('user_id');
    $user_type=$request->get('user_type');
    $data=Leaves::where([['user_id',$user_id],['user_type',$user_type]])->get();
    return $data;
}

public function save_leaves(Request $request){
        
  $user_id=$request->get('user_id');
  $user_type=$request->get('user_type');
  $user_name=$request->get('user_name');
    echo $user_type."<br>";
    echo $user_name;
  Leaves::where('user_id',$user_id)->where('user_type',$user_type)->delete();
  $from_dates=$request->get('from_dates');
  $to_dates=$request->get('to_dates');
  $i=0;
  foreach($from_dates as $date)
  {
      Leaves::create(array('user_id'=>$user_id,'user_type'=>$user_type,'user_name'=>$user_name,'to_date'=>date("Y-m-d", strtotime($to_dates[$i])),'from_date'=>date("Y-m-d", strtotime($date))));
      $i++;
  }
  return true;
}

}
