<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = ['name', 'ip', 'status', 'is_local', 'ssh_user', 'ssh_password', 'ssh_port', 'ssh_key'];

    protected $hidden = ['ssh_password', 'ssh_key'];

    protected $appends = ['encrypted_id'];

    public function getEncryptedIdAttribute()
    {
        return \Illuminate\Support\Facades\Crypt::encryptString($this->id);
    }
}
