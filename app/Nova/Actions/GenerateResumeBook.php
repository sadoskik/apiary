<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\DateTime;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Jobs\GenerateResumeBook as GenerateJob;

class GenerateResumeBook extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields  $fields
     * @param \Illuminate\Support\Collection  $models
     *
     * @return array
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $job = new GenerateJob($fields->major, $fields->resume_date_cutoff);
        $job->handle();

        return Action::download(route('api.v1.resumebook.show', ['datecode' => $job->datecode]), 'resume-book-'.$job->datecode.'.pdf');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        return [
            Text::make('Major')
                ->nullable()
                ->help('Only combine resumes for this major abbreviation (e.g., ME, EE, CMPE, CS).'),

            DateTime::make('Resume Date Cutoff')
                ->required(true)
                ->help('Only export resumes uploaded after this date.'),
        ];
    }

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;
}
