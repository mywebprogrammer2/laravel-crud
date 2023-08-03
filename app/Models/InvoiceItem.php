<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item',
        'quantity',
        'price'
    ];

    public function invoice(){
        return $this->belongsTo(Invoices::class,'invoice_id','id');
    }
}
