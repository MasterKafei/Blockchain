<?php

namespace AppBundle\Service\Util\EntityManager;

use AppBundle\Repository\Yarm\EntityRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class Yarm implements YarmInterface
{
    private $yarmDirectory;

    const SCHEMAS_DIRECTORY = '/schemas/';
    const DATA_DIRECTORY = '/data/';

    const EXTENSION_FILE = 'yml';

    public function __construct($yarmDirectory)
    {
        $this->yarmDirectory = $yarmDirectory;
    }

    public function persist($entity)
    {
        $class = get_class($entity);
        $entities = $this->getData($class);
        $id = count($entities) + 1;
        $setId = (function($id) {
            $this->id = $id;
        })->bindTo($entity, $entity);
        $setId($id);
        $entities[$id] = json_encode($entity);
        $this->setData($class, $entities);
    }

    public function remove($entity)
    {
        $class = get_class($entity);
        $entities = $this->getData($class);
        $entities[$entity->getId()] = null;
        $this->setData($class, $entities);
    }

    /**
     * Get repository.
     *
     * @param string $class
     * @return EntityRepository
     */
    public function getRepository($class)
    {
        $repositoryClass = (($this->getSchema($class))['repository_class']);

        return new $repositoryClass($this, $class);
    }


    public function createTables()
    {
        $fileSystem = new Filesystem();

        foreach ($this->getSchemas() as $schema) {
            $entityName = Yaml::parseFile($schema)['entity_name'];
            $schema = $this->getDataFile($entityName);
            if (!$fileSystem->exists($schema)) {
                $fileSystem->dumpFile($schema, Yaml::dump([]));
            }
        }
    }

    private function getSchemas()
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->getSchemaDirectory())) {
            $filesystem->mkdir($this->getSchemaDirectory());
        }
        $finder = new Finder();
        $finder->files()->in($this->getSchemaDirectory());

        return $finder;
    }

    private function getSchema($class)
    {
        return Yaml::parseFile($this->getSchemaFile($class));
    }

    private function getSchemaFile($class)
    {
        $class = (new \ReflectionClass($class))->getShortName();
        return $this->getSchemaDirectory() . $class . '.' . self::EXTENSION_FILE;
    }

    private function getSchemaDirectory()
    {
        return $this->yarmDirectory . self::SCHEMAS_DIRECTORY;
    }

    private function getDataDirectory()
    {
        return $this->yarmDirectory . self::DATA_DIRECTORY;
    }

    private function getDataFile($class)
    {
        $class = (new \ReflectionClass($class))->getShortName();
        return $this->getDataDirectory() . $class . '.' . self::EXTENSION_FILE;
    }

    public function getData($class)
    {
        return Yaml::parseFile($this->getDataFile($class));
    }

    private function setData($class, $data)
    {
        return file_put_contents($this->getDataFile($class), Yaml::dump($data));
    }

    public function createSchema($class)
    {
        file_put_contents($this->getSchemaFile($class), Yaml::dump(['entity_name' => $class]));
    }
}

