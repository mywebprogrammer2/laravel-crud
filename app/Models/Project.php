<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Project.
 *
 * @package namespace App\Models;
 */
class Project extends Model implements Transformable
{
    use TransformableTrait,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'customer_id',
        'start_date',
        'end_date',
        'active',
        'status'
    ];


    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function customer(){
        return $this->belongsTo(User::class,'customer_id','id');
    }

    public function scopeUser($q,$id = 0){
        $id = $id >  0 ? $id : Auth::user()->id;
        return $q->where('customer_id',$id);
    }
}
