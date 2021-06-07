<?php

namespace Jagdish_J_P\EmailVerifier\Console\Commands;

use Illuminate\Console\Command;
use Jagdish_J_P\EmailVerifier\Models\EmailList;

/**
 * Command to schedule email verification
 * @author Jagdish-J-P <jagdish1230@gmail.com>
 * @url https://github.com/jagdish-j-p
 */
class EmailSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:schedule {email? : email id to verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to add email in schedule';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->getEmail();

        $emailList= new EmailList;
        $emailList->emailId=$email;
        $emailList->save();
        if($emailList->id)
        $this->info("Email scheduled :)");
        else
        $this->error("Email Failed to schedule :(");
    }

    private function getEmail()
    {
        $email = $this->argument('email') ?? $this->ask('Enter email id to schedule:');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid From Email '$email'");
        }

        return $email;
    }
}
