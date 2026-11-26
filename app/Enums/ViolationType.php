<?php

declare(strict_types=1);

namespace App\Enums;

enum ViolationType: string
{
    case REDIRECT_COUNT_MISMATCH = 'redirect_count_mismatch';
    case WRONG_REDIRECT_STATUS = 'wrong_redirect_status';
    case WRONG_DESTINATION = 'wrong_destination';
    case WRONG_FINAL_STATUS = 'wrong_final_status';
    case NETWORK_ERROR = 'network_error';
    case REDIRECT_LOOP = 'redirect_loop';
    case REDIRECT_LIMIT_EXCEEDED = 'redirect_limit_exceeded';
    case MISSING_LOCATION = 'missing_location';
    case INVALID_LOCATION = 'invalid_location';
    case INCOMPLETE_TRACE = 'incomplete_trace';
}
