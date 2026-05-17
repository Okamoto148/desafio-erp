<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // Essa linha diz ao Laravel que esses campos podem ser gravados pelo formulário
    protected $fillable = ['name', 'document', 'email', 'status'];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
