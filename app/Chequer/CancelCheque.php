<?php

namespace App\Chequer;
use App\Account;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class CancelCheque extends Model
{
    use LogsActivity;

    protected static $logAttributes = ['*'];

    protected static $logFillable = true;
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    protected static $logName = 'cancel_cheque'; 

    protected $guarded = ['id'];
  
    protected $table = 'cancel_cheque';
}
