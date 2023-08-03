<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Invoice.
 *
 * @package namespace App\Models;
 */
class Invoice extends Model implements Transformable
{
    use TransformableTrait,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_number',
        'project_id',
        'total_amount',
        'due_date',
        'status',
        'notes',
        'qb_id'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];


    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            $model->invoice_number = $model->generateCode();
        });
    }


    public function items() {
        return $this->hasMany(InvoiceItem::class, 'invoice_id','id');
    }

    public function project(){
        return  $this->belongsTo(Project::class, 'project_id');
    }

    public function payments(){
        return  $this->hasMany(Payment::class, 'invoice_id','id');
    }

    public function scopeUser($q,$id = 0){
        $id = $id >  0 ? $id : Auth::user()->id;
        return $q->whereHas('project',function($q)use($id){
            $q->user($id);
        });
    }

    function generateCode($project_id = 0) {

        $project_id = $project_id > 0 ? $project_id : $this->project_id;

        $lastRecord = static::where('project_id',$project_id)->orderBy('id', 'desc')->first();
        $lastCode = $lastRecord ? $lastRecord->invoice_number: '000000';

        $nextNumber = intval($lastCode) + 1;
        $nextCode = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return $nextCode;
    }

    public function getRemainingAttribute(){
        return $this->total_amount -  $this->payments->sum('amount_paid');
    }


}
