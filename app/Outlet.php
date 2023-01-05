<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
   
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outlet_list';

    /**
     * @var array
     */
    protected $fillable = ['outlet_name', 'owner_name', 'channel_name','sub_channel_name','address','shop_image','user_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('Role');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles()
    {
        return $this->hasMany('channel_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('sub_channel_name');
    }

}
