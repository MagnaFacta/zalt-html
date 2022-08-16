<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets\Standard;

/**
 * Generic import wizard.
 *
 * Set the targetModel (directly or through $this->model) and the
 * importTranslators and it should work.
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class ModelImportSnippet extends \MUtil\Snippets\WizardFormSnippetAbstract
{
    /**
     * Contains the errors generated so far
     *
     * @var array
     */
    private $_errors = array();

    /**
     *
     * @var \Zend_Session_Namespace
     */
    protected $_session;

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

    /**
     *
     * @var \MUtil\Model\Importer
     */
    protected $importer;

    /**
     *
     * @var \MUtil\Model\ModelAbstract
     */
    protected $importModel;

    /**
     * Required, an array of one or more translators
     *
     * @var array of \MUtil\Model\ModelTranslatorInterface objects
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
     * @var \MUtil\Model\ModelAbstract
     */
    protected $model;

    /**
     * Model to read import
     *
     * @var \MUtil\Model\ModelAbstract
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
     * @var \MUtil\Model\ModelAbstract
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

    /**
     * Helper function to sort translator table by model order
     *
     * @param string $a
     * @param string $b
     * @return int
     */
    protected function _sortTranslatorTable($a, $b)
    {
        $ao = $this->targetModel->getOrder($a);
        $bo = $this->targetModel->getOrder($b);

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
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param \MUtil\Model\ModelAbstract $model
     */
    protected function addStep1(\MUtil\Model\Bridge\FormBridgeInterface $bridge, \MUtil\Model\ModelAbstract $model)
    {
        $this->addItems($bridge, 'trans', 'mode');
    }

    /**
     * Add the elements from the model to the bridge for the current step
     *
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param \MUtil\Model\ModelAbstract $model
     */
    protected function addStep2(\MUtil\Model\Bridge\FormBridgeInterface $bridge, \MUtil\Model\ModelAbstract $model)
    {
        $translator = $this->getImportTranslator();
        if ($translator instanceof \MUtil\Model\ModelTranslatorInterface) {
            $element = $bridge->getForm()->createElement('html', 'trans_header');
            $element->span($this->_('Choosen import definition: '));
            $element->strong($translator->getDescription());
            $element->setDecorators(array('Tooltip', 'ViewHelper'));
            $bridge->addElement($element);
        }

        if ($this->fileMode) {
            $this->addItems($bridge, 'file');

            $element = $bridge->getForm()->getElement('file');

            if ($element instanceof \Zend_Form_Element_File) {
                // Now add the rename filter, the localfile is known only once after loadFormData() has run
                $element->addFilter(new \Zend_Filter_File_Rename(array(
                    'target'    => $this->_session->localfile,
                    'overwrite' => true
                    )));

                // Download the data (no test for post, step 2 is always a post)
                if ($element->isValid(null) && $element->getFileName()) {
                    // Now the filename is still set to the upload filename.
                    $this->_session->extension = pathinfo($element->getFileName(), PATHINFO_EXTENSION);
                    // \MUtil\EchoOut\EchoOut::track($element->getFileName(), $element->getFileSize());
                    if (!$element->receive()) {
                        throw new \MUtil\Model\ModelException(sprintf(
                            $this->_("Error retrieving file '%s'."),
                            $element->getFileName()
                            ));
                    }
                }
            }
        } else {
            $this->addItems($bridge, 'content');

            $this->_session->extension = 'txt';
            if (isset($this->formData['content']) && $this->formData['content']) {
                file_put_contents($this->_session->localfile, $this->formData['content']);
            } else {
                if (filesize($this->_session->localfile)) {
                    $content = file_get_contents($this->_session->localfile);
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
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param \MUtil\Model\ModelAbstract $model
     */
    protected function addStep3(\MUtil\Model\Bridge\FormBridgeInterface $bridge, \MUtil\Model\ModelAbstract $model)
    {
        if ($this->loadSourceModel()) {
            $this->displayHeader($bridge, $this->_('Upload successful!'));
            $this->displayErrors($bridge, $this->_('Check the input visually.'));

            // \MUtil\EchoOut\EchoOut::track($this->sourceModel->load());

            $element = $bridge->getForm()->createElement('html', 'importdisplay');

            $repeater = \MUtil\Lazy::repeat(new \LimitIterator($this->sourceModel->loadIterator(), 0, 20));
            $table    = new \MUtil\Html\TableElement($repeater, array('class' => $this->formatBoxClass));

            foreach ($this->sourceModel->getItemsOrdered() as $name) {
                $table->addColumn($repeater->$name, $name);
            }

            // Extra div for CSS settings
            $element->setValue(new \MUtil\Html\HtmlElement('div', $table, array('class' => $this->formatBoxClass)));
            $bridge->addElement($element);
        } else {
            $this->displayHeader($bridge, $this->_('Upload error!'));
            $this->displayErrors($bridge);

            $this->nextDisabled = true;
        }
    }

    /**
     * Add the elements from the model to the bridge for the current step
     *
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param \MUtil\Model\ModelAbstract $model
     */
    protected function addStep4(\MUtil\Model\Bridge\FormBridgeInterface $bridge, \MUtil\Model\ModelAbstract $model)
    {
        $this->nextDisabled = true;

        if ($this->loadSourceModel()) {
            $form  = $bridge->getForm();
            $batch = $this->importer->getCheckWithImportBatches();

            $batch->setFormId($form->getId());
            $batch->autoStart = true;

            // \MUtil\Registry\Source::$verbose = true;
            if ($batch->run($this->getRequestQueryParams())) {
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
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param \MUtil\Model\ModelAbstract $model
     */
    protected function addStep5(\MUtil\Model\Bridge\FormBridgeInterface $bridge, \MUtil\Model\ModelAbstract $model)
    {
        $this->nextDisabled = true;

        if ($this->loadSourceModel()) {
            $form  = $bridge->getForm();
            $batch = $this->importer->getImportOnlyBatch();

            $batch->setFormId($form->getId());
            $batch->autoStart = true;

            if ($batch->run($this->getRequestQueryParams())) {
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
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param \MUtil\Model\ModelAbstract $model
     * @param int $step The current step
     */
    protected function addStepElementsFor(\MUtil\Model\Bridge\FormBridgeInterface $bridge, \MUtil\Model\ModelAbstract $model, $step)
    {
        $this->displayHeader(
                $bridge,
                sprintf($this->_('Data import. Step %d of %d.'), $step, $this->getStepCount()),
                'h1');

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

    /**
     * Hook for after save
     *
     * @param \MUtil\Task\TaskBatch $batch that was just executed
     * @param \MUtil\Form\Element\Html $element Tetx element for display of messages
     * @return string a message about what has changed (and used in the form)
     */
    public function afterImport(\MUtil\Task\TaskBatch $batch, \MUtil\Form\Element\Html $element)
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
        parent::afterRegistry();

        if (! $this->importer instanceof \MUtil\Model\Importer) {
            $this->importer = new \MUtil\Model\Importer();

            $source = new \MUtil\Registry\Source(get_object_vars($this));
            $source->applySource($this->importer);
            $this->importer->setRegistrySource($source);
        }
        if (! $this->targetModel instanceof \MUtil\Model\ModelAbstract) {
            if ($this->model instanceof \MUtil\Model\ModelAbstract) {
                $this->targetModel = $this->model;
            }
        }
        if ($this->targetModel instanceof \MUtil\Model\ModelAbstract) {
            $this->importer->setTargetModel($this->targetModel);
        }
        if ($this->sourceModel instanceof \MUtil\Model\ModelAbstract) {
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
            //$table1 = \MUtil\Html\TableElement::createArray($fieldInfo, $this->_('Import field definitions'), true);
            //$table1->appendAttrib('class', $this->formatBoxClass);

            // Fast
            $table = \MUtil\Html\TableElement::table();
            $table->caption($this->_('Import field definitions'));
            $table->appendAttrib('class', $this->formatBoxClass);
            $repeater = new \MUtil\Lazy\Repeatable($fieldInfo);
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
     * @return \MUtil\Model\ModelAbstract
     */
    protected function createModel()
    {
        if (! $this->importModel instanceof \MUtil\Model\ModelAbstract) {
            // $model = new \MUtil\Model\TableModel
            $model = new \MUtil\Model\SessionModel('import_for_' . $this->getCurrentController());

            $model->set('trans', 'label', $this->_('Import definition'),
                    'default', $this->defaultImportTranslator,
                    'description', $this->_('See import field definitions table'),
                    'multiOptions', $this->getTranslatorDescriptions(),
                    'required', true,
                    'elementClass', 'Radio',
                    'separator', ' ');

            $model->set('mode', 'label', $this->_('Choose work mode'),
                    'default', 'file',
                    'multiOptions', array(
                        'file'     => $this->_('Upload a file'),
                        'textarea' => $this->_('Copy and paste into a text field'),
                    ),
                    'required', true,
                    'elementClass', 'Radio',
                    'separator', ' ');

            $model->set('file', 'label', $this->_('Import file'),
                    'count',        1,
                    'elementClass', 'File',
                    'extension',    'csv,txt,xml',
                    'required',     true);

            if ($this->tempDirectory) {
                \MUtil\File::ensureDir($this->tempDirectory);
                $model->set('file', 'destination',  $this->tempDirectory);
            }

            // Storage for local copy of the file, kept through process
            $model->set('import_id');

            $model->set('content', 'label', $this->_('Import text - user header line - separate fields using tabs'),
                    'description', $this->_('Empty fields remove any existing values. Add a field only when used.'),
                    'cols', 120,
                    'elementClass', 'Textarea');

            $this->importModel = $model;
        }

        return $this->importModel;
    }

    /**
     * Display the errors
     *
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param array Errors to display
     */
    protected function displayErrors(\MUtil\Model\Bridge\FormBridgeInterface $bridge, $errors = null)
    {
        if (null === $errors) {
            $errors = $this->_errors;
        }

        if ($errors) {
            $element = $bridge->getForm()->createElement('html', 'errorlist');

            $element->ul($errors, array('class' => $this->errorClass));

            $bridge->addElement($element);
        }
    }

    /**
     * Display a header
     *
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param mixed $header Header content
     * @param string $tagName
     */
    protected function displayHeader(\MUtil\Model\Bridge\FormBridgeInterface $bridge, $header, $tagName = 'h2')
    {
        $element = $bridge->getForm()->createElement('html', 'step_header');
        $element->$tagName($header);

        $bridge->addElement($element);
    }

    /**
     * Try to get the current translator
     *
     * @return \MUtil\Model\ModelTranslatorInterface or false if none is current
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
        if (! $translator instanceof \MUtil\Model\ModelTranslatorInterface) {
            $this->_errors[] = sprintf($this->_('%s is not a valid import definition.'), $this->formData['trans']);
            return false;
        }

        // Store/set relevant variables
        if ($this->importer instanceof \MUtil\Model\Importer) {
            $this->importer->setImportTranslator($translator);
        }
        if ($this->targetModel instanceof \MUtil\Model\ModelAbstract) {
            $translator->setTargetModel($this->targetModel);
            if ($this->importer instanceof \MUtil\Model\Importer) {
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
     * @return areay key -> description
     */
    protected function getTranslatorDescriptions()
    {
        if (! $this->_translatorDescriptions) {
            $results = array();
            foreach ($this->importTranslators as $key => $translator) {
                if ($translator instanceof \MUtil\Model\ModelTranslatorInterface) {
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
     * @param mixed $for A single translator, an array of translators or all translators if null;
     * @return array key -> description
     */
    protected function getTranslatorTable($for = null)
    {
        if (! $this->targetModel) {
            return array();
        }

        if (null === $for) {
            $for = $this->getTranslatorDescriptions();
        } elseif (!is_array($for)) {
            $descriptors = $this->getTranslatorDescriptions();
            if (! isset($descriptors[$for])) {
                throw new \Zend_Exception("Unknown translator $for passed to " . __CLASS__ . '->' . __FUNCTION__ . '()');
            }
            $for = array($for => $descriptors[$for]);
        }

        $requiredKey = $this->_('Required');
        $minimal     = array($requiredKey => ' '); // Array for making sure all fields are there
        $results     = array_fill_keys($this->targetModel->getItemsOrdered(), array());
        $transCount  = count($for);

        foreach ($for as $transKey => $transName) {
            if (! isset($this->importTranslators[$transKey])) {
                throw new \Zend_Exception("Unknown translator $for passed to " . __CLASS__ . '->' . __FUNCTION__ . '()');
            }
            $translator = $this->importTranslators[$transKey];

            if ($translator instanceof \MUtil\Model\ModelTranslatorInterface) {

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
                            $results[$target][$transName] = new \MUtil\Html\HtmlElement('strong', $source);
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

                if ($this->targetModel->has($name, 'label')) {
                    $label = $this->targetModel->get($name, 'label');
                } else {
                    $label = $name;
                }

                // $field = $this->_targetModel->get($name, 'type', 'maxlength', 'label', 'required');
                switch ($this->targetModel->get($name, 'type')) {
                    case \MUtil\Model::TYPE_NOVALUE:
                        unset($results[$name]);
                        continue 2;

                    case \MUtil\Model::TYPE_NUMERIC:
                        $maxlength = $this->targetModel->get($name, 'maxlength');
                        if ($maxlength) {
                            $decimals = $this->targetModel->get($name, 'decimals');
                            if ($decimals) {
                                $type = sprintf($this->_('A number of length %d, with a precision of %d digits after the period.'), $maxlength, $decimals);
                            } else {
                                $type = sprintf($this->_('A whole number of length %d.'), $maxlength);
                            }
                        } else {
                            $type = $this->_('A numeric value');
                        }
                        break;

                    case \MUtil\Model::TYPE_DATE:
                        $type = $this->_('Date value using ISO 8601: yyyy-mm-dd');
                        break;

                    case \MUtil\Model::TYPE_DATETIME:
                        $type = $this->_('Datetime value using ISO 8601: yyyy-mm-ddThh:mm:ss[+-hh:mm]');
                        break;

                    case \MUtil\Model::TYPE_TIME:
                        $type = $this->_('Time value using ISO 8601: hh:mm:ss[+-hh:mm]');
                        break;

                    default:
                        $maxlength = $this->targetModel->get($name, 'maxlength');
                        $minlength = $this->targetModel->get($name, 'minlength');
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
                $options = $this->targetModel->get($name, 'multiOptions');
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

                $typeDescr = $this->targetModel->get($name, 'import_descr');
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
     * {@see \MUtil\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput()
    {
        return parent::hasHtmlOutput();
    }

    /**
     * Initialize the _items variable to hold all items from the model
     */
    protected function initItems()
    {
        parent::initItems();

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
    protected function loadFormData()
    {
        if ($this->isPost()) {
            $this->formData = $this->getRequestPostParams() + $this->formData;
        } else {
            foreach ($this->importModel->getColNames('default') as $name) {
                if (!(isset($this->formData[$name]) && $this->formData[$name])) {
                    $this->formData[$name] = $this->importModel->get($name, 'default');
                }
            }
        }
        if (! (isset($this->formData['import_id']) && $this->formData['import_id'])) {
            $this->formData['import_id'] = mt_rand(10000,99999) . time();
        }
        $this->_session = new \Zend_Session_Namespace(__CLASS__ . '-' . $this->formData['import_id']);

        if (isset($this->formData[$this->stepFieldName]) &&
                $this->formData[$this->stepFieldName] > 1 &&
                (!(isset($this->_session->localfile) && $this->_session->localfile))) {
            $this->_session->localfile = \MUtil\File::createTemporaryIn(
                    $this->tempDirectory,
                    $this->getCurrentController() . '_'
                    );
        }

        // Must always exists
        $this->fileMode = 'file' === $this->formData['mode'];

        // Set the translator
        $translator = $this->getImportTranslator();
        if ($translator instanceof \MUtil\Model\ModelTranslatorInterface) {
            $this->importer->setImportTranslator($translator);
        }

        // \MUtil\EchoOut\EchoOut::track($_POST, $_FILES, $this->formData);
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
                $this->importer->setSourceFile($this->_session->localfile, $this->_session->extension);
                $this->sourceModel = $this->importer->getSourceModel();
            }
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
        }

        return $this->sourceModel instanceof \MUtil\Model\ModelAbstract;
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
            $queryParams = $this->getRequestQueryParams();
            $transName = $this->defaultImportTranslator;
            if (isset($queryParams['trans'])) {
                $transName = $queryParams['trans'];
            }
            if (! isset($this->importTranslators[$transName])) {
                throw new \MUtil\Model\ModelTranslateException(sprintf(
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

            // \MUtil\Registry\Source::$verbose = true;
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
                    $this->getCurrentController(),
                    $this->getCurrentAction(),
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
    protected function saveData()
    {
        // do nothing, save occurs in batch
    }

    /**
     * Set what to do when the form is 'finished' or 'cancelled'.
     *
     * @return \MUtil\Snippets\Standard\ModelImportSnippet
     */
    protected function setAfterSaveRoute()
    {
        if (isset($this->_session->localfile) && file_exists($this->_session->localfile)) {
            // Now is a good moment to remove the temporary file
            @unlink($this->_session->localfile);
        }

        parent::setAfterSaveRoute();
    }
}
