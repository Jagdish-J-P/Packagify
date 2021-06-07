<?php

namespace Jagdish_J_P\EmailVerifier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailList extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'emailId',
        'status',
        'lastAttempt',
        'verificationDate',
        'fromEmail',
    ];
}
