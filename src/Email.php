<?php

namespace Jagdish_J_P\EmailVerifier;

use Jagdish_J_P\EmailVerifier\Models\EmailList;
use Jagdish_J_P\EmailVerifier\Helpers\EmailHelper;

/**
 * Email Verifier is a package to verify email is real or fake.
 * @author Jagdish-J-P <jagdish1230@gmail.com>
 * @url https://github.com/jagdish-j-p
 */
class Email {
    /**
     * Function to schedule emails for verification
     * @param String $email email to verify
     * @return boolean true if scheduled
     */
    public function schedule(String $email)
    {
        $emailList= new EmailList;
        $emailList->emailId=$email;
        $emailList->save();
        if($emailList->id)
        return true;
        else
        return false;
    }
    /**
     * Function to verify email
     * @param String $email email to verify
     * @return int 1 for success, 0 for failed
     */
    public function verify(String $email)
    {
        $emailHelper = new EmailHelper;
        return $emailHelper->verify($email);
    }
}
