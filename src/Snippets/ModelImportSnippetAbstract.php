<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Gems\Html;
use Mezzio\Session\SessionInterface;
use Zalt\Base\RequestInfo;
use Zalt\Base\TranslatorInterface;
use Zalt\File\File;
use Zalt\Html\HtmlElement;
use Zalt\Html\TableElement;
use Zalt\Late\Late;
use Zalt\Late\Repeatable;
use Zalt\Late\RepeatableInterface;
use Zalt\Message\MessengerInterface;
use Zalt\Model\Bridge\FormBridgeInterface;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\Model\Data\FullDataInterface;
use Zalt\Model\Exception\ModelException;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\MetaModelLoader;
use Zalt\Model\Ra\SessionModel;
use Zalt\Model\Translator\ImportProcessor;
use Zalt\Model\Translator\ImportProcessorInterface;
use Zalt\Model\Translator\ModelTranslatorInterface;
use Zalt\Model\Translator\StraightTranslator;
use Zalt\Snippets\ModelBridge\TableBridge;
use Zalt\Snippets\ModelBridge\ZendFormBridge;
use Zalt\SnippetsLoader\SnippetOptions;

/**
 * Generic import wizard.
 *
 * Set the targetModel (directly or through $this->model) and the
 * importTranslators and it should work.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.3
 */
abstract class ModelImportSnippetAbstract extends \Zalt\Snippets\WizardFormSnippetAbstract
{
    /**
     * Contains the errors generated so far
     *
     * @var array
     */
    private $_errors = array();

    /**
     *
     * @var array|bool
     */
    protected $_translatorDescriptions = [];

    /**
     * Name of the default import translator
     *
     * @var string
     */
    protected $defaultImportTranslator = 'straight';

    /**
     * Css class for messages and errors
     *
     * @var string
     */
    protected $errorClass = 'errors';

    /**
     * The final directory when the data could not be imported.
     *
     * If empty the file is thrown away after the failure.
     *
     * Used only when importer is not set
     *
     * @var string
     */
    public $failureDirectory;

    /**
     * True when content is supplied from a file
     *
     * @var boolean
     */
    protected $fileMode = true;

    /**
     * Class for import fields table
     *
     * @var string
     */
    protected $formatBoxClass;

    protected ImportProcessorInterface $importProcessor;

    /**
     *
     * @var SessionModel $importModel
     */
    protected SessionModel $importModel;

    /**
     * The filename minus the extension for long term storage.
     *
     * If empty the file is not kept.
     *
     * Used only when importer is not set
     *
     * @var string
     */
    protected $longtermFilename;

    /**
     *
     * @var ?\Zalt\Model\Data\FullDataInterface
     */
    protected ?FullDataInterface $model;

    protected string $stepsHeader = 'Data import. Step %d of %d.';

    /**
     * Model to read import
     *
     * @var null|\Zalt\Model\Data\DataReaderInterface
     */
    protected $sourceModel = null;

    /**
     * The final directory when the data was successfully imported.
     *
     * If empty the file is thrown away after the import.
     *
     * Used only when importer is not set
     *
     * @var string
     */
    public $successDirectory;

    /**
     * Model to save import into
     *
     * Required, can be set by passing a model to $this->model
     *
     * @var null|\Zalt\Model\Data\FullDataInterface
     */
    protected $targetModel = null;

    /**
     * @var string[] Names of possible getImportTranslator() calls
     */
    protected $translatorNames = ['straight'];

    /**
     * The filepath for temporary files
     *
     * @var string
     */
    public $tempDirectory;

    public function __construct(
        SnippetOptions $snippetOptions,
        RequestInfo $requestInfo,
        TranslatorInterface $translate,
        MessengerInterface $messenger,
        protected readonly MetaModelLoader $metaModelLoader,
        protected readonly SessionInterface $session,
    )
    {
        parent::__construct($snippetOptions, $requestInfo, $translate, $messenger);

        if (! $this->targetModel instanceof FullDataInterface) {
            if ($this->model instanceof FullDataInterface) {
                $this->targetModel = $this->model;
            }
        }

        // Cleanup any references to model to avoid confusion
        $this->model = null;

        $this->init();

        $this->createModel();
    }

