<?php

namespace philwc\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use philwc\Classes;

/**
 * Update
 *
 * @author Philip Wright- Christie <philwc@gmail.com>
 */
class UpdateCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Analyse SQL files, compare to the database changelog and apply any new scripts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystemUpdates = new \philwc\Classes\FilesystemUpdates(__DIR__ . '/../sql');
        $changes           = $filesystemUpdates->get();

        $dbUpdates = new \philwc\Classes\DBUpdates();

        foreach ($dbUpdates->getExistingHashes() as $hash) {
            if (isset($changes[$hash])) {
                unset($changes[$hash]);
            }
        }

        if (empty($changes)) {
            $output->writeln('No Changes To Apply');
        } else {
            foreach ($changes as $hash => $details) {
                $output->write("Processing $hash [{$details['author']}] [{$details['description']}]... ");

                $details['file'] = $filesystemUpdates->getFile($hash);

                $result = $dbUpdates->applyScript($hash, $details);

                if ($result === true) {
                    $result = 'Added Successfully';
                } elseif ($result === false) {
                    $result = 'Failed Adding';
                }

                $output->writeln($result);
            }
        }
    }

}