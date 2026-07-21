<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a requested appointment slot is no longer available at the
 * moment of booking (already taken, outside working hours, staff not
 * assigned to the service, etc.). Callers should catch this to offer the
 * customer a waitlist spot instead of a generic error.
 */
class BookingUnavailableException extends RuntimeException {}
