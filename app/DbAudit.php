<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DbAudit extends Model
{
    protected $table = 'tempohub_dbaudit';

    protected $fillable = ['tablename', 'username', 'action', 'rowdata', 'newdata', 'changed', 'query'];

    public 	  $timestamps 	= false;
}

