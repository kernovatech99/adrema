<template>
    <page-layout>
        <template #toolbar>
            <page-toolbar-button :href="meta.links.formtemplate_index" color="primary" icon="event">Vorlagen</page-toolbar-button>
            <page-toolbar-button color="primary" icon="plus" @click.prevent="create">Veranstaltung erstellen</page-toolbar-button>
        </template>

        <ui-popup v-if="single !== null && single.config === null" heading="Vorlage auswählen" @close="cancel">
            <div class="mt-3 grid gap-3 grid-cols-2">
                <a v-for="(template, index) in meta.templates" :key="index" class="py-2 px-3 border rounded bg-zinc-800 hover:bg-zinc-700 transition" href="#" @click.prevent="setTemplate(template)">
                    <span v-text="template.name" />
                </a>
            </div>
        </ui-popup>

        <ui-popup v-if="showing !== null" :heading="`Teilnehmende für ${showing.name}`" full @close="showing = null">
            <participants :has-nami-field="showing.has_nami_field" :root-url="showing.links.participant_root_index" :url="showing.links.participant_index" />
        </ui-popup>

        <ui-popup v-if="single !== null && single.config !== null" :heading="`Veranstaltung ${single.id ? 'bearbeiten' : 'erstellen'}`" full @close="cancel">
            <div class="flex flex-col mt-3">
                <ui-tabs v-model="active" :entries="tabs" />
                <div v-show="active === 0" class="grid grid-cols-4 gap-3">
                    <div class="flex space-x-3 col-span-2">
                        <f-text id="name" v-model="single.name" class="grow" label="Name" required />
                        <f-switch
                            id="is_active"
                            v-model="single.is_active"
                            name="is_active"
                            label="Aktiv"
                            hint="Inaktive Veranstaltungen werden außerhalb von Adrema wie nicht existierende Veranstaltungen betrachtet. Insbesondere ist eine Anmeldung dann nicht möglich und die Veranstaltung erscheint auch nicht in der Veranstaltungs-Übersicht."
                        />
                        <f-switch
                            id="is_private"
                            v-model="single.is_private"
                            name="is_private"
                            label="Privat"
                            hint="Ist eine Veranstaltung privat, so wird diese nicht auf der Website angezeigt. Eine Anmeldung ist jedoch trotzdem möglich, wenn man über den Anmelde-Link verfügt."
                        />
                    </div>
                    <f-singlefile
                        id="header_image"
                        v-model="single.header_image"
                        class="col-span-2"
                        label="Bild"
                        name="header_image"
                        parent-name="form"
                        :parent-id="single.id"
                        collection="headerImage"
                        required
                    />
                    <f-text id="from" v-model="single.from" type="date" label="Von" required />
                    <f-text id="to" v-model="single.to" type="date" label="Bis" required />
                    <f-text id="zip" v-model="single.zip" label="PLZ" />
                    <f-text id="location" v-model="single.location" label="Ort" />
                    <f-select id="country" v-model="single.country" class="col-span-2" name="country" label="Land" :options="meta.countries" />
                    <f-text
                        id="registration_from"
                        v-model="single.registration_from"
                        type="datetime-local"
                        label="Registrierung von"
                        hint="Ist eine Anmeldung laut dieser zwei Datumsangaben möglich, kann man sich anmelden. Andernfalls wird die Veranstaltung (mit Beschreibungstext) auf der Übersichtsseite angezeigt, man kommt allerdings nicht zum Anmeldeformular."
                        required
                    />
                    <f-text id="registration_until" v-model="single.registration_until" type="datetime-local" label="Registrierung bis" required />
                    <f-textarea
                        id="excerpt"
                        v-model="single.excerpt"
                        hint="Gebe hier eine kurze Beschreibung für die Veranstaltungs-Übersicht ein (Maximal 130 Zeichen)."
                        label="Auszug"
                        :rows="5"
                        class="col-span-full"
                        required
                    />
                </div>
                <div v-if="active === 1">
                    <f-editor id="description" v-model="single.description" name="description" label="Beschreibung" :rows="10" required />
                </div>
                <div v-if="active === 2">
                    <ui-note class="mt-2"> Sobald sich der erste Teilnehmer für die Veranstaltung angemeldet hat, kann dieses Formular nicht mehr geändert werden. </ui-note>
                    <form-builder v-model="single.config" :meta="meta" />
                </div>
                <div v-show="active === 3" class="grid grid-cols-[1fr_300px] gap-3">
                    <ui-note class="mt-2 col-span-full">
                        Hier kannst du die E-Mail anpassen, die nach der Anmeldung an den Teilnehmer verschickt wird.<br />
                        Es gibt dafür einen ersten E-Mail-Teil und einen zweiten E-Mail-Teil. Dazwischen werden die Daten des Teilnehmers aufgelistet.<br />
                        Die Anrede ("Hallo Max Mustermann") wird automatisch an den Anfang gesetzt.<br />
                        Außerdem kannst du Dateien hochladen, die automatisch mit angehangen werden.
                    </ui-note>
                    <div>
                        <ui-tabs v-model="activeMailTab" :entries="mailTabs" />
                        <f-editor v-if="activeMailTab === 0" id="mail_top" v-model="single.mail_top" name="mail_top" label="E-Mail-Teil 1" :rows="8" conditions required>
                            <template #conditions="{data, resolve}">
                                <conditions-form id="mail_top_conditions" :single="single" :value="data" @save="resolve" />
                            </template>
                        </f-editor>
                        <f-editor v-if="activeMailTab === 1" id="mail_bottom" v-model="single.mail_bottom" name="mail_bottom" label="E-Mail-Teil 2" :rows="8" conditions required>
                            <template #conditions="{data, resolve}">
                                <conditions-form id="mail_bottom_conditions" :single="single" :value="data" @save="resolve" />
                            </template>
                        </f-editor>
                    </div>
                    <f-multiplefiles
                        id="mailattachments"
                        v-model="single.mailattachments"
                        label="Anhänge"
                        name="mailattachments"
                        parent-name="form"
                        :parent-id="single.id"
                        collection="mailattachments"
                        class="row-span-2"
                    >
                        <template #buttons="{file, buttonClass, iconClass}">
                            <a v-tooltip="`Bedingungen`" href="#" :class="[buttonClass, 'bg-blue-200', 'relative']" @click.prevent="fileSettingPopup = file">
                                <div v-if="file.properties.conditions.ifs.length" class="absolute w-2 h-2 -mt-[0.05rem] -ml-[0.05rem] flex-none bg-red-900 rounded-full top-0 left-0" />
                                <ui-sprite src="setting" :class="[iconClass, 'text-blue-800']" />
                            </a>
                        </template>
                    </f-multiplefiles>
                </div>
                <div v-if="active === 4">
                    <div class="grid gap-3">
                        <ui-remote-resource id="export" v-model="single.export.root" label="Haupt-Ordner" />
                        <f-select id="group_by" v-model="single.export.group_by" :options="allFields" label="Gruppieren nach" name="group_by" />
                        <f-select id="to_group_field" v-model="single.export.to_group_field" :options="allFields" label="Nach Gruppe schreiben" name="to_group_field" />
                    </div>
                </div>
                <div v-show="active === 5" class="grid grid-cols-2 gap-3">
                    <f-switch id="needs_prevention" v-model="single.needs_prevention" name="needs_prevention" label="Prävention" />
                    <f-editor
                        id="prevention_text"
                        v-model="single.prevention_text"
                        hint="Wird an die Präventions-Email angehangen, die Teilnehmende dieser Veranstaltung erhalten"
                        :rows="6"
                        label="Präventions-Hinweis"
                    />
                    <ui-box heading="Bedingung für Präventions-Unterlagen">
                        <conditions id="prevention_conditions" v-model="single.prevention_conditions" :single="single" />
                    </ui-box>
                </div>
                <div v-if="active === 6">
                    <ui-box heading="Bedingung für Leiter*in">
                        <conditions id="leader_conditions" v-model="single.leader_conditions" :single="single" />
                    </ui-box>
                </div>
            </div>
            <template #actions>
                <a href="#" @click.prevent="submit">
                    <ui-sprite src="save" class="text-zinc-400 w-6 h-6" />
                </a>
            </template>
        </ui-popup>

        <ui-popup v-if="fileSettingPopup !== null" :heading="`Bedingungen für Datei ${fileSettingPopup.name}`" @close="fileSettingPopup = null">
            <conditions-form id="filesettings" :single="single" :value="fileSettingPopup.properties.conditions" @save="saveFileConditions" />
        </ui-popup>

        <page-filter>
            <template #buttons>
                <f-text id="search" :model-value="getFilter('search')" label="Suchen …" size="sm" @update:model-value="setFilter('search', $event)" />
                <f-switch id="past" :model-value="getFilter('past')" label="vergangene zeigen" name="past" size="sm" @update:model-value="setFilter('past', $event)" />
                <f-switch id="inactive" :model-value="getFilter('inactive')" label="inaktive zeigen" name="inactive" size="sm" @update:model-value="setFilter('inactive', $event)" />
            </template>
        </page-filter>

        <table cellspacing="0" cellpadding="0" border="0" class="custom-table custom-table-sm">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Von</th>
                    <th>Bis</th>
                    <th>Tags</th>
                    <th>Anzahl TN</th>
                    <th />
                </tr>
            </thead>

            <tbody>
                <tr v-for="(form, index) in data" :key="index">
                    <td>
                        <div v-text="form.name" />
                    </td>
                    <td>
                        <div v-text="form.from_human" />
                    </td>
                    <td>
                        <div v-text="form.to_human" />
                    </td>
                    <td>
                        <div class="bool-row">
                            <ui-bool true-comment="aktiv" false-comment="inaktiv" :value="form.is_active">A</ui-bool>
                            <ui-bool true-comment="private Veranstaltung" false-comment="nicht private Veranstaltung" :value="form.is_private">P</ui-bool>
                            <ui-bool true-comment="Anmeldung möglich (lt. 'Registrierung von / bis')" false-comment="Anmeldeschluss erreicht" :value="form.is_in_dates">D</ui-bool>
                        </div>
                    </td>
                    <td>
                        <div v-text="form.participants_count" />
                    </td>
                    <td>
                        <div class="flex space-x-2">
                            <ui-action-button tooltip="Bearbeiten" class="btn-warning" icon="pencil" @click.prevent="edit(form)" />
                            <ui-action-button tooltip="Teilnehmende anzeigen" class="btn-info" icon="user" @click.prevent="showParticipants(form)" />
                            <ui-action-button :href="form.links.frontend" target="_BLANK" tooltip="zur Anmeldeseite" class="btn-info" icon="eye" />
                            <ui-action-button tooltip="Kopieren" class="btn-info" icon="copy" @click="onCopy(form)" />
                            <ui-action-button tooltip="Nachmelde-Link kopieren" class="btn-info" icon="externallink" @click="copyLaterLink(form)" />
                            <ui-action-button tooltip="Zuschuss-Liste erstellen" class="btn-info" icon="contribution" @click="onGenerateContribution(form)" />
                            <ui-action-button :href="form.links.export" target="_BLANK" tooltip="als Tabellendokument exportieren" class="btn-info" icon="document" />
                            <ui-action-button tooltip="alle Teilnehmende löschen" class="btn-danger" icon="broom" @click.prevent="onCleanup(form)" />
                            <ui-action-button tooltip="Veranstaltung löschen" class="btn-danger" icon="trash" @click.prevent="onDelete(form)" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="px-6">
            <ui-pagination class="mt-4" :value="meta" @reload="reloadPage" />
        </div>
    </page-layout>
