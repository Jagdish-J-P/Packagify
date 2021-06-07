<?php

namespace Jagdish_J_P\EmailVerifier\Console\Commands;

use Jagdish_J_P\EmailVerifier\Helpers\EmailHelper;
use Jagdish_J_P\EmailVerifier\Models\EmailList;
use Illuminate\Console\Command;

class EmailVerifier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:verify {email? : From Email Id }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to verify email is real or fake.';

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
        $result = [];
        $fromEmail = $this->getEmail();
        $now = now();
        EmailList::where('status', -1)->where(function ($q) {
            $q->where('lastAttempt', null)
                ->orWhere('lastAttempt', '<', now()->subSeconds(40));
        })->take(config('emailverifier.emailLimit'))->update(['lastAttempt' => $now]);

        $emailList = EmailList::get()->where('lastAttempt', $now)->where('status', -1);

        foreach ($emailList as $row) {
            $email = $row->emailId;

            $mail = new EmailHelper;
            $mail->setEmailFrom($fromEmail);
            $status = $mail->verify($email);
            if($status==1)
            $this->info("$email is valid :)");
            else
            $this->error("$email is not valid:(");

            EmailList::where('id', $row->id)->update(['status' => $status, 'verificationDate' => now()]);
        }

        return 1;
    }
    private function getEmail()
    {
        $email = $this->argument('email') ?? $this->ask('Enter your email id:');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid From Email '$email'");
        }

        return $email;
    }
}
