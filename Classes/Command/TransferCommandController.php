<?php
declare(strict_types=1);

namespace PunktDe\Analytics\Neos\Command;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Analytics\Neos\Transfer\JobRunner;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

class TransferCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var JobRunner
     */
    protected $jobRunner;

    public function historyCommand(): void
    {
        $this->jobRunner->runHistoryTransfer();
    }

    public function nodedataCommand(): void
    {
        $this->jobRunner->runNodeDataTransfer();
    }
}
