<?php

namespace App\Form\Models;

use App\Contribution\Enums\Country;
use App\Form\Actions\UpdateParticipantSearchIndexAction;
use App\Form\Data\ExportData;
use App\Form\Data\FieldCollection;
use App\Form\Data\FormConfigData;
use App\Lib\Editor\Condition;
use App\Lib\Editor\EditorData;
use App\Lib\Sorting;
use Cviebrock\EloquentSluggable\Sluggable;
use Database\Factories\Form\Models\FormFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Zoomyboy\MedialibraryHelper\DefersUploads;

/** @todo replace editor content with EditorData cast */
class Form extends Model implements HasMedia
{
    /** @use HasFactory<FormFactory> */
    use HasFactory;
    use Sluggable;
    use InteractsWithMedia;
    use DefersUploads;
    use Searchable;

    public $guarded = [];

    public $casts = [
        'config' => FormConfigData::class,
        'meta' => 'json',
        'description' => EditorData::class,
        'mail_top' => EditorData::class,
        'mail_bottom' => EditorData::class,
        'is_active' => 'boolean',
        'is_private' => 'boolean',
        'export' => ExportData::class,
        'needs_prevention' => 'boolean',
        'prevention_text' => EditorData::class,
        'prevention_conditions' => Condition::class,
        'from' => 'datetime',
        'to' => 'datetime',
        'registration_from' => 'datetime',
        'registration_until' => 'datetime',
        'country' => Country::class,
        'leader_conditions' => Condition::class,
    ];

    /**
     * @return SluggableConfig
     */
    public function sluggable(): array
    {
        return [
            'slug' => ['source' => ['name']],
        ];
    }

    /**
     * @return HasMany<Participant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('headerImage')
            ->singleFile()
            ->maxWidth(fn() => 500)
            ->forceFileName(fn(Form $model) => $model->slug)
            ->convert(fn() => 'jpg')
            ->registerMediaConversions(function () {
                $this->addMediaConversion('square')->fit(Fit::Crop, 400, 400);
            });
        $this->addMediaCollection('mailattachments')
            ->withDefaultProperties(fn() => [
                'conditions' => [
                    'mode' => 'all',
                    'ifs' => []
                ],
            ])
            ->withPropertyValidation(fn() => [
                'conditions.mode' => 'required|string|in:all,any',
                'conditions.ifs' => 'array',
                'conditions.ifs.*.field' => 'required',
                'conditions.ifs.*.comparator' => 'required',
                'conditions.ifs.*.value' => 'present',
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getRegistrationRules(): array
    {
        return $this->getFields()->reduce(fn($carry, $field) => [
            ...$carry,
            ...$field->getRegistrationRules($this),
        ], []);
    }

    /**
     * @return array<string, mixed>
     */
    public function getRegistrationMessages(): array
    {
        return $this->getFields()->reduce(fn($carry, $field) => [
            ...$carry,
            ...$field->getRegistrationMessages($this),
        ], []);
    }

    /**
     * @return array<string, mixed>
     */
    public function getRegistrationAttributes(): array
    {
        return $this->getFields()->reduce(fn($carry, $field) => [
            ...$carry,
            ...$field->getRegistrationAttributes($this),
        ], []);
    }

    public function getFields(): FieldCollection
    {
        return $this->config->fields();
    }


    // --------------------------------- Searching ---------------------------------
    // *****************************************************************************

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray()
    {
        return [
            'from' => $this->from->timestamp,
            'to' => $this->to->timestamp,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'is_private' => $this->is_private,
        ];
    }

    public static function booted(): void
    {
        static::saving(function (self $model) {
            if (is_null(data_get($model->meta, 'active_columns'))) {
                $model->setAttribute('meta', [
                    'active_columns' => $model->getFields()->count() ? $model->getFields()->take(4)->pluck('key')->toArray() : null,
                    'sorting' => Sorting::by('id'),
                ]);
                return;
            }

            if (is_array(data_get($model->meta, 'active_columns'))) {
                $model->setAttribute('meta', [
                    ...$model->meta,
                    'active_columns' => array_values(array_intersect([...$model->getFields()->pluck('key')->toArray(), 'created_at', 'prevention'], $model->meta['active_columns'])),
                ]);
                return;
            }
        });

        static::saved(function ($model) {
            UpdateParticipantSearchIndexAction::dispatch($model);
        });
    }

    public function participantsSearchableAs(): string
    {
        return config('scout.prefix') . 'forms_' . $this->id . '_participants';
    }

    public function defaultSorting(): Sorting
    {
        return Sorting::from($this->meta['sorting']);
    }

    public function isInDates(): bool
    {
        if ($this->registration_from && $this->registration_from->gt(now())) {
            return false;
        }

        if ($this->registration_until && $this->registration_until->lt(now())) {
            return false;
        }

        return true;
    }

    public function canRegister(): bool
    {
        return $this->is_active && $this->isInDates();
    }
}
