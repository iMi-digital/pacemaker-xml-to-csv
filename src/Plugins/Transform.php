<?php

/**
 * TechDivision\Import\Plugins\CreateOkFilesPlugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */

namespace IMI\PacemakerImport\Converter\Xml\Plugins;

use DOMDocument;
use TechDivision\Import\Adapter\ExportAdapterInterface;
use TechDivision\Import\ApplicationInterface;
use TechDivision\Import\Plugins\AbstractPlugin;
use TechDivision\Import\Plugins\ExportableTrait;
use TechDivision\Import\Subjects\FileWriter\FileWriterFactoryInterface;
use XSLTProcessor;

/**
 * Plugin that creates .OK files for the .CSV files found in the actual source directory.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2020 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */
class Transform extends AbstractPlugin
{
    use ExportableTrait;

    /**
     * The file writer factory instance.
     *
     * @var \TechDivision\Import\Subjects\FileWriter\FileWriterFactoryInterface
     */
    protected $fileWriterFactory;

    /**
     *
     * @param \TechDivision\Import\ApplicationInterface $application The application instance
     * @param \TechDivision\Import\Subjects\FileWriter\FileWriterFactoryInterface $fileWriterFactory The file writer factory instance
     */
    public function __construct(
        ApplicationInterface $application,
        FileWriterFactoryInterface $fileWriterFactory,
        ExportAdapterInterface $exportAdapter
    )
    {

        // set the passed file writer factory instance
        $this->fileWriterFactory = $fileWriterFactory;

        // set the export adapter
        $this->exportAdapter = $exportAdapter;

        // pass the application to the parent constructor
        parent::__construct($application);
    }

    /**
     * Return's the file writer factory instance.
     *
     * @return \TechDivision\Import\Subjects\FileWriter\FileWriterFactoryInterface The file writer factory instance
     */
    protected function getFileWriterFactory(): FileWriterFactoryInterface
    {
        return $this->fileWriterFactory;
    }

    /**
     * Process the plugin functionality.
     *
     * @return void
     * @throws \Exception Is thrown, if the plugin can not be processed
     */
    public function process()
    {
        $this->export(date('20200101'), 1); //TODO: maybe use another timestamp
    }

    public function getArtefacts()
    {
        $input = $this->getPluginConfiguration()->getParam('input-xml');
        $transformation = $this->getPluginConfiguration()->getParam('transformation-xslt');

        echo "Loading..." . PHP_EOL;

        $xsl = new DOMDocument();
        $xsl->load($transformation);

        $xml = new DOMDocument();
        $xml->load($this->getSourceDir() . '/' . $input);

        $proc = new XSLTProcessor();

        $proc->importStyleSheet($xsl);

        echo "Transforming..." . PHP_EOL;

        $out = $proc->transformToXML($xml);

        echo "Loading to SimpleXML..." . PHP_EOL;
        $outXml = simplexml_load_string($out);

        echo "Extracing headers..." . PHP_EOL;
        $rowTemplate = [];

        foreach ($outXml->Item as $item) {
            foreach ($item->children() as $field) {
                $rowTemplate[$field->getName()] = '';
            }
        }

        $records = [];
        foreach ($outXml->Item as $item) {
            $record = $rowTemplate;
            foreach ($item->children() as $key => $value) {
                $record[$key] = (string) $value;
            }
            $records[] = $record;
        }
        return [
            $this->getPluginConfiguration()->getParam('exportable-artefact-type') => [ $records ]
        ];

    }

    public function resetArtefacts()
    {
        // TODO: Implement resetArtefacts() method.
    }
}
