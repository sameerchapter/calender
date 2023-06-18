<?php

namespace Database\Seeders;


use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\Staff;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::create(['guard_name' => 'staff', 'name' => 'Staff']);
        
       $staff=Staff::create([
           'name' => 'Aaron',
           'email' => 'aaron@boxit.com',
           'password' => bcrypt('Boxit@123'),
       ]);
       $staff = $staff->fresh();
       $staff->assignRole('Staff');

       $staff=Staff::create([
        'name' => 'Ethan',
        'email' => 'ethan@boxit.com',
        'password' => bcrypt('Boxit@123'),
    ]);
    $staff = $staff->fresh();
    $staff->assignRole('Staff');

   

    $staff=Staff::create([
        'name' => 'Solomon',
        'email' => 'solomon@boxit.com',
        'password' => bcrypt('Boxit@123'),
    ]);
    $staff = $staff->fresh();
    $staff->assignRole('Staff');

    $staff=Staff::create([
        'name' => 'Adrian',
        'email' => 'adrian@boxit.com',
        'password' => bcrypt('Boxit@123'),
    ]);
    $staff = $staff->fresh();
    $staff->assignRole('Staff');

    $staff=Staff::create([
        'name' => 'Stefan',
        'email' => 'stefan@boxit.com',
        'password' => bcrypt('Boxit@123'),
    ]);
    $staff = $staff->fresh();
    $staff->assignRole('Staff');

    }
}