    /**
     * Helper function to sort translator table by model order
     *
     * @param string $a
     * @param string $b
     * @return int
     */
    protected function _sortTranslatorTable($a, $b)
    {
        $metaModel = $this->targetModel->getMetaModel();

        $ao = $metaModel->getOrder($a);
        $bo = $metaModel->getOrder($b);

        if ($ao < $bo) {
            return -1;
        } elseif ($ao > $bo) {
            return 1;
        } else {
            return 0;
        }
    }

    protected function addImportModelSettings(SessionModel $model): void
    {
        $metaModel = $model->getMetaModel();

        $metaModel->set('trans', [
            'label' => $this->_('Import definition'),
            'default' => $this->defaultImportTranslator,
            'description' => $this->_('See import field definitions table'),
            'multiOptions' => $this->getTranslatorDescriptions(),
            'required' => true,
            'elementClass' => 'Radio',
            'separator' => ' ',
        ]);

        $metaModel->set('mode', [
            'label' => $this->_('Choose work mode'),
            'default' => 'file',
            'multiOptions' => array(
                'file'     => $this->_('Upload a file'),
                'textarea' => $this->_('Copy and paste into a text field'),
            ),
            'required' => true,
            'elementClass' => 'Radio',
            'separator' => ' ',
        ]);

        $metaModel->set('file', [
            'label' => $this->_('Import file'),
            'count' => 1,
            'elementClass' => 'File',
            'extension' => 'csv,txt,xml',
            'required' => true,
        ]);

        if ($this->tempDirectory) {
            File::ensureDir($this->tempDirectory);
            $metaModel->set('file', ['destination' =>  $this->tempDirectory]);
        }

        // Storage for local copy of the file, kept through process
        $metaModel->set('import_id');

        $metaModel->set('content', [
            'label' => $this->_('Import text - user header line - separate fields using tabs'),
            'description' => $this->_('Empty fields remove any existing values. Add a field only when used.'),
            'cols' => 120,
            'elementClass' => 'Textarea',
        ]);
    }

    /**
     * Add the elements from the model to the bridge for the current step
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param \Zalt\Model\Data\FullDataInterface $model
     */
    protected function addStep1(FormBridgeInterface $bridge, FullDataInterface $model)
    {
        $this->addItems($bridge, ['trans', 'mode']);
    }

    /**
     * Add the elements from the model to the bridge for the current step
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param FullDataInterface $model
     */
    protected function addStep2(FormBridgeInterface $bridge, FullDataInterface $model)
    {
        $translator = $this->getCurrentImportTranslator();
//        if ($translator instanceof ModelTranslatorInterface) {
//            $element = $bridge->getForm()->createElement('html', 'trans_header');
//            $element->span($this->_('Choosen import definition: '));
//            $element->strong($translator->getDescription());
//            $element->setDecorators(array('Tooltip', 'ViewHelper'));
//            $bridge->addElement($element);
//        }

        if ($this->fileMode) {
            $this->addItems($bridge, ['file']);

            $element = $bridge->getForm()->getElement('file');

            if ($element instanceof \Zend_Form_Element_File) {
                // Now add the rename filter, the localfile is known only once after loadFormData() has run
                // @phpstan-ignore-next-line
                $element->addFilter(new \Zend_Filter_File_Rename([
                    'target'    => $this->getTempFileName(),
                    'overwrite' => true,
                    ]));

                // Download the data (no test for post, step 2 is always a post)
                if ($element->isValid('') && $element->getFileName()) {
                    // Now the filename is still set to the upload filename.
                    $this->session->set('extension', pathinfo($element->getFileName(), PATHINFO_EXTENSION));
                    // \Zalt\EchoOut\EchoOut::track($element->getFileName(), $element->getFileSize());
                    if (!$element->receive()) {
                        throw new ModelException(sprintf(
                            $this->_("Error retrieving file '%s'."),
                            $element->getFileName()
                            ));
                    }
                }
            }
        } else {
            $this->addItems($bridge, ['content']);

            $this->session->set('extension', 'txt');

            if (isset($this->formData['content']) && $this->formData['content']) {
                // dump($_POST, $this->formData['content']);
                file_put_contents($this->getTempFileName(), $this->formData['content']);
            } else {
                if (file_exists($this->getTempFileName())) {
                    $content = file_get_contents($this->getTempFileName());
                } else {
                    $content = '';
                }

                if (!$content) {
                    // Add a default content if empty
                    $fields = array_filter(array_keys($translator->getFieldsTranslations()), 'is_string');

                    $content = implode("\t", $fields) . "\n" .
                        str_repeat("\t", count($fields) - 1) . "\n";

                }
                $this->formData['content'] = $content;
            }
        }
    }

