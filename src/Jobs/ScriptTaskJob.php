<?php
declare(strict_types=1);

namespace Viezel\Nayra\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use Viezel\Nayra\Facades\Nayra;

class ScriptTaskJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tokenId;
    protected $instanceId;

    /**
     * Create a new job instance.
     *
     * @param TokenInterface  $token
     *
     * @return void
     */
    public function __construct(TokenInterface $token)
    {
        $this->tokenId = $token->getId();
        $this->instanceId = $token->getInstance()->getId();
    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {
        Nayra::executeScript($this->instanceId, $this->tokenId);
    }
}
