<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'notified_user_id', 'notification_type_id', 'sub_type', 'message'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'remove' => 'boolean'
    ];

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
     * Get default notifications
     *
     * @return array
     */
    public static function getDefaultNotifications()
    {
        return [
            [
                'notification_type_id' => NotificationType::TYPE_REQUIRED,
                'sub_type' => 'no_active_queue',
                'message' => 'You do not have an active queue. Please create one and activate it.'
            ],
            [
                'notification_type_id' => NotificationType::TYPE_REQUIRED,
                'sub_type' => 'no_player_card',
                'message' => 'Your Player Card is currently blank. Please add a new entry.'
            ],
            // [
            //     'notification_type_id' => NotificationType::TYPE_REQUIRED,
            //     'sub_type' => 'no_paypal_email',
            //     'message' => 'Your payout email is blank. Please add a Paypal address.'
            // ],
            [
                'notification_type_id' => NotificationType::TYPE_INFORMATIONAL,
                'sub_type' => 'schedule_and_social',
                'message' => 'Set your game schedule and update social media links (Optional).'
            ],
            [
                'notification_type_id' => NotificationType::TYPE_INFORMATIONAL,
                'sub_type' => 'add_profile_link',
                'message' => 'Add profile link to your Twitch Panel for player access. See Brand Assets for logos.'
            ],
            [
                'notification_type_id' => NotificationType::TYPE_INFORMATIONAL,
                'sub_type' => 'follow_twitter',
                'message' => 'Follow @viewergames on Twitter to get notified of new blog posts and features.'
            ]
        ];
    }

    /**
     * Get the user record associated with the notification.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User', 'notified_user_id');
    }

    /**
     * Get the notification type record associated with the notification.
     */
    public function type()
    {
        return $this->belongsTo('App\Models\Notification\NotificationType', 'notification_type_id');
    }

    /**
     * Add default notifications
     *
     * @param int $userId
     * @return void
     */
    public static function insertDefault($userId)
    {
        foreach (self::getDefaultNotifications() as $defaultNotification) {
            $defaultNotification['notified_user_id'] = $userId;
            
            static::create($defaultNotification);
        }
    }

    /**
     * Change notification 'remove'
     *
     * @param int $userId
     * @param string $subType
     * @param bool $remove
     * @return bool
     */
    public static function toggle($userId, $subType, $remove)
    {
        $notification = self::where('notified_user_id', $userId)->where('sub_type', $subType)->first();

        if (!$notification) {
            return false;
        }

        $notification->remove = $remove;

        return $notification->save();
    }
}