    /**
     * Add the elements from the model to the bridge for the current step
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param \Zalt\Model\Data\FullDataInterface $model
     */
    protected function addStep3(FormBridgeInterface $bridge, FullDataInterface $model)
    {
        if ($this->loadSourceModel()) {
            $this->displayHeader($bridge, $this->_('Upload successful!'));
            $this->displayErrors($bridge, $this->_('Check the input visually.'));

            // \Zalt\EchoOut\EchoOut::track($this->sourceModel->load());

            $element = $bridge->getForm()->createElement('html', 'importdisplay');

            $data     = $this->sourceModel->load();
            $repeater = Late::repeat(new \LimitIterator(new \ArrayIterator($data), 0, 20));
            $table    = new TableElement($repeater, array('class' => $this->formatBoxClass));

            foreach ($this->sourceModel->getMetaModel()->getItemsOrdered() as $name) {
                $table->addColumn($repeater->$name, $name);
            }

            // Extra div for CSS settings
            $element->setValue(new HtmlElement('div', $table, array('class' => $this->formatBoxClass)));
            if ($bridge instanceof ZendFormBridge) {
                $bridge->addElement($element);
            }
        } else {
            $this->displayHeader($bridge, $this->_('Upload error!'));
            $this->displayErrors($bridge);

            $this->nextDisabled = true;
        }
    }

    /**
     * Add the elements from the model to the bridge for the current step
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param \Zalt\Model\Data\FullDataInterface $model
     */
    protected function addStep4(FormBridgeInterface $bridge, FullDataInterface $model)
    {
        if ($this->loadSourceModel()) {
            $this->displayHeader($bridge, $this->_('Checking results....'));

            // \Zalt\EchoOut\EchoOut::track($this->sourceModel->load());

            $element = $bridge->getForm()->createElement('html', 'importdisplay');

            $input      = $this->sourceModel->load();
            $translator = $this->getImportTranslator();

            $output = $translator->translateImport($input);
            if ($translator->hasErrors()) {
                $this->displayErrors($bridge, $translator->getErrors());
                $this->nextDisabled = true;
            } else {
                $this->displayErrors($bridge, $this->_('Check the result output.'));
            }

            // dump($output);
            $repeater = Late::repeat(new \LimitIterator(new \ArrayIterator($output), 0, 40));
            $table    = $this->getTargetModelTable($translator->getFieldsTranslations(), $repeater);

            // Extra div for CSS settings
            $element->setValue(new HtmlElement('div', $table, array('class' => $this->formatBoxClass)));
            if ($bridge instanceof ZendFormBridge) {
                $bridge->addElement($element);
            }
        } else {
            $this->displayHeader($bridge, $this->_('Check error!'));
            $this->displayErrors($bridge);

            $this->nextDisabled = true;
        }
    }

    /**
     * Add the elements from the model to the bridge for the current step
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param \Zalt\Model\Data\FullDataInterface $model
     */
    protected function addStep5(FormBridgeInterface $bridge, FullDataInterface $model)
    {
        if ($this->loadSourceModel()) {
            $this->displayHeader($bridge, $this->_('Checking results....'));

            $element = $bridge->getForm()->createElement('html', 'importdisplay');

            $input      = $this->sourceModel->load();
            $translator = $this->getImportTranslator();

            $output = $translator->translateImport($input);
            if ($translator->hasErrors()) {
                $html = $this->getHtmlSequence();
                $html->h4($this->_('Import FAILED'));
                $html->ul($translator->getErrors());

                // $this->displayErrors($bridge, $translator->getErrors());
            } else {
                // file_put_contents('data/logs/echo.txt', __CLASS__ . '->' . __FUNCTION__ . '(' . __LINE__ . '): ' .  print_r($output, true) . "\n", FILE_APPEND);
                $result = [];
                foreach ($output as $row) {
                    $result[] = $this->targetModel->save($row);
                }
                // file_put_contents('data/logs/echo.txt', __CLASS__ . '->' . __FUNCTION__ . '(' . __LINE__ . '): ' .  print_r($result, true) . "\n", FILE_APPEND);
                $msg = $this->getItemsSavedMessage(count($result));
                $this->displayErrors($bridge, $msg);

                $html = $this->getHtmlSequence();
                $html->h4($this->_('Import succesfull'));
                $html->pInfo($msg);
            }

            // Extra div for CSS settings
            $element->setValue(new HtmlElement('div', $html, array('class' => $this->formatBoxClass)));
            if ($bridge instanceof ZendFormBridge) {
                $bridge->addElement($element);
            }
        } else {
            $this->nextDisabled = true;

            $this->displayHeader($bridge, $this->_('Import error!'));
            $this->displayErrors($bridge);

            $this->nextDisabled = true;
        }


        return;
    }

