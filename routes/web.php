<?php

use App\Activity\Actions\ActivityStoreAction;
use App\Activity\Actions\ActivityUpdateAction;
use App\Activity\Actions\CreateAction as ActivityCreateAction;
use App\Activity\Actions\DestroyAction as ActivityDestroyAction;
use App\Activity\Actions\EditAction as ActivityEditAction;
use App\Activity\Actions\IndexAction as ActivityIndexAction;
use App\Activity\Api\SubactivityShowAction;
use App\Activity\Api\SubactivityStoreAction;
use App\Activity\Api\SubactivityUpdateAction;
use App\Contribution\Actions\FormAction as ContributionFormAction;
use App\Contribution\Actions\GenerateAction as ContributionGenerateAction;
use App\Course\Actions\CourseDestroyAction;
use App\Course\Actions\CourseIndexAction;
use App\Course\Actions\CourseStoreAction;
use App\Invoice\Actions\InvoiceStoreAction;
use App\Course\Actions\CourseUpdateAction;
use App\Efz\ShowEfzDocumentAction;
use App\Fileshare\Actions\FileshareApiIndexAction;
use App\Fileshare\Actions\FileshareStoreAction;
use App\Fileshare\Actions\FileshareUpdateAction;
use App\Fileshare\Actions\ListFilesAction;
use App\Form\Actions\ExportAction as ActionsExportAction;
use App\Form\Actions\FormCleanupAction;
use App\Form\Actions\FormCopyAction;
use App\Form\Actions\FormDestroyAction;
use App\Form\Actions\FormGenerateLaterlinkAction;
use App\Form\Actions\FormIndexAction;
use App\Group\Actions\GroupBulkstoreAction;
use App\Group\Actions\GroupIndexAction;
use App\Form\Actions\FormStoreAction;
use App\Form\Actions\FormtemplateDestroyAction;
use App\Form\Actions\FormtemplateIndexAction;
use App\Form\Actions\FormtemplateStoreAction;
use App\Form\Actions\FormtemplateUpdateAction;
use App\Form\Actions\FormUpdateAction;
use App\Form\Actions\FormUpdateMetaAction;
use App\Form\Actions\GenerateContributionAction;
use App\Form\Actions\IsDirtyAction;
use App\Form\Actions\ParticipantAssignAction;
use App\Form\Actions\ParticipantDestroyAction;
use App\Form\Actions\ParticipantFieldsAction;
use App\Form\Actions\ParticipantIndexAction;
use App\Form\Actions\ParticipantStoreAction;
use App\Form\Actions\ParticipantUpdateAction;
use App\Initialize\Actions\InitializeAction;
use App\Initialize\Actions\InitializeFormAction;
use App\Initialize\Actions\NamiGetSearchLayerAction;
use App\Initialize\Actions\NamiLoginCheckAction;
use App\Initialize\Actions\NamiSearchAction;
use App\Invoice\Actions\DisplayPdfAction;
use App\Invoice\Actions\DisplayRememberpdfAction;
use App\Invoice\Actions\InvoiceDestroyAction;
use App\Invoice\Actions\InvoiceIndexAction;
use App\Invoice\Actions\InvoiceUpdateAction;
use App\Invoice\Actions\MassPostPdfAction;
use App\Invoice\Actions\MassStoreAction as InvoiceMassStoreAction;
use App\Invoice\Actions\MemberNewInvoiceAction;
use App\Invoice\Actions\PaymentPositionIndexAction;
use App\Maildispatcher\Actions\CreateAction;
use App\Maildispatcher\Actions\DestroyAction;
use App\Maildispatcher\Actions\EditAction;
use App\Maildispatcher\Actions\IndexAction;
use App\Maildispatcher\Actions\StoreAction as MaildispatcherStoreAction;
use App\Maildispatcher\Actions\UpdateAction as MaildispatcherUpdateAction;
use App\Mailgateway\Actions\StoreAction;
use App\Mailgateway\Actions\UpdateAction;
use App\Member\Actions\ExportAction;
use App\Member\Actions\MemberDeleteAction;
use App\Member\Actions\MemberResyncAction;
use App\Member\Actions\MemberShowAction;
use App\Member\Actions\SearchAction;
use App\Member\MemberController;
use App\Membership\Actions\ListForGroupAction;
use App\Membership\Actions\MassListAction;
use App\Membership\Actions\MassStoreAction;
use App\Membership\Actions\MemberIndexAction;
use App\Membership\Actions\MembershipDestroyAction;
use App\Membership\Actions\MembershipIndexAction as ActionsMembershipIndexAction;
use App\Membership\Actions\MembershipStoreAction;
use App\Membership\Actions\MembershipUpdateAction;
use App\Payment\SubscriptionController;

