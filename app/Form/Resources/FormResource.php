<?php

namespace App\Form\Resources;

use App\Contribution\Enums\Country;
use App\Form\Data\ExportData;
use App\Form\Enums\NamiType;
use App\Form\Enums\SpecialType;
use App\Form\Fields\Field;
use App\Form\FormSettings;
use App\Form\Scopes\FormFilterScope;
use App\Form\Models\Form;
use App\Form\Models\Formtemplate;
use App\Group;
use App\Lib\Editor\EditorData;
use App\Lib\HasMeta;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Contribution\ContributionFactory;

/**
 * @mixin Form
 */
class FormResource extends JsonResource
{

    use HasMeta;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'from_human' => $this->from->format('d.m.Y'),
            'to_human' => $this->to->format('d.m.Y'),
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'excerpt' => $this->excerpt,
            'description' => $this->description,
            'mail_top' => $this->mail_top,
            'mail_bottom' => $this->mail_bottom,
            'registration_from' => $this->registration_from?->format('Y-m-d H:i:s'),
            'registration_until' => $this->registration_until?->format('Y-m-d H:i:s'),
            'is_in_dates' => $this->isInDates(),
            'config' => $this->config,
            'participants_count' => $this->participants_count,
            'is_active' => $this->is_active,
            'is_private' => $this->is_private,
            'has_nami_field' => $this->getFields()->hasNamiField(),
            'export' => $this->export,
            'needs_prevention' => $this->needs_prevention,
            'prevention_text' => $this->prevention_text,
            'prevention_conditions' => $this->prevention_conditions,
            'leader_conditions' => $this->leader_conditions,
            'zip' => $this->zip,
            'location' => $this->location,
            'country' => $this->country,
            'links' => [
                'participant_index' => route('form.participant.index', ['form' => $this->getModel(), 'parent' => null]),
                'participant_root_index' => route('form.participant.index', ['form' => $this->getModel(), 'parent' => -1]),
                'update' => route('form.update', $this->getModel()),
                'destroy' => route('form.destroy', $this->getModel()),
                'cleanup' => route('form.cleanup', $this->getModel()),
                'is_dirty' => route('form.is-dirty', $this->getModel()),
                'frontend' => str(app(FormSettings::class)->registerUrl)->replace('{slug}', $this->slug),
                'export' => route('form.export', $this->getModel()),
                'copy' => route('form.copy', $this->getModel()),
                'contribution' => route('form.contribution', $this->getModel()),
                'laterlink' => route('form.laterlink', $this->getModel()),
            ]
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function meta(): array
    {
        return [
            'base_url' => url(''),
            'groups' => Group::forSelect(),
            'fields' => Field::asMeta(),
            'filter' => FormFilterScope::fromRequest(request()->input('filter', '')),
            'links' => [
                'store' => route('form.store'),
                'formtemplate_index' => route('formtemplate.index'),
            ],
            'templates' => FormtemplateResource::collection(Formtemplate::get()),
            'namiTypes' => NamiType::forSelect(),
            'specialTypes' => SpecialType::forSelect(),
            'countries' => Country::forSelect(),
            'contribution_types' => app(ContributionFactory::class)->compilerSelect(),
            'default' => [
                'description' => [],
                'is_active' => true,
                'is_private' => false,
                'name' => '',
                'excerpt' => '',
                'from' => null,
                'to' => null,
                'registration_from' => null,
                'registration_until' => null,
                'mail_top' => [],
                'mail_bottom' => [],
                'config' => null,
                'header_image' => null,
                'mailattachments' => [],
                'prevention_text' => EditorData::default(),
                'id' => null,
                'export' => ExportData::from([]),
                'prevention_conditions' => ['mode' => 'all', 'ifs' => []],
                'zip' => '',
                'location' => '',
                'country' => null,
            ],
            'section_default' => [
                'name' => '',
                'intro' => '',
                'fields' => [],
            ]
        ];
    }
}