    /**
     * Add the elements from the model to the bridge for the current step
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param \Zalt\Model\Data\DataReaderInterface $model
     * @param int $step The current step
     */
    protected function addStepElementsFor(FormBridgeInterface $bridge, DataReaderInterface $model, $step)
    {
        $this->displayHeader(
                $bridge,
                sprintf($this->stepsHeader, $step, $this->getStepCount()),
                'h1');

        if ($model instanceof FullDataInterface) {
            switch ($step) {
                case 0:
                case 1:
                    $this->addStep1($bridge, $model);
                    break;

                case 2:
                    $this->addStep2($bridge, $model);
                    break;

                case 3:
                    $this->addStep3($bridge, $model);
                    break;

                case 4:
                    $this->addStep4($bridge, $model);
                    break;

                default:
                    $this->addStep5($bridge, $model);
                    break;

            }
        }
        $this->beforeDisplayFor($step);
    }

    /**
     * Hook for after save
     *
     * @param HtmlElement $element Tetx element for display of messages
     * @return string a message about what has changed (and used in the form)
     */
    public function afterImport(HtmlElement $element)
    {
//        $imported = $batch->getCounter('imported');
//        $changed  = $batch->getCounter('changed');

//        $text = sprintf($this->plural('%d row imported.', '%d rows imported.', $imported), $imported) . ' ' .
//                sprintf($this->plural('%d row changed.', '%d rows changed.', $changed), $changed);

//        $this->addMessage($batch->getMessages(true));
//        $this->addMessage($text);

//        $element->pInfo($text);

//        return $text;
        return '';
    }

    /**
     * Overrule this function for any activities you want to take place
     * before the actual form is displayed.
     *
     * This means the form has been validated, step buttons where processed
     * and the current form will be the one displayed.
     *
     * @param int $step The current step
     */
    protected function beforeDisplayFor($step)
    {
        switch ($step) {
        case 1:
            $fieldInfo = $this->getTranslatorTable();
            break;

        case 2:
        case 3:
        case 4:
        case 5:
            if (isset($this->formData['trans']) && $this->formData['trans']) {
                $fieldInfo = $this->getTranslatorTable($this->formData['trans']);
                break;
            }

        default:
            $fieldInfo = null;
        }

        if ($fieldInfo) {
            // Slow
            //$table1 = \Zalt\Html\TableElement::createArray($fieldInfo, $this->_('Import field definitions'), true);
            //$table1->appendAttrib('class', $this->formatBoxClass);

            // Fast
            $table = \Zalt\Html\TableElement::table();
            $table->caption($this->_('Import field definitions'));
            $table->appendAttrib('class', $this->formatBoxClass);
            $repeater = new Repeatable($fieldInfo);
            $table->setRepeater($repeater);
            foreach (reset($fieldInfo) as $title => $element)
            {
                $table->addColumn($repeater->$title, $title);
            }

            $element = $this->_forms[$step]->createElement('Html', 'transtable');
            $element->setValue($table);

            $this->_forms[$step]->addElement($element);
        }
    }

    /**
     * Creates the model
     *
     * @return \Zalt\Model\Data\DataReaderInterface
     */
    protected function createModel(): DataReaderInterface
    {
        if (! isset($this->importModel)) {
            // @phpstan-ignore-next-line
            $this->importModel = $this->metaModelLoader->createModel(SessionModel::class, $this->session);
            // @phpstan-ignore-next-line
            $this->addImportModelSettings($this->importModel);
        }

        return $this->importModel;
    }

