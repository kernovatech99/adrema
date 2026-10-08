<?php

namespace App\Form\Actions;

use App\Form\Models\Form;
use App\Lib\Events\Succeeded;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class FormCleanupAction
{
    use AsAction;

    public function handle(Form $form): void
    {
        $form->participants()->get()->each->delete();
        $form->save();
        ClearFrontendCacheAction::run();
    }

    public function asController(Form $form): JsonResponse
    {
        $this->handle($form);

        Succeeded::message('Teilnehmende gelöscht.')->dispatch();
        return response()->json([]);
    }
}
