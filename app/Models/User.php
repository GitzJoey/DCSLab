<?php

namespace App\Models;

use App\Models\Employee;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Vinkla\Hashids\Facades\Hashids;
use Spatie\Activitylog\ActivityLogger;

use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\LaratrustUserTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use LaratrustUserTrait;
    use HasFactory, HasApiTokens, Notifiable;
    use LogsActivity;
    use PivotEventTrait;
    use Messagable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected static $logAttributes = ['name'];

    protected static $logOnlyDirty = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function hId() : Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public static function boot()
    {
        parent::boot();

        static::pivotAttached(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
            $logger = app(ActivityLogger::class)->useLog($model->getLogNameToUse('pivotAttached'));

            foreach ($pivotIds as $pivotId) {
                $properties = [
                    'relationName' => $relationName,
                    'pivot_id' => $pivotId,
                    'pivotData' => empty($pivotIdsAttributes[$pivotId]) ? [] : $pivotIdsAttributes[$pivotId],
                ];

                $logger->performedOn($model)->withProperties($properties);

                if (method_exists($model, 'tapActivity')) {
                    $logger->tap([$model, 'tapActivity'], 'pivotAttached');
                }

                $logger->log($model->getDescriptionForEvent('pivotAttached'));
            }
        });

        static::pivotDetached(function ($model, $relationName, $pivotIds) {
            $logger = app(ActivityLogger::class)->useLog($model->getLogNameToUse('pivotDetached'));

            foreach ($pivotIds as $pivotId) {
                $properties = [
                    'relationName' => $relationName,
                    'pivot_id' => $pivotId,
                    'pivotData' => empty($pivotIdsAttributes[$pivotId]) ? [] : $pivotIdsAttributes[$pivotId],
                ];

                $logger->performedOn($model)->withProperties($properties);

                $logger->causedBy($model);

                if (method_exists($model, 'tapActivity')) {
                    $logger->tap([$model, 'tapActivity'], 'pivotDetached');
                }

                $logger->log($model->getDescriptionForEvent('pivotDetached'));
            }
        });

        static::pivotUpdated(function ($model, $relationName, $pivotIds) {
            $logger = app(ActivityLogger::class)->useLog($model->getLogNameToUse('pivotUpdated'));

            foreach ($pivotIds as $pivotId) {
                $properties = [
                    'relationName' => $relationName,
                    'pivot_id' => $pivotId,
                    'pivotData' => empty($pivotIdsAttributes[$pivotId]) ? [] : $pivotIdsAttributes[$pivotId],
                ];

                $logger->performedOn($model)->withProperties($properties);

                $logger->causedBy($model);

                if (method_exists($model, 'tapActivity')) {
                    $logger->tap([$model, 'tapActivity'], 'pivotUpdated');
                }

                $logger->log($model->getDescriptionForEvent('pivotUpdated'));
            }
        });
    }
}