</template>

<script lang="js" setup>
import {ref, inject, computed} from 'vue';
import {indexProps, useIndex} from '../../composables/useInertiaApiIndex.js';
import FormBuilder from '../formtemplate/FormBuilder.vue';
import Participants from './Participants.vue';
import Conditions from './Conditions.vue';
import ConditionsForm from './ConditionsForm.vue';
import {useToast} from 'vue-toastification';
import useSwal from '@/stores/swalStore.ts';
import useDownloads from '@/composables/useDownloads.ts';
import useClipboard from 'vue-clipboard3';

const props = defineProps(indexProps);
const {meta, data, reloadPage, reload, create, single, edit, cancel, submit, remove, getFilter, setFilter} = useIndex(props.data, 'form');
const axios = inject('axios');
const toast = useToast();
const {download} = useDownloads();
const {toClipboard} = useClipboard();

const showing = ref(null);
const fileSettingPopup = ref(null);

const active = ref(0);
const activeMailTab = ref(0);
const tabs = [{title: 'Allgemeines'}, {title: 'Beschreibung'}, {title: 'Formular'}, {title: 'Bestätigungs-E-Mail'}, {title: 'Export'}, {title: 'Prävention'}, {title: 'Zuschüsse'}];
const mailTabs = [{title: 'vor Daten'}, {title: 'nach Daten'}];
const swal = useSwal();

