<?php

namespace philwc\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use philwc\Classes;

/**
 * Add Change
 *
 * @author Philip Wright- Christie <philwc@gmail.com>
 */
class AddChangeCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('vdb:add')
            ->setDescription('Add a new change to the repository');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new \philwc\Classes\Config();
        $sqlDir = $config->getSetting('file', 'sqlDir');

        $filesystemUpdates = new \philwc\Classes\FilesystemUpdates($sqlDir);

        $dialog = $this->getHelperSet()->get('dialog');

        $updateScript = $dialog->ask(
            $output, '<question>Please enter the update script:</question> ', ''
        );

        $downgradeScript = $dialog->ask(
            $output, '<question>Please enter the downgrade script:</question> ', ''
        );

        $author = $dialog->ask(
            $output, '<question>Please enter the author name:</question> ', ''
        );

        $description = $dialog->ask(
            $output, '<question>Please enter a description:</question> ', ''
        );

        $filesystemUpdates->addFiles($updateScript, $downgradeScript, $author, $description);
    }

}
