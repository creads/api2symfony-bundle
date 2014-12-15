<?php

namespace Creads\Api2SymfonyBundle;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Filesystem\Filesystem;

use Creads\Api2Symfony\SymfonyController;

/**
 * @author Quentin <q.pautrat@creads.org>
 */
class Api2SymfonyDumper
{
    /**
     * Templating system
     *
     * @var EngineInterface
     */
    private $templating;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Default destination folder path
     *
     * @var string
     */
    private $destination;

    /**
     * Initialize generator
     *
     * @param EngineInterface   $templating
     * @param Filesystem        $filesystem
     * @param string            $destination
     */
    public function __construct(EngineInterface $templating, Filesystem $filesystem, $destination)
    {
        $this->templating   = $templating;
        $this->filesystem   = $filesystem;
        $this->destination  = $destination;
    }

    /**
     * Returns destination folder path
     *
     * @param  SymfonyController $controller
     * @param  string            $destination Base folder
     * @return string
     */
    protected function getDestinationFolder(SymfonyController $controller, $destination = null)
    {
        $destination = (!$destination || empty($destination)) ? $this->destination : $destination;

        if ($controller->getNamespace() && !empty($controller->getNamespace())) {
            $destination .= '/' . str_replace('\\', '/', $controller->getNamespace());
        }

        return $destination;
    }

    /**
     * Returns destination file path
     *
     * @param  SymfonyController $controller
     * @param  string            $destination Base folder
     * @return string
     */
    protected function getDestinationFilename(SymfonyController $controller, $destination = null)
    {
        return $this->getDestinationFolder($controller, $destination) . '/' . $controller->getName() . '.php';
    }

    /**
     * Does the controller exists in destination directory
     *
     * @param  SymfonyController $controller
     * @param  string            $destination
     * @return boolean
     */
    public function exists(SymfonyController $controller, $destination)
    {
        return $this->filesystem->exists($this->getDestinationFilename($controller, $destination));
    }

    /**
     * Save SymfonyController instances into file
     *
     * @param  array  $controllers List of SymfonyController
     * @param  string $destination Destination folder to save controllers
     * @return string File path dumped
     */
    public function dump(SymfonyController $controller, $destination = null, $backup = false)
    {
        $render = $this->templating->render('Api2SymfonyBundle::controller.php.twig', array('controller' => $controller));

        $destinationFolder = $this->getDestinationFolder($controller, $destination);

        if (!$this->filesystem->exists($destinationFolder)) {
            $this->filesystem->mkdir($destinationFolder);
        }

        $filename = $this->getDestinationFilename($controller, $destination);

        if ($backup) {
            $this->filesystem->copy($filename, $filename . '.old');
        }

        if (false === file_put_contents($filename, $render)) {
            throw new \Exception(sprintf("Controller %s can not be write into %s", $controller->getName(), $filename));
        }

        return $filename;
    }
}