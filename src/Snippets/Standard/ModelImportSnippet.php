<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets\Standard;

use Mezzio\Session\SessionInterface;
use MUtil\Model\Importer;
use MUtil\Task\TaskBatch;
use Zalt\Base\RequestInfo;
use Zalt\Base\TranslatorInterface;
use Zalt\File\File;
use Zalt\Html\HtmlElement;
use Zalt\Snippets\ModelBridge\ZendFormBridge;
use Zalt\Late\Late;
use Zalt\Late\Repeatable;
use Zalt\Message\MessengerInterface;
use Zalt\Model\Bridge\FormBridgeInterface;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\Model\Data\FullDataInterface;
use Zalt\Model\Exception\ModelException;
use Zalt\Model\Exception\ModelTranslatorException;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\MetaModelLoader;
use Zalt\Model\Ra\SessionModel;
use Zalt\Model\Translator\ModelTranslatorInterface;
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
abstract class ModelImportSnippet extends \Zalt\Snippets\WizardFormSnippetAbstract
{
    /**
     * Contains the errors generated so far
     *
     * @var array
     */
    private $_errors = array();

    /**
     *
     * @var array
     */
    protected $_translatorDescriptions;

    /**
     * Array key of the default import translator
     *
     * @var string
     */
    protected $defaultImportTranslator;

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

    protected $importer;

    /**
     *
     * @var SessionModel $importModel
     */
    protected SessionModel $importModel;

    /**
     * Required, an array of one or more translators
     *
     * @var array of ModelTranslatorInterface objects
     */
    protected $importTranslators;

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
    protected ? FullDataInterface $model;

    /**
     * Model to read import
     *
     * @var \Zalt\Model\Data\FullDataInterface
     */
    protected $sourceModel;

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
     * @var \Zalt\Model\Data\FullDataInterface
     */
    protected $targetModel;

    /**
     * The filepath for temporary files
     *
     * @var string
     */
    public $tempDirectory;

    /**
     * Use csrf token on form for protection against Cross Site Request Forgery
     *
     * @var boolean
     */
    public $useCsrf = false;

    /**
     *
     * @var \Zend_View
     */
    public $view;

