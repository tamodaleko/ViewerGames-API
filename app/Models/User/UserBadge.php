<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['receiver_user_id', 'issuer_user_id', 'badge_id', 'match_id'];

    /**
     * @inheritdoc
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * Get the badge record associated with the user.
     */
    public function badge()
    {
        return $this->belongsTo('App\Models\Badge');
    }

    /**
     * Get the receiver user record associated with the badge.
     */
    public function receiverUser()
    {
        return $this->belongsTo('App\Models\User\User', 'receiver_user_id');
    }

    /**
     * Get the issuer user record associated with the badge.
     */
    public function issuerUser()
    {
        return $this->belongsTo('App\Models\User\User', 'issuer_user_id');
    }

    /**
     * Give badge to the user.
     *
     * @param int $idType
     * @param int $receiverId
     * @param int $issuerId
     * @param int $matchId
     *
     * @return bool
     */
    public static function grantBadge($idType, $receiverId, $issuerId = null, $matchId = null)
    {
        return static::create([
            'badge_id' => $idType,
            'receiver_user_id' => $receiverId,
            'issuer_user_id' => $issuerId,
            'match_id' => $matchId
        ]);
    }
}