    /**
     * Display the errors
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param mixed $errors Errors to display
     */
    protected function displayErrors(FormBridgeInterface $bridge, $errors = null)
    {
        if (null === $errors) {
            $errors = $this->_errors;
        }

        if ($errors) {
            $element = $bridge->getForm()->createElement('html', 'errorlist');

            $element->ul($errors, array('class' => $this->errorClass));

            if ($bridge instanceof ZendFormBridge) {
                $bridge->addElement($element);
            }
        }
    }

    /**
     * Display a header
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param mixed $header Header content
     * @param string $tagName
     */
    protected function displayHeader(FormBridgeInterface $bridge, $header, $tagName = 'h2')
    {
        $form = $bridge->getForm();
        if ($form instanceof \Zend_Form) {
            $element = $form->getElement('step_header');
        } else {
            $element = null;
        }
        if (! $element) {
            $element = $form->createElement('Html', 'step_header');
            if ($bridge instanceof ZendFormBridge) {
                $bridge->addElement($element);
            }
        }
        $element->$tagName($header);

    }

    /**
     * Try to get the current translator
     *
     * @return ModelTranslatorInterface|null Current or translator or null
     */
    protected function getCurrentImportTranslator(): ?ModelTranslatorInterface
    {
        if (! (isset($this->formData['trans']) && $this->formData['trans'])) {
            $this->_errors[] = $this->_('No import definition specified');
            return null;
        }

        $translator = $this->getImportTranslator($this->formData['trans']);

        // Store/set relevant variables
        $this->importProcessor->setImportTranslator($translator);

        if ($this->targetModel instanceof FullDataInterface) {
            $translator->setTargetModel($this->targetModel);
        }

        return $translator;
    }

    /**
     * @param string $name Optional identifier
     * @return ModelTranslatorInterface
     */
    protected function getImportTranslator(string $name = ''): ModelTranslatorInterface
    {
        $translator = $this->metaModelLoader->createTranslator(StraightTranslator::class);
        $translator->setTargetModel($this->targetModel);
        $translator->setDescription('Straight');

        return $translator;
    }

    protected function getItemsSavedMessage(int $count)
    {
        return sprintf($this->plural('%d item saved!', '%d items saved!', $count), $count);
    }

    /**
     * The number of steps in this form
     *
     * @return int
     */
    protected function getStepCount()
    {
        return 5;
    }

    protected function getTargetModelTable(array $fields, RepeatableInterface $repeatable)
    {
        $metaModel = $this->targetModel->getMetaModel();
        /**
         * @var TableBridge $bridge
         */
        $bridge = $this->targetModel->getBridgeFor('table');
        $bridge->setRepeater($repeatable);
        $table  = $bridge->getTable();
        $table->getOnEmpty()->raw('&hellip;');

        foreach($fields as $name) {
            if ($metaModel->has($name, 'label')) {
                $bridge->add($name, $metaModel->get($name, 'label'));
            }
        }

        return $table;
    }

    protected function getTempFileName(): string
    {
        if ($this->session->has('localfile')) {
            return $this->session->get('localfile');
        }
        $file = File::createTemporaryIn(
            $this->tempDirectory,
            $this->requestInfo->getCurrentController() . '_'
        );
        $this->session->set('localfile', $file);

        return $file;
    }

    /**
     * Get the descriptions of the translators
     *
     * @return array key -> description
     */
    protected function getTranslatorDescriptions()
    {
        if (! $this->_translatorDescriptions) {
            $results = [];
            foreach ($this->getTranslatorNames() as $key) {
                $translator = $this->getImportTranslator($key);

                $results[$key] = $translator->getDescription();
            }

            asort($results);

            $this->_translatorDescriptions = $results;
        }

        return $this->_translatorDescriptions;
    }

    /**
     * Get the available translator keys
     *
     * @return array key
     */
    protected function getTranslatorNames(): array
    {
        return $this->translatorNames;
    }