Route::group(['namespace' => 'App\\Http\\Controllers'], function (): void {
    Auth::routes(['register' => false]);
});

Route::group(['middleware' => 'auth:web'], function (): void {
    Route::post('/nami/login-check', NamiLoginCheckAction::class)->name('nami.login-check');
    Route::post('/nami/get-search-layer', NamiGetSearchLayerAction::class)->name('nami.get-search-layer');
    Route::post('/nami/search', NamiSearchAction::class)->name('nami.search');
    Route::get('/initialize', InitializeFormAction::class)->name('initialize.form');
    Route::post('/initialize', InitializeAction::class)->name('initialize.store');
    Route::resource('member', MemberController::class)->except('show', 'destroy');
    Route::delete('/member/{member}', MemberDeleteAction::class);
    Route::get('/member/{member}', MemberShowAction::class)->name('member.show');
    Route::resource('subscription', SubscriptionController::class);
    Route::get('/member/{member}/efz', ShowEfzDocumentAction::class)->name('efz');
    Route::get('/member/{member}/resync', MemberResyncAction::class)->name('member.resync');
    Route::get('member-export', ExportAction::class)->name('member-export');
    Route::get('/activity', ActivityIndexAction::class)->name('activity.index');
    Route::get('/activity/{activity}/edit', ActivityEditAction::class)->name('activity.edit');
    Route::get('/activity/create', ActivityCreateAction::class)->name('activity.create');
    Route::post('/activity', ActivityStoreAction::class)->name('activity.store');
    Route::patch('/activity/{activity}', ActivityUpdateAction::class)->name('activity.update');
    Route::delete('/activity/{activity}', ActivityDestroyAction::class)->name('activity.destroy');
    Route::post('/subactivity', SubactivityStoreAction::class)->name('api.subactivity.store');
    Route::patch('/subactivity/{subactivity}', SubactivityUpdateAction::class)->name('api.subactivity.update');
    Route::get('/subactivity/{subactivity}', SubactivityShowAction::class)->name('api.subactivity.show');
    Route::post('/api/member/search', SearchAction::class)->name('member.search');

    // ------------------------------- Contributions -------------------------------
    Route::get('/contribution', ContributionFormAction::class)->name('contribution.form');
    Route::get('/contribution-generate', ContributionGenerateAction::class)->name('contribution.generate');

    // ----------------------------------- mail ------------------------------------
    Route::post('/api/mailgateway', StoreAction::class)->name('mailgateway.store');
    Route::patch('/api/mailgateway/{mailgateway}', UpdateAction::class)->name('mailgateway.update');
    Route::get('/maildispatcher', IndexAction::class)->name('maildispatcher.index');
    Route::get('/maildispatcher/create', CreateAction::class)->name('maildispatcher.create');
    Route::get('/maildispatcher/{maildispatcher}', EditAction::class)->name('maildispatcher.edit');
    Route::patch('/maildispatcher/{maildispatcher}', MaildispatcherUpdateAction::class)->name('maildispatcher.update');
    Route::post('/maildispatcher', MaildispatcherStoreAction::class)->name('maildispatcher.store');
    Route::delete('/maildispatcher/{maildispatcher}', DestroyAction::class)->name('maildispatcher.destroy');


    // -------------------------------- allpayment ---------------------------------
    Route::post('/invoice/mass-store', InvoiceMassStoreAction::class)->name('invoice.mass-store');

    // ---------------------------------- invoice ----------------------------------
    Route::get('/invoice', InvoiceIndexAction::class)->name('invoice.index');
    Route::post('/invoice', InvoiceStoreAction::class)->name('invoice.store');
    Route::patch('/invoice/{invoice}', InvoiceUpdateAction::class)->name('invoice.update');
    Route::delete('/invoice/{invoice}', InvoiceDestroyAction::class)->name('invoice.destroy');
    Route::get('/invoice/{invoice}/pdf', DisplayPdfAction::class)->name('invoice.pdf');
    Route::get('/invoice/{invoice}/rememberpdf', DisplayRememberpdfAction::class)->name('invoice.rememberpdf');
    Route::get('/invoice/masspdf', MassPostPdfAction::class)->name('invoice.masspdf');
    Route::post('/invoice/new-invoice-attributes', MemberNewInvoiceAction::class)->name('invoice.new-invoice-attributes');


    // ----------------------------- invoice-position ------------------------------
    Route::get('/member/{member}/invoice-position', PaymentPositionIndexAction::class)->name('member.invoice-position.index');

    // --------------------------------- membership --------------------------------
    Route::get('/member/{member}/membership', MemberIndexAction::class)->name('member.membership.index');
    Route::post('/member/{member}/membership', MembershipStoreAction::class)->name('member.membership.store');
    Route::patch('/membership/{membership}', MembershipUpdateAction::class)->name('membership.update');
    Route::delete('/membership/{membership}', MembershipDestroyAction::class)->name('membership.destroy');
    Route::post('/api/membership/member-list', ListForGroupAction::class)->name('membership.member-list');
    Route::post('/api/membership/masslist', MassStoreAction::class)->name('membership.masslist.store');
    Route::get('/membership/masslist', MassListAction::class)->name('membership.masslist.index');
    Route::get('/membership', ActionsMembershipIndexAction::class)->name('membership.index');

    // ----------------------------------- group ----------------------------------
    Route::get('/group', GroupIndexAction::class)->name('group.index');
    Route::post('/group/bulkstore', GroupBulkstoreAction::class)->name('group.bulkstore');

    // ----------------------------------- course ----------------------------------
    Route::get('/member/{member}/course', CourseIndexAction::class)->name('member.course.index');
    Route::post('/member/{member}/course', CourseStoreAction::class)->name('member.course.store');
    Route::patch('/course/{course}', CourseUpdateAction::class)->name('course.update');
    Route::delete('/course/{course}', CourseDestroyAction::class)->name('course.destroy');

    // ------------------------------------ form -----------------------------------
    Route::get('/formtemplate', FormtemplateIndexAction::class)->name('formtemplate.index');
    Route::get('/form/{form}/export', ActionsExportAction::class)->name('form.export');
    Route::get('/form', FormIndexAction::class)->name('form.index');
    Route::patch('/form/{form}', FormUpdateAction::class)->name('form.update');
    Route::delete('/form/{form}', FormDestroyAction::class)->name('form.destroy');
    Route::delete('/form/{form}/cleanup', FormCleanupAction::class)->name('form.cleanup');
    Route::post('/formtemplate', FormtemplateStoreAction::class)->name('formtemplate.store');
    Route::patch('/formtemplate/{formtemplate}', FormtemplateUpdateAction::class)->name('formtemplate.update');
    Route::delete('/formtemplate/{formtemplate}', FormtemplateDestroyAction::class)->name('formtemplate.destroy');
    Route::post('/form', FormStoreAction::class)->name('form.store');
    Route::patch('/form/{form}/meta', FormUpdateMetaAction::class)->name('form.update-meta');
    Route::get('/form/{form}/participants/{parent?}', ParticipantIndexAction::class)->name('form.participant.index');
    Route::post('/form/{form}/is-dirty', IsDirtyAction::class)->name('form.is-dirty');
    Route::delete('/participant/{participant}', ParticipantDestroyAction::class)->name('participant.destroy');
    Route::post('/participant/{participant}/assign', ParticipantAssignAction::class)->name('participant.assign');
    Route::get('/participant/{participant}/fields', ParticipantFieldsAction::class)->name('participant.fields');
    Route::patch('/participant/{participant}', ParticipantUpdateAction::class)->name('participant.update');
    Route::post('/form/{form}/participant', ParticipantStoreAction::class)->name('form.participant.store');
    Route::post('/form/{form}/copy', FormCopyAction::class)->name('form.copy');
    Route::get('/form/{form}/contribution', GenerateContributionAction::class)->name('form.contribution');
    Route::get('/form/{form}/laterlink', FormGenerateLaterlinkAction::class)->name('form.laterlink');

    // ------------------------------------ fileshare -----------------------------------
    Route::post('/fileshare', FileshareStoreAction::class)->name('fileshare.store');
    Route::patch('/fileshare/{fileshare}', FileshareUpdateAction::class)->name('fileshare.update');
    Route::get('/api/fileshare', FileshareApiIndexAction::class)->name('api.fileshare.index');
    Route::post('/api/fileshare/{fileshare}/files', ListFilesAction::class)->name('api.fileshare.files');
});
