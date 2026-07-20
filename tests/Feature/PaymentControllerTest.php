<?php

it('returns 501 for the Stripe webhook until Step 8 implements it', function () {
    $this->postJson(route('webhooks.stripe'), [])->assertStatus(501);
});
