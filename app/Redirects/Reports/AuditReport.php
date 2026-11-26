<?php

declare(strict_types=1);

namespace App\Redirects\Reports;

use App\Data\AuditData;

interface AuditReport
{
    public function render(AuditData $audit): string;
}
