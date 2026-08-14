<?php

namespace App\Console\Commands;

use App\Models\ImageAltSuggestion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Bulk-approves pending image alt suggestions.
 *
 * Compatible with the Laravel 6/7-style console structure used by this project.
 */
class AltApprovePendingCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'alt:approve-pending
        {--reviewer= : User ID to write to reviewed_by}
        {--chunk=500 : Number of suggestions processed in one transaction}
        {--dry-run : Show how many suggestions would be approved without changing data}
        {--yes : Skip the interactive confirmation}';

    /**
     * @var string
     */
    protected $description = 'Approve all pending image alt suggestions without applying them to source models';

    /**
     * @return int
     */
    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');

        if ($chunkSize < 1 || $chunkSize > 5000) {
            $this->error('The --chunk value must be between 1 and 5000.');

            return 1;
        }

        /*
         * Freeze the upper ID boundary at command start. Suggestions created
         * while this command is running will not be approved accidentally.
         */
        $snapshotMaxId = ImageAltSuggestion::query()
            ->where('status', ImageAltSuggestion::STATUS_PENDING)
            ->max('id');

        if ($snapshotMaxId === null) {
            $this->info('There are no pending image alt suggestions.');

            return 0;
        }

        $snapshotQuery = ImageAltSuggestion::query()
            ->where('status', ImageAltSuggestion::STATUS_PENDING)
            ->where('id', '<=', $snapshotMaxId);

        $total = (clone $snapshotQuery)->count();

        if ($total === 0) {
            $this->info('There are no pending image alt suggestions.');

            return 0;
        }

        $this->table(
            ['Status', 'Count', 'Maximum ID'],
            [[ImageAltSuggestion::STATUS_PENDING, $total, $snapshotMaxId]]
        );

        if ((bool) $this->option('dry-run')) {
            $sampleIds = (clone $snapshotQuery)
                ->orderBy('id')
                ->limit(10)
                ->pluck('id')
                ->implode(', ');

            $this->comment('Dry run: no database records were changed.');
            $this->line('First suggestion IDs: ' . $sampleIds);

            return 0;
        }

        $reviewerId = $this->validatedReviewerId();

        if ($reviewerId === null) {
            return 1;
        }

        if (!(bool) $this->option('yes')) {
            $confirmed = $this->confirm(
                'Approve ' . $total . ' pending suggestions as reviewer #' . $reviewerId . '?',
                false
            );

            if (!$confirmed) {
                $this->comment('Operation cancelled. No records were changed.');

                return 0;
            }
        }

        $approved = 0;
        $skipped = 0;
        $startedAt = Carbon::now();

        try {
            ImageAltSuggestion::query()
                ->where('status', ImageAltSuggestion::STATUS_PENDING)
                ->where('id', '<=', $snapshotMaxId)
                ->orderBy('id')
                ->chunkById($chunkSize, function (Collection $candidates) use (
                    $reviewerId,
                    $startedAt,
                    &$approved,
                    &$skipped,
                    $total
                ) {
                    $candidateIds = $candidates->pluck('id')->all();
                    $candidateCount = count($candidateIds);
                    $approvedInChunk = 0;

                    DB::transaction(function () use (
                        $candidateIds,
                        $reviewerId,
                        $startedAt,
                        &$approvedInChunk
                    ) {
                        /*
                         * Re-read and lock each chunk. A record changed by another
                         * process after selection is skipped instead of overwritten.
                         */
                        $suggestions = ImageAltSuggestion::query()
                            ->whereIn('id', $candidateIds)
                            ->where('status', ImageAltSuggestion::STATUS_PENDING)
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->get();

                        foreach ($suggestions as $suggestion) {
                            /* Match AltSuggestionController::approve() behavior. */
                            $suggestion->approved_alt = $suggestion->suggested_alt;
                            $suggestion->approved_title = $suggestion->suggested_title;
                            $suggestion->setStatus(ImageAltSuggestion::STATUS_APPROVED);
                            $suggestion->reviewed_by = $reviewerId;
                            $suggestion->reviewed_at = $startedAt;
                            $suggestion->save();

                            $approvedInChunk++;
                        }
                    }, 3);

                    $approved += $approvedInChunk;
                    $skipped += $candidateCount - $approvedInChunk;

                    $this->line(
                        'Processed: ' . ($approved + $skipped) . '/' . $total
                        . '; approved: ' . $approved
                        . '; skipped: ' . $skipped
                    );
                }, 'id');
        } catch (Throwable $exception) {
            $this->error('Approval stopped: ' . $exception->getMessage());
            $this->line('Completed chunks remain committed; the failed chunk was rolled back.');

            return 1;
        }

        $remainingInSnapshot = ImageAltSuggestion::query()
            ->where('status', ImageAltSuggestion::STATUS_PENDING)
            ->where('id', '<=', $snapshotMaxId)
            ->count();

        $this->info('Approved suggestions: ' . $approved);
        $this->line('Skipped because their status changed concurrently: ' . $skipped);
        $this->line('Pending suggestions remaining in the initial snapshot: ' . $remainingInSnapshot);
        $this->comment('No alt/title values were applied to source models.');

        return $remainingInSnapshot === 0 ? 0 : 1;
    }

    /**
     * @return int|null
     */
    private function validatedReviewerId(): ?int
    {
        $value = trim((string) $this->option('reviewer'));

        if ($value === '' || !ctype_digit($value) || (int) $value < 1) {
            $this->error('A valid positive --reviewer user ID is required.');

            return null;
        }

        $reviewerId = (int) $value;

        if (!User::query()->whereKey($reviewerId)->exists()) {
            $this->error('Reviewer user #' . $reviewerId . ' was not found.');

            return null;
        }

        return $reviewerId;
    }
}