const allFields = computed(() => {
    if (!single.value) {
        return [];
    }

    const result = [];
    single.value.config.sections.forEach((section) => {
        section.fields.forEach((field) => {
            result.push({id: field.key, name: field.name});
        });
    });

    return result;
});

async function onCopy(form) {
    await swal.confirm(
        'Diese Veranstaltung kopieren?',
        'Nach dem Kopieren wird die Veranstaltung auf inaktiv gesetzt. Bitte aktiviere den Filter "inaktive zeigen", um die kopierte Veranstaltung zu sehen.',
    );
    await axios.post(form.links.copy, {});
    reload(false);
}

async function onGenerateContribution(form) {
    const response = await swal.ask(
        'Zuschussliste erstellen',
        'Hiermit erstellst du eine Zuschussliste mit allen angemeldeten Mitgliedern. Bite wähle aus, für welche Organisation du eine Liste erstellen willst.',
        [
            {
                name: 'type',
                label: 'Organisation',
                required: true,
                type: 'select',
                options: meta.value.contribution_types,
            },
        ],
    );
    await download(form.links.contribution, {type: response.type, validate: '1'});
    await download(form.links.contribution, {type: response.type});
}

async function onDelete(form) {
    await swal.confirm('Diese Veranstaltung löschen?', `Die Veranstaltung ${form.name} wird gelöscht werden.`);
    await remove(form);
}

async function onCleanup(form) {
    await swal.confirm('Teilnehmende löschen?', `Alle Teilnehmenden der Veranstaltung ${form.name} werden unwiderruflich gelöscht. Die Veranstaltung selbst bleibt erhalten.`);
    await axios.delete(form.links.cleanup);
    reload(false);
}

function setTemplate(template) {
    active.value = 0;
    single.value.config = template.config;
    single.value.mail_top = template.mail_top;
    single.value.mail_bottom = template.mail_bottom;
}

async function copyLaterLink(form) {
    const response = await axios.get(form.links.laterlink);
    await toClipboard(response.data.url);
    toast.success('Link in Zwischenablage kopiert');
}

async function saveFileConditions(conditions) {
    await axios.patch(`/mediaupload/${fileSettingPopup.value.id}`, {
        properties: {
            ...fileSettingPopup.value.properties,
            conditions: conditions,
        },
    });

    fileSettingPopup.value = null;
    toast.success('Datei aktualisiert');
}

function showParticipants(form) {
    showing.value = form;
}
</script>
