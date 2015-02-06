<?php

namespace Creads\Api2SymfonyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Question\Question;

class RamlGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api2symfony:generate:raml')
            ->setDescription('Convert a RAML specification to mocked controllers')
            ->addArgument('raml_file', InputArgument::REQUIRED, 'RAML specification file')
            ->addArgument('bundle_namespace', InputArgument::REQUIRED, 'Namespace of the bundle where controllers will be dumped')
            ->addOption('destination', 'd', InputOption::VALUE_OPTIONAL, 'Force another destination for dumped controllers')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command will convert a RAML specification to mocked controllers.

  <info>php %command.full_name% path/to/file.raml Base/Namespace/Of/YourBundle [--destination=force/another/destination/path]</info>
EOT
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('raml_file');
        $namespace = $input->getArgument('bundle_namespace');
        $destination = $input->hasOption('destination') ? $input->getOption('destination') : null;

        if (!$destination) {
            //we try to guess the destination from namespace
            $namespace = preg_replace('/(\\\Controller)?\\\?$/', '', $namespace); //be sure to remove trailing slash or \Controller from namesapce
            $autoload = $this->getContainer()->getParameter('kernel.root_dir') . '/autoload.php';
            if (file_exists($autoload)) {
                $loader = require $autoload;
                $bundles = $this->getContainer()->getParameter('kernel.bundles');
                foreach ($bundles as $bundleName => $bundleClass) {
                    $reflection =  new \ReflectionClass($bundleClass);
                    if ($namespace === $reflection->getNamespaceName()) {
                        $bundleFile = $loader->findFile($bundleClass);
                        $destination = dirname($bundleFile) . '/Controller';
                    }
                }
            }

            if (!$destination) {
                throw new \RuntimeException(sprintf('Could not guess destination for namespace %s. Please  check it or use --destination to force a destination for generated controllers.', $namespace));
            }
        }

        $namespace  = $namespace . '\Controller';

        $controllers = $this->getContainer()->get('api2symfony.converter.raml')->convert($file, str_replace('/', '\\', $namespace));

        $dialog = $this->getHelperSet()->get('question');
        $fs = new Filesystem();
        if (!$fs->exists($destination)) {
            $output->writeln(sprintf('<error>Destination directory %s does not exist.</error>', $destination));
            if (!$dialog->ask(
                $input,
                $output,
                new Question('<question>Would you like to create it ? [y/N] </question>', false)
            )) {
                exit;
            }

            $fs->mkdir($destination);
        }

        foreach ($controllers as $controller) {



            if ($this->getContainer()->get('api2symfony.dumper')->exists($controller, $destination)) {

                $output->writeln(sprintf('* <comment>%s</comment>: <error>EXISTS</error>', $controller->getClassName()));
                $answer = $dialog->ask(
                    $input,
                    $output,
                    new Question(sprintf('<question>Overwrite this file (previous file will be renamed with extension .old) ?</question> [Y]/n ', $controller->getClassName()), false)
                );
                if ($answer === 'n' || $answer === 'N') {
                    continue;
                }
            }

            $file = $this->getContainer()->get('api2symfony.dumper')->dump($controller, $destination);
            $output->writeln(sprintf('* <comment>%s</comment>: <info>OK</info>', $controller->getClassName()));
        }
    }
}
