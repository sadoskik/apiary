<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a template for a notififaction that is stored in the database.
 *
 * @property int $id the database ID for this NotificationTemplate
 * @property string $name The name of the template
 * @property string $subject The subject that will be used for emails sent using this template
 * @property string $body_markdown The body of this template
 * @property int $created_by The user ID that created the template
 */
class NotificationTemplate extends Model
{
    use Actionable;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['created_by'];

    /**
     * Get the user that owns the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'creator' => 'users',
        ];
    }
}
