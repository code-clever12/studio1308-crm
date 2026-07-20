<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Stripe webhook endpoint. Signature verification and event handling
 * (payment_intent.succeeded, charge.refunded, payout.paid, payout.failed)
 * are implemented in Step 8 once the Stripe SDK and API keys are wired up.
 */
class PaymentController extends Controller
{
    public function webhook(Request $request): Response
    {
        return response('Stripe webhook handling is implemented in Step 8.', 501);
    }
}