    /**
     * Get the descriptions of the translators
     *
     * @param mixed $forInput A single translator, an array of translators or all translators if null;
     * @return array key -> description
     */
    protected function getTranslatorTable($forInput = null)
    {
//        if (! $this->targetModel) {
//            return [];
//        }

        if (null === $forInput) {
            $for = $this->getTranslatorDescriptions();
        } elseif (is_string($forInput)) {
            $descriptors = $this->getTranslatorDescriptions();
            if (! isset($descriptors[$forInput])) {
                throw new \Zend_Exception("Unknown translator $forInput passed to " . __CLASS__ . '->' . __FUNCTION__ . '()');
            }
            $for = [$forInput => $descriptors[$forInput]];
        } else {
            return [];
        }

        $requiredKey = $this->_('Required');
        $metaModel   = $this->targetModel->getMetaModel();
        $minimal     = array($requiredKey => ' '); // Array for making sure all fields are there
        $results     = array_fill_keys($metaModel->getItemsOrdered(), array());
        $transCount  = count($for);

        foreach ($for as $transKey => $transName) {
            $translator = $this->getImportTranslator($transKey);

            if ($translator instanceof ModelTranslatorInterface) {

                $translator->setTargetModel($this->targetModel);
                $translations = $translator->getFieldsTranslations();
                $requireds    = $translator->getRequiredFields();

                $minimal[$transName] = ' ';

                foreach ($translations as $source => $target) {
                    // Skip numeric fields
                    if (! is_int($source)) {
                        $required = isset($requireds[$source]);

                        // Add required row
                        $results[$target][$requiredKey][$transName] = $required;

                        if ($required) {
                            $results[$target][$transName] = new \Zalt\Html\HtmlElement('strong', $source);
                        } else {
                            $results[$target][$transName] = $source;
                        }
                    }
                }
            }
        }

        $output = array();
        foreach ($results as $name => $resultRow) {
            if (count($resultRow) > 1) {
                // Always first
                $requireds = count(array_filter($resultRow[$requiredKey]));
                if ($requireds) {
                    // $resultRow[$requiredKey] = ($requireds == $transCount ? $this->_('Yes') : $this->_('For bold'));
                    $resultRow[$requiredKey] = $this->_('For bold');
                } else {
                    $resultRow[$requiredKey] = ' ';
                }

                if ($metaModel->has($name, 'label')) {
                    $label = $metaModel->get($name, 'label');
                } else {
                    $label = $name;
                }

                // $field = $this->_targetModel->get($name, 'type', 'maxlength', 'label', 'required');
                switch ($metaModel->get($name, 'type')) {
                    case MetaModelInterface::TYPE_NOVALUE:
                        unset($results[$name]);
                        continue 2;

                    case MetaModelInterface::TYPE_NUMERIC:
                        $maxlength = $metaModel->get($name, 'maxlength');
                        if ($maxlength) {
                            $decimals = $metaModel->get($name, 'decimals');
                            if ($decimals) {
                                $type = sprintf($this->_('A number of length %d, with a precision of %d digits after the period.'), $maxlength, $decimals);
                            } else {
                                $type = sprintf($this->_('A whole number of length %d.'), $maxlength);
                            }
                        } else {
                            $type = $this->_('A numeric value');
                        }
                        break;

                    case MetaModelInterface::TYPE_DATE:
                        $type = $this->_('Date value using ISO 8601: yyyy-mm-dd');
                        break;

                    case MetaModelInterface::TYPE_DATETIME:
                        $type = $this->_('Datetime value using ISO 8601: yyyy-mm-ddThh:mm:ss[+-hh:mm]');
                        break;

                    case MetaModelInterface::TYPE_TIME:
                        $type = $this->_('Time value using ISO 8601: hh:mm:ss[+-hh:mm]');
                        break;

                    default:
                        $maxlength = $metaModel->get($name, 'maxlength');
                        $minlength = $metaModel->get($name, 'minlength');
                        if ($maxlength && $minlength) {
                            $type = sprintf($this->plural(
                                    'Text, between %d and %d character',
                                    'Text, between %d and %d characters',
                                    $maxlength), $minlength, $maxlength);
                        } elseif ($maxlength) {
                            $type = sprintf($this->plural(
                                    'Text, %d character',
                                    'Text, %d characters',
                                    $maxlength), $maxlength);
                        } elseif ($minlength) {
                            $type = sprintf($this->plural(
                                    'Text, at least %d character',
                                    'Text, at least %d characters',
                                    $minlength), $minlength);
                        } else {
                            $type = $this->_('Text');
                        }
                        break;

                }
                $options = $metaModel->get($name, 'multiOptions');
                if ($options) {
                    $cutoff      = 6;
                    $i           = 0;
                    $optionDescr = '';
                    $separator   = $this->_(', ');

                    if (is_callable($options)) {
                        $options = call_user_func($options);
                    }
                    foreach($options as $key => $value) {
                        $optionDescr .= $separator . $key;
                        $i++;
                        if ($key != (string) $value) {
                            $optionDescr .= sprintf($this->_(', %s'), $value);
                            $i++;
                        }
                        if ($i > $cutoff) {
                            break;
                        }
                    }
                    $optionDescr = substr($optionDescr, strlen($separator));

                    if ($i < $cutoff) {
                        // $type .= $this->_('; one of: ') . implode($this->_(', '), array_keys($options));
                        $type .= sprintf($this->_('; one of: %s'), $optionDescr);
                    } else {
                        $type .= sprintf($this->_('; e.g. one of: %s, ...'), $optionDescr);
                    }
                }

                $typeDescr = $metaModel->get($name, 'import_descr');
                if ($typeDescr) {
                    $type .= $this->_('; ') . $typeDescr;
                }

                $resultRow[$this->_('Field description')] = (string) $label ? $label : $this->_('<<no description>>');
                $resultRow[$this->_('Content')]           = $type;

                // Make sure all fields are there
                $resultRow = array_merge($minimal, $resultRow);

                $output[$name] = $resultRow;
            }
        }
        uksort($output, array($this, '_sortTranslatorTable'));

        return $output;
    }

