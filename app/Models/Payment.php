<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Payment.
 *
 * @package namespace App\Models;
 */
class Payment extends Model implements Transformable
{
    use TransformableTrait,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',
        'payment_date',
        'amount_paid',
        'payment_method',
        'transaction_id',
        'notes',
        'transaction_no'
    ];

    protected $casts= [
        'payment_date' => 'date',
    ];

    public function invoice(){
        return $this->belongsTo(Invoice::class,'invoice_id');
    }
}
