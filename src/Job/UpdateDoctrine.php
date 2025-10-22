<?php declare(strict_types=1);

namespace RolesManager\Job;

use Omeka\Job\AbstractJob;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;

class UpdateDoctrine extends AbstractJob
{

    public function perform(): void
    {

        $moduleName = 'RolesManager';
        $modulePath = implode(DIRECTORY_SEPARATOR, [OMEKA_PATH, 'modules', $moduleName]);
        $entityManager = $this->serviceLocator->get('Omeka\EntityManager');
        $classMetadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $moduleClassMetadatas = [];
        $moduleTables = [];
        foreach ($classMetadatas as $classMetadata) {
            $fileName = $classMetadata->getReflectionClass()->getFileName();
            if (strncmp($fileName, $modulePath, strlen($modulePath)) === 0) {
                $moduleClassMetadatas[] = $classMetadata;
        
                // Gather "main" table and any owned many-to-many tables for each file
                $moduleTables[] = $classMetadata->getTableName();
                foreach ($classMetadata->associationMappings as $mapping) {
                    if ($mapping['type'] == ClassMetadata::MANY_TO_MANY && $mapping['isOwningSide']) {
                        $moduleTables[] = $mapping['joinTable']['name'];
                    }
                }
            }
        }

        if (!$moduleClassMetadatas) {
            echo "There are no database entities for the $moduleName module.";
            exit;
        }

        $dest = implode(DIRECTORY_SEPARATOR, [$modulePath, 'data', 'doctrine-proxies']);
        if (!file_exists($dest)) {
            if (!mkdir($dest, 0755, true)) {
                echo "Couldn't create a directory at $dest!\n";
                exit(1);
            }
        }

        if (!is_dir($dest)) {
            echo "$dest exists, but isn't a directory!\n";
            exit(1);
        }


        $entityManager->getProxyFactory()->generateProxyClasses($moduleClassMetadatas, $dest);

        echo "Proxies created at $dest.\n";

        $schemaTool = new SchemaTool($entityManager);
        $schema = $schemaTool->getSchemaFromMetadata($classMetadatas);

        // We have to give the SchemaTool the core entities also, or it won't create
        // references from the module entities to the core entities. To get only
        // the SQL we actually want, we drop all the tables that aren't coming from
        // the module before we get the SQL.
        foreach ($schema->getTables() as $table) {
            foreach ($moduleTables as $tableName) {
                if ($table->getName() === $tableName) {
                    continue 2;
                }
            }
            $schema->dropTable($table->getName());
        }
        $statements = $schema->toSql($entityManager->getConnection()->getDatabasePlatform());
        $statements[] = '';

        echo "SQL:\n\n" . implode(';' . PHP_EOL, $statements);

    }

}
