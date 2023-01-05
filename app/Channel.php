<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
   
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mdlz_channel_master';

    /**
     * @var array
     */
    protected $fillable = ['refid', 'name', 'name_org', 'fld1751', 'main_channel_name', 'stat'];

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
    public function channel_name()
    {
        return $this->hasOne('name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('sub_channel_name');
    }

}
