<?php

namespace philwc\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use philwc\Classes;

/**
 * Downgrade
 *
 * @author Philip Wright- Christie <philwc@gmail.com>
 */
class DowngradeCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('downgrade')
            ->setDescription('Analyse SQL files, compare to the database changelog and apply any new scripts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new \philwc\Classes\Config();
        $sqlDir = $config->getSetting('file', 'sqlDir');

        $filesystemUpdates = new \philwc\Classes\FilesystemUpdates($sqlDir, false);
        $changes           = $filesystemUpdates->get();

        $dbUpdates = new \philwc\Classes\DBUpdates();

        $existingHashes = $dbUpdates->getExistingHashes(false);

        $output->writeln('Existing Changes');
        $output->writeln('================');

        $table = $this->getHelperSet()->get('table');
        $table
            ->setHeaders(array('Record Number', 'Upgrade Hash', 'Downgrade Hash', 'Date Added', 'Author', 'Description'))
            ->setRows($existingHashes);

        $table->render($output);

        $dialog = $this->getHelperSet()->get('dialog');

        $downgradeRecord = $dialog->ask(
            $output, '<question>Please select a record to downgrade to:</question> ', ''
        );

        while (!is_numeric($downgradeRecord)) {
            $output->writeln('Please enter a number!');
            $downgradeRecord = $dialog->ask(
                $output, '<question>Please select a record to downgrade to:</question> ', ''
            );
        }

        //check record exists;


        $confirm = $dialog->ask(
            $output,
            '<question>Are you sure you want to downgrade to record ' . $downgradeRecord . '? (y/n)</question> ',
            ''
        );

        if (in_array(strtolower($confirm), array('y', 'yes'))) {
            //do stuff
            var_dump($changes);
        }


        /*
         * foreach ($dbUpdates->getExistingHashes() as $hash) {
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
          } */
    }

}
