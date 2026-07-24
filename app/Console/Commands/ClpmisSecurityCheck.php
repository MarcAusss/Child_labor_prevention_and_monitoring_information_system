<?php

namespace App\Console\Commands;

use App\Services\Security\SecurityAuditService;
use Illuminate\Console\Command;

class ClpmisSecurityCheck extends Command
{
    protected $signature =
        'clpmis:security-check
        {--fail-on-warning : Return a failing exit code when warnings exist}';

    protected $description =
        'Inspect important CLPMIS application, session, database, storage, and PHP security settings.';

    public function handle(
        SecurityAuditService $securityAuditService
    ): int {
        $audit = $securityAuditService
            ->audit();

        $this->newLine();

        $this->info(
            'CLPMIS Security Check'
        );

        $this->line(
            str_repeat('=', 72)
        );

        $rows = $audit['checks']
            ->map(
                fn (array $check): array => [
                    strtoupper(
                        $check['status']
                    ),

                    $check['category'],

                    $check['label'],

                    $check['details'],
                ]
            )
            ->all();

        $this->table(
            [
                'Status',
                'Category',
                'Check',
                'Result',
            ],
            $rows
        );

        $summary =
            $audit['summary'];

        $this->newLine();

        $this->line(
            'Score: '
            .$summary['score']
            .'%'
        );

        $this->line(
            'Passed: '
            .$summary['passed']
            .' | Warnings: '
            .$summary['warnings']
            .' | Critical: '
            .$summary['critical']
        );

        if (
            $summary['critical'] > 0
        ) {
            $this->error(
                'Critical security checks require attention.'
            );

            return self::FAILURE;
        }

        if (
            $this->option(
                'fail-on-warning'
            )
            && $summary['warnings'] > 0
        ) {
            $this->warn(
                'Warnings were detected.'
            );

            return self::FAILURE;
        }

        $this->info(
            'No critical security check failed.'
        );

        return self::SUCCESS;
    }
}
