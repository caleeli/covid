<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class)->create([
            'name' => 'gest',
            'email' => 'gest@covid.coredump.com.bo',
            'password' => Hash::make('gest'),
        ]);
    }
}
