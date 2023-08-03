<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class UserDetail.
 *
 * @package namespace App\Models;
 */
class UserDetail extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'users_id',
        'image_url',
        'phone',
        'address',
        'dob',
        'gender',
    ];

    public function user(){
        $this->belongsTo(User::class, 'user_id');
    }

}
