<?php


namespace vhallComponent\decouple\jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vss\Queue\Contracts\JobInterface;

abstract class BaseJob implements ShouldQueue, JobInterface
{
    use InteractsWithQueue, Queueable, SerializesModels;
}
