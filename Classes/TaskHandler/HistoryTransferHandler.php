<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\TaskHandler;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Flowpack\Task\Domain\Task\WorkloadInterface;
use Neos\Flow\Annotations as Flow;
use Flowpack\Task\TaskHandler\TaskHandlerInterface;
use PunktDe\Analytics\Neos\Transfer\JobRunner;

class HistoryTransferHandler implements TaskHandlerInterface
{
    /**
     * @Flow\Inject
     * @var JobRunner
     */
    protected $jobRunner;

    public function handle(WorkloadInterface $workload): string
    {
        $this->jobRunner->runHistoryTransfer();
        return 'success';
    }
}
