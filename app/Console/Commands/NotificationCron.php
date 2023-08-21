<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectSchedule;
use App\Models\Staff;
use App\Models\Notification;
use Twilio\Rest\Client;
use App\Jobs\BookingEmailJob;
use App\Models\DeviceToken;
use Exception;

class NotificationCron extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'notification:cron';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
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
      if(empty($booking->staff_id))
      {
        $booking->staff_id=[];
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
      Notification::create(['user_id' => $booking->foreman_id, 'model' => 'user','notification'=>$msg]);
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
            echo "msgsent"."<br>";
          } catch (Exception $e) {
            $e->getMessage();
          }
        }
        $tokens = DeviceToken::where(array('user_id' => $staff, 'model' => 'staff'))->get();
        Notification::create(['user_id' => $staff, 'model' => 'staff','notification'=>$msg]);
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