    public function hasHtmlOutput(): bool
    {
        return parent::hasHtmlOutput();
    }

    /**
     * @return void Utility function to check and set variables
     */
    protected function init(): void
    {
        $this->importProcessor = new ImportProcessor($this->metaModelLoader);
    }

    /**
     * Initialize the _items variable to hold all items from the model
     */
    protected function initItems(MetaModelInterface $metaModel)
    {
        parent::initItems($metaModel);

        // Remove content as big text slab will slow things down and storage is locally in file
        $i = array_search('content', $this->_items);
        if (false !== $i) {
            unset($this->_items[$i]);
        }
    }

    protected function getDefaultFormValues(): array
    {
        return [
            'trans' => $this->defaultImportTranslator,
            'mode' => 'file',
        ];
    }

    /**
     * Hook that loads the form data from $_POST or the model
     *
     * Or from whatever other source you specify here.
     */
    protected function loadFormData(): array
    {
        if ($this->isPost()) {
            $this->formData = $this->requestInfo->getRequestPostParams() + $this->formData;
        } else {
            foreach ($this->importModel->getMetaModel()->getColNames('default') as $name) {
                if (!(isset($this->formData[$name]) && $this->formData[$name])) {
                    $this->formData[$name] = $this->importModel->getMetaModel()->get($name, 'default');
                }
            }
        }
        if (! (isset($this->formData['import_id']) && $this->formData['import_id'])) {
            $this->formData['import_id'] = mt_rand(10000,99999) . time();
        }

        // Must always exists
        $this->fileMode = 'file' === $this->formData['mode'];

        // Set the translator
        $translator = $this->getCurrentImportTranslator();
        if ($translator && ! (isset($this->formData['content']) || $this->fileMode)) {
            $fields = $translator->getFieldsTranslations();
            $this->formData['content'] = implode("\t", array_keys($fields)) . "\n" .
                str_repeat("\t", count($fields)) . "\n" ;
        }
        if ($translator instanceof ModelTranslatorInterface) {
            $this->importProcessor->setImportTranslator($translator);
        }

        $this->formData = $this->loadCsrfData() + $this->formData;
        // dump($_POST, $this->formData);

        return $this->formData;
    }

    /**
     * (Try to) load the source model
     *
     * @return boolean True if successful
     */
    protected function loadSourceModel()
    {
        try {
            // Make sure the translator is loaded and activated
            $this->getCurrentImportTranslator();

            if (! $this->sourceModel) {
                $this->sourceModel = $this->importProcessor->setSourceFile($this->getTempFileName(), $this->session->get('extension'));
            }
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
        }

        return $this->sourceModel instanceof DataReaderInterface;
    }

    /**
     * Hook containing the actual save code.
     *
     * Call's afterSave() for user interaction.
     *
     * @see afterSave()
     */
    protected function saveData(): int
    {
        // do nothing, save occurs in batch
        return 0;
    }

    protected function setAfterSaveRoute()
    {
        $file = $this->getTempFileName();
        if ($file && file_exists($file)) {
            // Now is a good moment to remove the temporary file
            @unlink($file);
        }

        parent::setAfterSaveRoute();
    }
}
