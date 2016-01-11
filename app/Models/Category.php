<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 	Document meta model.
 */
class Category extends Model
{
    //Document this meta is describing
    public function docs()
    {
        return $this->belongsToMany('App\Models\Doc');
    }
}
