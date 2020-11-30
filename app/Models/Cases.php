<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{
    use HasFactory;
    protected $table="cases";
    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }
    public function ccode()
    {
        return $this->country->code;
    }
}
