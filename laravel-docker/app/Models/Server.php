<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = ['name', 'ip', 'status', 'is_local', 'ssh_user', 'ssh_password', 'ssh_port', 'ssh_key'];
}
