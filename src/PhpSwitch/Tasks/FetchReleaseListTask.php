<?php

namespace PhpSwitch\Tasks;

use PhpSwitch\ReleaseList;

class FetchReleaseListTask extends BaseTask
{
    public function fetch()
    {
        $this->logger->info('===> Fetching release list...');
        $releaseList = new ReleaseList();
        $releaseList->fetchRemoteReleaseList($this->options);

        return $releaseList->getReleases();
    }
}
