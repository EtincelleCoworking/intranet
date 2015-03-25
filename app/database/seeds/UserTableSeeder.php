<?php
/**
* User table seeder
*/
class UserTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();

        User::create(array('email' => 'admin@mydomain.fr', 'password' => Hash::make('123456'), 'firstname' => 'Admin', 'lastname' => 'admin', 'bio_short' => '', 'bio_long' => '', 'avatar' => '', 'is_member' => 1, 'twitter' => '', 'website' => '', 'role' => 'superadmin'));
    }
}