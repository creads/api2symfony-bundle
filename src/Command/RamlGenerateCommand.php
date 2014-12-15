<?php

namespace Creads\Api2SymfonyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RamlGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api2symfony:generate:raml')
            ->setDescription('Convert raml specification to controllers')
            ->addArgument('spec', InputArgument::REQUIRED, 'Path to load specification file')
            ->addArgument('namespace', InputArgument::REQUIRED, 'Define a namespace')
            ->addOption('destination', 'd', InputOption::VALUE_OPTIONAL, 'A destination folder path to save controllers. Default is app cache folder')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        $controllers = $this->getContainer()->get('api2symfony.converter.raml')->convert($input->getArgument('spec'), str_replace('/', '\\', $input->getArgument('namespace')));

        $destination = $input->hasOption('destination') ? $input->getOption('destination') : null;

        foreach ($controllers as $controller) {
            if ($this->getContainer()->get('api2symfony.dumper')->exists($controller, $destination)) {
                if ($dialog->askConfirmation($output,'<question>Controller has been already dumped. Would you dumped it again ? [y/n]</question>', false)) {
                    $file = $this->getContainer()->get('api2symfony.dumper')->dump($controller, $destination, true);
                }
            } else {
                $file = $this->getContainer()->get('api2symfony.dumper')->dump($controller, $destination);
            }

            if (isset($file)) {
                $output->writeln(sprintf('<info>New controller dumped at %s</info>', $file));
                unset($file);
            }
        }
    }
}