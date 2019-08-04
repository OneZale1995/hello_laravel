<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = factory(User::class)->times(1000)->make();
        User::insert($user->makeVisible(['password', 'remember_token'])->toArray());

        $user = User::find(1);
        $user->name = 'OneZale';
        $user->email = '442342634@qq.com';
        $user->is_admin = true;
        $user->save();
    }
}
