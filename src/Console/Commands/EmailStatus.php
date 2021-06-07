<?php

namespace Jagdish_J_P\EmailVerifier\Console\Commands;

use Illuminate\Console\Command;
use Jagdish_J_P\EmailVerifier\Models\EmailList;

/**
 * Command to schedule email verification
 * @author Jagdish-J-P <jagdish1230@gmail.com>
 * @url https://github.com/jagdish-j-p
 */
class EmailStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:status {email? : from email id to filter list}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to view status';

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

        $orderDir = $this->choice('Do you want latest record or oldest?',['latest','oldest'],0);

        $limit = filter_var($this->ask('How many record do you want to fetch?',100),FILTER_VALIDATE_INT);

        $emailList= EmailList::where('emailId', 'like', $email)
        ->$orderDir()
        ->limit($limit)
        ->get(['emailId','status','created_at','verificationDate'])
        ->toArray();

        if(count($emailList))
        $this->table(['emailId','status','date_created','verificationDate'],$emailList);
        else
        $this->error("No record found");
    }

    private function getEmail()
    {
        $email = $this->argument('email') ?? '%';

        if ($email!='%' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid From Email '$email'");
        }

        return $email;
    }
}