    public function __construct(
        SnippetOptions $snippetOptions,
        RequestInfo $requestInfo,
        TranslatorInterface $translate,
        MessengerInterface $messenger,
        protected readonly MetaModelLoader $metaModelLoader,
        protected SessionInterface $session,
    )
    {
        parent::__construct($snippetOptions, $requestInfo, $translate, $messenger);
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
        $translator = $this->getImportTranslator();
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
                $element->addFilter(new \Zend_Filter_File_Rename(array(
                    'target'    => $this->session->get('localfile'),
                    'overwrite' => true
                    )));

                // Download the data (no test for post, step 2 is always a post)
                if ($element->isValid(null) && $element->getFileName()) {
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
                file_put_contents($this->session->get('localfile'), $this->formData['content']);
            } else {
                if (filesize($this->session->get('localfile'))) {
                    $content = file_get_contents($this->session->get('localfile'));
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

            $repeater = Late::repeat(new \LimitIterator(new \ArrayIterator($this->sourceModel->load()), 0, 20));
            $table    = new \Zalt\Html\TableElement($repeater, array('class' => $this->formatBoxClass));

            foreach ($this->sourceModel->getMetaModel()->getItemsOrdered() as $name) {
                $table->addColumn($repeater->$name, $name);
            }

            // Extra div for CSS settings
            $element->setValue(new \Zalt\Html\HtmlElement('div', $table, array('class' => $this->formatBoxClass)));
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
        $this->nextDisabled = true;

        if ($this->loadSourceModel()) {
            $form  = $bridge->getForm();
            $batch = $this->importer->getCheckWithImportBatches();

            $batch->setFormId($form->getId());
            $batch->autoStart = true;

            // \Zalt\Registry\Source::$verbose = true;
            if ($batch->run($this->requestInfo->getRequestQueryParams())) {
                exit;
            }

            $element = $form->createElement('html', $batch->getId());

            if ($batch->isFinished()) {
                $this->nextDisabled = $batch->getCounter('import_errors');
                $batch->autoStart   = false;

                $this->addMessage($batch->getMessages(true));
                if ($this->nextDisabled) {
                    $element->pInfo($this->_('Import errors found, import is not allowed.'));
                } else {
                    $element->pInfo($this->_('Check was successfull, import can start.'));
                }

            } else {
                $iter = $batch->getSessionVariable('iterator');
                if ($iter instanceof \Iterator) {
                    // Restart the iterator
                    $iter->rewind();
                }
                $element->setValue($batch->getPanel($this->view, $batch->getProgressPercentage() . '%'));

            }
            $form->activateJQuery();
            $form->addElement($element);
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
        $this->nextDisabled = true;

        if ($this->loadSourceModel()) {
            $form  = $bridge->getForm();
            $batch = $this->importer->getImportOnlyBatch();

            $batch->setFormId($form->getId());
            $batch->autoStart = true;

            if ($batch->run($this->requestInfo->getRequestQueryParams())) {
                exit;
            }

            $element = $bridge->getForm()->createElement('html', $batch->getId());

            if ($batch->isFinished()) {
                $this->nextDisabled = $batch->getCounter('import_errors');
                $batch->autoStart   = false;

                $text = $this->afterImport($batch, $element);

            } else {
                $iter = $batch->getSessionVariable('iterator');
                if ($iter instanceof \Iterator) {
                    // Restart the iterator
                    $iter->rewind();
                }
                $element->setValue($batch->getPanel($this->view, $batch->getProgressPercentage() . '%'));

            }
            $form->activateJQuery();
            $form->addElement($element);
        } else {
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
                sprintf($this->_('Data import. Step %d of %d.'), $step, $this->getStepCount()),
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
    }

    /**
     * Hook for after save
     *
     * @param \MUtil\Task\TaskBatch $batch that was just executed
     * @param HtmlElement $element Tetx element for display of messages
     * @return string a message about what has changed (and used in the form)
     */
    public function afterImport(TaskBatch $batch, HtmlElement $element)
    {
        $imported = $batch->getCounter('imported');
        $changed  = $batch->getCounter('changed');

        $text = sprintf($this->plural('%d row imported.', '%d rows imported.', $imported), $imported) . ' ' .
                sprintf($this->plural('%d row changed.', '%d rows changed.', $changed), $changed);

        $this->addMessage($batch->getMessages(true));
        $this->addMessage($text);

        $element->pInfo($text);

        return $text;
    }

    /**
     * Called after the check that all required registry values
     * have been set correctly has run.
     *
     * @return void
     */
    public function afterRegistry()
    {
        if (! $this->importer instanceof Importer) {
            $this->importer = new Importer();

//            $source = new \Zalt\Registry\Source(get_object_vars($this));
//            $source->applySource($this->importer);
//            $this->importer->setRegistrySource($source);
        }
        if (! $this->targetModel instanceof FullDataInterface) {
            if ($this->model instanceof FullDataInterface) {
                $this->targetModel = $this->model;
            }
        }
        if ($this->targetModel instanceof FullDataInterface) {
            $this->importer->setTargetModel($this->targetModel);
        }
        if ($this->sourceModel instanceof FullDataInterface) {
            $this->importer->setSourceModel($this->sourceModel);
        }

        // Cleanup any references to model to avoid confusion
        $this->model = null;
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

            $element = $this->_form->createElement('html', 'transtable');
            $element->setValue($table);

            $this->_form->addElement($element);
        }
    }

    /**
     * Creates the model
     *
     * @return \Zalt\Model\Data\DataReaderInterface
     */
    protected function createModel(): DataReaderInterface
    {
        if (! $this->importModel instanceof FullDataInterface) {
            /**
             * @var SessionModel $model
             */
            $model = $this->metaModelLoader->createModel(SessionModel::class);
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

            $this->importModel = $model;
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
        $element = $bridge->getForm()->createElement('html', 'step_header');
        $element->$tagName($header);

        if ($bridge instanceof ZendFormBridge) {
            $bridge->addElement($element);
        }
    }

    /**
     * Try to get the current translator
     *
     * @return ModelTranslatorInterface|bool or false if none is current
     */
    protected function getImportTranslator()
    {
        if (! (isset($this->formData['trans']) && $this->formData['trans'])) {
            $this->_errors[] = $this->_('No import definition specified');
            return false;
        }

        if (! isset($this->importTranslators[$this->formData['trans']])) {
            $this->_errors[] = sprintf($this->_('Import definition %s does not exist.'), $this->formData['trans']);
            return false;
        }

        $translator = $this->importTranslators[$this->formData['trans']];
        if (! $translator instanceof ModelTranslatorInterface) {
            $this->_errors[] = sprintf($this->_('%s is not a valid import definition.'), $this->formData['trans']);
            return false;
        }

        // Store/set relevant variables
        if ($this->importer instanceof Importer) {
            $this->importer->setImportTranslator($translator);
        }
        if ($this->targetModel instanceof FullDataInterface) {
            $translator->setTargetModel($this->targetModel);
            if ($this->importer instanceof Importer) {
                $this->importer->setTargetModel($this->targetModel);
            }
        }

        return $translator;
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

    /**
     * Get the descriptions of the translators
     *
     * @return array key -> description
     */
    protected function getTranslatorDescriptions()
    {
        if (! $this->_translatorDescriptions) {
            $results = array();
            foreach ($this->importTranslators as $key => $translator) {
                if ($translator instanceof ModelTranslatorInterface) {
                    $results[$key] = $translator->getDescription();
                }
            }

            asort($results);

            $this->_translatorDescriptions = $results;
        }

        return $this->_translatorDescriptions;
    }

    /**
     * Get the descriptions of the translators
     *
     * @param mixed $forInput A single translator, an array of translators or all translators if null;
     * @return array key -> description
     */
    protected function getTranslatorTable($forInput = null)
    {
        if (! $this->targetModel) {
            return [];
        }

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
            if (! isset($this->importTranslators[$transKey])) {
                throw new \Zend_Exception("Unknown translator $transName passed to " . __CLASS__ . '->' . __FUNCTION__ . '()');
            }
            $translator = $this->importTranslators[$transKey];

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

                        if (trim($required)) {
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
                $resultRow[$requiredKey] = $requireds ? ($requireds == $transCount ? $this->_('Yes') : $this->_('For bold')) : ' ';

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

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * When invalid data should result in an error, you can throw it
     * here but you can also perform the check in the
     * checkRegistryRequestsAnswers() function from the
     * {@see \Zalt\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput(): bool
    {
        return parent::hasHtmlOutput();
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

        if (isset($this->formData[$this->stepFieldName]) &&
                $this->formData[$this->stepFieldName] > 1 &&
                (! $this->session->has('localfile'))) {
            $this->session->set('localfile', File::createTemporaryIn(
                    $this->tempDirectory,
                    $this->requestInfo->getCurrentController() . '_'
                    ));
        }

        // Must always exists
        $this->fileMode = 'file' === $this->formData['mode'];

        // Set the translator
        $translator = $this->getImportTranslator();
        if ($translator instanceof ModelTranslatorInterface) {
            $this->importer->setImportTranslator($translator);
        }

        // \Zalt\EchoOut\EchoOut::track($_POST, $_FILES, $this->formData);
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
            $this->getImportTranslator();

            if (! $this->sourceModel) {
                $this->importer->setSourceFile($this->session->get('localfile'), $this->session->get('extension'));
                $this->sourceModel = $this->importer->getSourceModel();
            }
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
        }

        return $this->sourceModel instanceof FullDataInterface;
    }

    /**
     * Code execution in batch mode
     *
     * @return void
     */
    protected function processCli()
    {
        try {
            // Lookup in importTranslators
            $queryParams = $this->requestInfo->getRequestQueryParams();
            $transName = $this->defaultImportTranslator;
            if (isset($queryParams['trans'])) {
                $transName = $queryParams['trans'];
            }
            if (! isset($this->importTranslators[$transName])) {
                throw new ModelTranslatorException(sprintf(
                        $this->_("Unknown translator '%s'. Should be one of: %s"),
                        $transName,
                        implode($this->_(', '), array_keys($this->importTranslators))
                    ));
            }
            $translator = $this->importTranslators[$transName];

            $file = null;
            if (isset($queryParams['file'])) {
                $file = $queryParams['file'];
            }

            $this->importer->setSourceFile($file);
            $this->importer->setImportTranslator($translator);

            $check = false;
            if (isset($queryParams['check'])) {
                $check = $queryParams['check'];
            }

            // \Zalt\Registry\Source::$verbose = true;
            $batch = $this->importer->getCheckAndImportBatch();
            $batch->setVariable('addImport', !$check);
            $batch->runContinuous();

            if ($batch->getMessages(false)) {
                echo implode("\n", $batch->getMessages()) . "\n";
            }
            if (! $batch->getCounter('import_errors')) {
                echo sprintf("%d records imported, %d records changed.\n", $batch->getCounter('imported'), $batch->getCounter('changed'));
            }

        } catch (\Exception $e) {
            $messages[] = "IMPORT ERROR!";
            $messages[] = $e->getMessage();
            $messages[] = null;
            $messages[] = sprintf(
                    "Usage instruction: %s %s file=filename [trans=[%s]] [check=1]",
                    $this->requestInfo->getCurrentController(),
                    $this->requestInfo->getCurrentAction(),
                    implode('|', array_keys($this->importTranslators))
                    );
            $messages[] = sprintf(
                    "\tRequired parameter: file=filename to import, absolute or relative to %s",
                    getcwd()
                    );
            $messages[] = sprintf(
                    "\tOptional parameter: trans=[%s] default is %s",
                    implode('|', array_keys($this->importTranslators)),
                    $this->defaultImportTranslator
                    );
            $messages[] = "\tOptional parameter: check=[0|1], 0=default, 1=check input only";
            echo implode("\n", $messages) . "\n";
        }
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
        if ($this->session->has('localfile') && file_exists($this->session->get('localfile'))) {
            // Now is a good moment to remove the temporary file
            @unlink($this->session->get('localfile'));
        }

        parent::setAfterSaveRoute();
    }
}
