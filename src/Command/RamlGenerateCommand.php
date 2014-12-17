<?php

namespace Creads\Api2SymfonyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class RamlGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api2symfony:generate:raml')
            ->setDescription('Convert raml specification to controllers')
            ->addArgument('file', InputArgument::REQUIRED, 'RAML Specification filename')
            ->addArgument('namespace', InputArgument::REQUIRED, 'Define a base namespace for you controllers')
            ->addOption('destination', 'd', InputOption::VALUE_OPTIONAL, 'A destination folder path to save controllers. Default is app cache folder')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        $namespace = $input->getArgument('namespace');

        $controllers = $this->getContainer()->get('api2symfony.converter.raml')->convert($input->getArgument('file'), str_replace('/', '\\', $namespace));

        $destination = $input->hasOption('destination') ? $input->getOption('destination') : null;

        $destination = $destination?$destination:$this->getContainer()->getParameter('api2symfony.default_dir');

        $fs = new Filesystem();
        if (!$fs->exists($destination)) {
            $output->writeln(sprintf('<error>Destination directory %s does not exist.</error>', $destination));
            if (!$dialog->askConfirmation(
                $output,
                '<question>Would you like to create it ? [y/N] </question>',
                false
            )) {
                exit;
            }

            $fs->mkdir($destination);
        }

        foreach ($controllers as $controller) {
            if ($this->getContainer()->get('api2symfony.dumper')->exists($controller, $destination)) {
                $output->writeln(sprintf('<error>A controller with the name "%s" already exists.</error>', $controller->getName()));
                if (!$dialog->askConfirmation(
                    $output,
                    '<question>Would you like to backup it (.old) and overwrite it ? [y/N] </question>',
                    false
                )) {
                    continue;
                }
            }

            $file = $this->getContainer()->get('api2symfony.dumper')->dump($controller, $destination);
            $output->writeln(sprintf('Created: <info>%s</info>', $controller->getName()));
        }
    }
}