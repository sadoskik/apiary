<?php declare(strict_types = 1);

namespace App\Nova\Metrics;

use App\Rsvp;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class RsvpSourceBreakdown extends Partition
{
    /**
     * The displayable name of the metric.
     *
     * @var string
     */
    public $name = 'RSVP Sources';

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->result(
            Rsvp::where('event_id', $request->resourceId)
                ->leftJoin('recruiting_visits', 'source', '=', 'visit_token')
                ->selectRaw('if(recruiting_visits.id, "Recruiting Email", source) as rsvpsource')
                ->selectRaw('count(rsvps.id) as aggregate')
                ->groupBy('rsvpsource')
                ->orderBy('aggregate', 'desc')
                ->get()
                ->mapWithKeys(static function ($item): array {
                    if ($item->rsvpsource) {
                        return [$item->rsvpsource => $item->aggregate];
                    } else {
                        return ['<unknown>' => $item->aggregate];
                    }
                })
                ->toArray()
        );
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'rsvp-source-breakdown';
    }
}
