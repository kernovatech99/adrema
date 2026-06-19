<?php

namespace App\Invoice\Actions;

use App\Invoice\Models\Invoice;
use App\Member\Member;
use App\Payment\Subscription;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class MemberNewInvoiceAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'year' => 'required|integer|gte:0',
            'subscription_id' => 'required|exists:subscriptions,id',
            'member_id' => 'required|exists:members,id',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Member $member, Subscription $subscription, int $year): array
    {
        $invoice = Invoice::createForMember($member, collect([$member]), $year, $subscription);

        return [
            ...$invoice->getAttributes(),
            'to' => $invoice->to,
            'positions' => $invoice->getRelationValue('positions')->toArray(),
        ];
    }

    public function asController(ActionRequest $request, Member $member): JsonResponse
    {
        $payload = $this->handle(Member::find($request->member_id), Subscription::find($request->input('subscription_id')), $request->input('year'));
        return response()->json($payload);
    }
}
