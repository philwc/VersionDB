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
            ->setName('vdb:downgrade')
            ->setDescription('Read the changelog table and downgrade to specified record.');
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

        $downgradeRecordId = $dialog->ask(
            $output, '<question>Please select a record to downgrade to:</question> ', ''
        );

        while (!is_numeric($downgradeRecordId)) {
            $output->writeln('Please enter a number!');
            $downgradeRecordId = $dialog->ask(
                $output, '<question>Please select a record to downgrade to:</question> ', ''
            );
        }

        $confirm = $dialog->ask(
            $output,
            '<question>Are you sure you want to downgrade to record ' . $downgradeRecordId . '? (y/n)</question> ',
            ''
        );

        if (in_array(strtolower($confirm), array('y', 'yes'))) {

            //We should remove all records after the one specified.
            $downgradeRecordId++;

            //check record exists;
            $downgradeRecord = '';
            foreach ($existingHashes as $existingHash) {
                if ($existingHash['record'] == $downgradeRecordId) {
                    $downgradeRecord = $existingHash;
                    break;
                }
            }

            if ($downgradeRecord == '') {
                $output->writeln('Invalid Record ID or unable to downgrade to specified record.');
                exit();
            }

            /* var_dump($downgradeRecord);

              var_dump($changes); */

            $downgradeDate = new \DateTime($downgradeRecord['date']);

            foreach ($changes as $hash => $details) {
                if ($details['date'] >= $downgradeDate) {
                    //run script
                    $details['file'] = $filesystemUpdates->getFile($hash);

                    $result = $dbUpdates->applyScript($hash, true, $details);

                    if ($result === true) {
                        $result = 'Removed Successfully';
                    } elseif ($result === false) {
                        $result = 'Failed Adding';
                    }

                    $output->writeln($result);
                }
            }
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
