<?php

use App\Events\SendEmailToGM;
use Illuminate\Database\Seeder;

class SendMail extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        new SendEmailToGM();
    }
}
