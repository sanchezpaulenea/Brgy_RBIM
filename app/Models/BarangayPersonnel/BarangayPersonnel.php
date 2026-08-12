<?php

namespace App\Models\BarangayPersonnel;

use App\Models\UserManagement\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BarangayPersonnel extends Model
{
    protected $table = 'barangay_personnel';

    protected $primaryKey = 'personnel_id';

    public $timestamps = false;

    protected $fillable = [
        'position_id',
        'personnel_last_name',
        'personnel_first_name',
        'personnel_middle_name',
        'personnel_suffix',
        'personnel_status_id',
        'personnel_date_of_birth',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'personnel_date_of_birth' => 'date',
    ];

    /**
     * @return HasOne<User, $this>
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'personnel_id', 'personnel_id');
    }
}
