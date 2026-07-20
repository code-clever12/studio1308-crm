<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Stripe webhook endpoints. Signature verification and event handling
 * are implemented in Step 8 once the Stripe SDK and API keys are wired up.
 */
class PaymentController extends Controller
{
    /**
     * payment_intent.succeeded, charge.refunded.
     */
    public function webhook(Request $request): Response
    {
        return response('Stripe webhook handling is implemented in Step 8.', 501);
    }

    /**
     * payout.paid, payout.failed — ACH transfer status for staff payouts.
     */
    public function payoutWebhook(Request $request): Response
    {
        return response('Stripe payout webhook handling is implemented in Step 8.', 501);
    }
}
