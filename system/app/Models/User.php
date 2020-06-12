<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'password', 'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'login_token'
    ];

    public static function supperAdmin()
    {
        return static::where('role', 'supperadmin')->first();
    }

    public function preferences()
    {
        return UserPreference::where('user_id', $this->id)->get();
    }

    public function getPreferenceValue($keyname)
    {
        $preference = UserPreference::where([
            'user_id' => $this->id,
            'config_keyname' => $keyname,
        ])->first();

        if ($preference) {
            return $preference->getValue();
        }

        return null;
    }

    public function updatePreference($keyname, array $data)
    {
        return UserPreference::updateOrCreate([
            'user_id' => $this->id,
            'config_keyname' => $keyname,
        ], ['data' => $data]);
    }
}
