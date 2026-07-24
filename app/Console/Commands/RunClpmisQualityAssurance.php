<?php

namespace App\Console\Commands;

use App\Services\QualityAssurance\QualityAssuranceService;
use Illuminate\Console\Command;

class RunClpmisQualityAssurance extends Command
{
    protected $signature =
        'clpmis:qa
        {--skip-build : Do not run npm run build}
        {--skip-security : Do not run clpmis:security-check}';

    protected $description =
        'Run the complete CLPMIS quality-assurance pipeline and store a JSON report.';

    public function handle(
        QualityAssuranceService $qualityAssuranceService
    ): int {
        $this->warn(
            'The PHPUnit suite uses the database configured in phpunit.clpmis.xml.'
        );

        $this->warn(
            'Never configure the dedicated test suite to use the live CLPMIS database.'
        );

        $this->newLine();

        $report =
            $qualityAssuranceService
                ->run(
                    skipBuild:
                        (bool) $this->option(
                            'skip-build'
                        ),

                    skipSecurity:
                        (bool) $this->option(
                            'skip-security'
                        )
                );

        $this->table(
            [
                'Step',
                'Status',
                'Exit Code',
                'Seconds',
            ],
            collect(
                $report['steps']
            )->map(
                fn (array $step): array => [
                    $step['label'],
                    strtoupper(
                        $step['status']
                    ),
                    $step['exit_code'],
                    $step['duration_seconds'],
                ]
            )->all()
        );

        $this->newLine();

        if (
            $report['status']
            === 'passed'
        ) {
            $this->info(
                'The CLPMIS QA pipeline passed.'
            );

            return self::SUCCESS;
        }

        $this->error(
            'The CLPMIS QA pipeline failed. Review the step output and stored JSON report.'
        );

        return self::FAILURE;
    }
}
