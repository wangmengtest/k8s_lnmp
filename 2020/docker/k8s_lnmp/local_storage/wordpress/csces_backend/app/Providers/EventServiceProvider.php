<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Database\Events\QueryExecuted' => [
            'App\Listeners\QueryListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // 记录执行失败的队列任务
        Event::listen(function (JobFailed $jobFailed) {
            $payload = $jobFailed->job->payload();
            vss_logger()->error("[queue task failed]:", [
                'name'        => $payload['displayName'],
                'channel'     => $jobFailed->job->getQueue(),
                'job_id'      => $jobFailed->job->getJobId(),
                'error_msg'   => $jobFailed->exception->getMessage(),
                'error_trace' => $jobFailed->exception->getTrace(),
                'payload'     => $payload,
            ]);
        });
    }
}
