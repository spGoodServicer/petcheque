<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerStatement extends Model
{
    use LogsActivity;

    protected static $logAttributes = ['*'];

    protected static $logFillable = true;

    protected static $logName = 'Customer Statement';

     /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(\App\CustomerStatementDetail::class, 'statement_id', 'id');
    }

    public function location()
    {
        return $this->hasOne(\App\BusinessLocation::class, 'id', 'location_id');
    }

    public function user()
    {
        return $this->hasOne(\App\User::class, 'id', 'added_by');
    }

    public function contact()
    {
        return $this->hasOne(\App\Contact::class, 'id', 'customer_id');
    }

    public function amount()
    {
        return $this->details->reduce(function ($carry, $item) {
            return $carry + $item->invoice_amount;
        });
    }
}
