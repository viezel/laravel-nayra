<?php
declare(strict_types=1);

namespace Viezel\Nayra\Nayra;

use ProcessMaker\Nayra\Bpmn\Models\DatePeriod;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TimerEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\JobManagerInterface;
use ProcessMaker\Nayra\Engine\JobManagerTrait;
use Viezel\Nayra\Facades\Nayra;
use Viezel\Nayra\Jobs\CycleTimerJob;
use Viezel\Nayra\Jobs\TimerJob;

class JobManager implements JobManagerInterface
{
    use JobManagerTrait;

    /**
     * Schedule a job for a specific date and time for the given BPMN element,
     * event definition and an optional Token object
     *
     * @param string $datetime in ISO-8601 format
     * @param TimerEventDefinitionInterface $eventDefinition
     * @param EntityInterface $element
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function scheduleDate(
        $datetime,
        TimerEventDefinitionInterface $eventDefinition,
        FlowElementInterface $element,
        TokenInterface $token = null
    ) {
        Nayra::saveProcessInstance($token->getInstance());
        TimerJob::dispatch($eventDefinition, $element, $token)
            ->delay(new Carbon($datetime));

        return $this;
    }

    /**
     * Schedule a job for a specific cycle for the given BPMN element, event definition
     * and an optional Token object
     *
     * @param DatePeriod $cycle in ISO-8601 format
     * @param TimerEventDefinitionInterface $eventDefinition
     * @param EntityInterface $element
     * @param TokenInterface $token
     */
    public function scheduleCycle(
        $cycle,
        TimerEventDefinitionInterface $eventDefinition,
        FlowElementInterface $element,
        TokenInterface $token = null
    ) {
        $now = now();
        $next = $this->getNextDateTimeCycle($cycle, $now);
        if ($token) {
            Nayra::saveProcessInstance($token->getInstance());
        }
        CycleTimerJob::dispatch($cycle, $element, $token, $now)
            ->delay($next);

        return $this;
    }

    /**
     * Schedule a job execution after a time duration for the given BPMN element,
     * event definition and an optional Token object
     *
     * @param string $duration in ISO-8601 format
     * @param TimerEventDefinitionInterface $eventDefinition
     * @param EntityInterface $element
     * @param TokenInterface $token
     */
    public function scheduleDuration(
        $duration,
        TimerEventDefinitionInterface $eventDefinition,
        FlowElementInterface $element,
        TokenInterface $token = null
    ) {
        if ($token) {
            Nayra::saveProcessInstance($token->getInstance());
        }
        TimerJob::dispatch($eventDefinition, $element, $token)
            ->delay(now()->add($duration));

        return $this;
    }
}
