<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Authentication\Auth;
use Icinga\Forms\ConfigForm;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\ProvidedActions;
use Icinga\Module\Reporting\Template;
use Icinga\Module\Reporting\Web\DivDecorator;
use Icinga\Module\Reporting\Web\Flatpickr;
use Icinga\Module\Reporting\Web\Controller;
use ipl\Html\Form;
use ipl\Html\FormElement\SubmitElementInterface;
use ipl\Html\FormElement\TextareaElement;
use ipl\Html\Html;
use reportingipl\Web\Url;
use Icinga\Web;

class TemplateForm extends Form
{
    use Database;
    use DecoratedElement;
    use ProvidedActions;

    //const COVER_FIELDS_TOGGLE = 'coveron';

    /** @var Template */
    protected $template;

    protected $id;

    const WILDCARD_NAME = 'allAndEverything';


    /*public function setTemplate(Template $template)
    {
        $this->template = $template;

        $schedule = $template->getSchedule();

        if ($schedule !== null) {
            $this->setId($schedule->getId());

            $values = [
                    'start'     => $schedule->getStart(),
                   // 'action'    => $schedule->getAction(),
                    'coveron'   => $schedule->getAction()
                ] + $schedule->getConfig();

            $this->populate($values);
        }

        return $this;
    }
*/
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    protected function assemble()
    {
        $this->setDefaultElementDecorator(new DivDecorator());

        $this->addElement('text', 'name', [
            'required'         => true,
            'label'            => 'Template Name',
            'placeholder'      => 'Type your Template Name'
        ]);

        $this->addElement('text', 'bg_img', [
           // 'required'         => true,
            'label'            => 'Background Image',
            'placeholder'      => 'Enter URL'
        ]);

        $this->addElement('text', 'title_logo', [
            //'required'         => true,
            'label'            => 'Title Logo',
            'placeholder'      => 'Enter URL'
        ]);

        $this->addElement('text', 'title', [
           // 'required'         => true,
            'label'            => 'Title',
            'placeholder'      => 'Enter a Report Title',
            'class'     => ['id' => 'title']
        ]);

        //HEADER
        //select1
        $this->addElement('select', 'hcolumnone', [
            //'required'  => true,
            'label'     => 'H:Column 1',
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'hcolumnone']
        ]);
        //text1
        $this->addElement('text', 'hconetext', [
            //'required'         => true,
            'placeholder'      => 'Enter Text',
            'class'     => ['id' => 'hconetext']
        ]);

        //select2
        $this->addElement('select', 'hcolumntwo', [
            //'required'  => true,
            'label'     => 'H:Column 2',
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'hcolumntwo']
        ]);
        //text2
        $this->addElement('text', 'hctwotext', [
            //'required'         => true,
            'placeholder'      => 'Enter URL',
            'class'     => ['id' => 'hctwotext']
        ]);

        //select3
        $this->addElement('select', 'hcolumnthree', [
            //'required'  => true,
            'label'     => 'H:Column 3',
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'hcolumnthree']
        ]);
        //select3
        $this->addElement('select', 'hcolumnfour', [
            //'required'  => true,
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'hcolumnfour']
        ]);

        //FOOTER
        //select1
        $this->addElement('select', 'fcolumnone', [
            //'required'  => true,
            'label'     => 'F:Column 1',
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'fcolumnone']
        ]);
        //select1
        $this->addElement('select', 'fcolumnonenext', [
            //'required'  => true,
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'fcolumnonenext']
        ]);

        //select2
        $this->addElement('select', 'fcolumntwo', [
            //'required'  => true,
            'label'     => 'F:Column 2',
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'fcolumntwo']
        ]);
        //select2
        $this->addElement('select', 'fcolumntwonext', [
            //'required'  => true,
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'fcolumntwonext']
        ]);

        //select3
        $this->addElement('select', 'fcolumnthree', [
            //'required'  => true,
            'label'     => 'F:Column 3',
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'fcolumnthree']
        ]);
        //select3
        $this->addElement('select', 'fcolumnthreenext', [
            //'required'  => true,
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => ['id' => 'fcolumnthreenext']
        ]);

        /*$this->addElement('checkbox', self::COVER_FIELDS_TOGGLE, [
            'autosubmit' => true,
            'label'     => 'Use Cover Page'
        ]);*/

        $values = $this->getValues();

        /*if (isset($values[self::COVER_FIELDS_TOGGLE]) && $values[self::COVER_FIELDS_TOGGLE]) {

            $this->addElement('text', 'cover_image', [
                'required'         => true,
                'label'            => 'Image for coverpage',
                'placeholder'      => 'Upload Image'
            ]);
        }*/
        
        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? 'Create Template' : 'Update Template',
            //'href' => 'reporting/templates'
            ]);

        $this->addElement('submit', 'cancel', [
            'label' => $this->id === null ? 'Cancel' : 'Cancel',
            //'href' => 'reporting/templates',
            'formnovalidate' => true
        ]);

        if ($this->id !== null) {
            $this->addElement('submit', 'remove', [
                'label'          => 'Remove Template',
                'class'          => 'remove-button',
                'formnovalidate' => true
            ]);

            /** @var SubmitElementInterface $remove */
            $remove = $this->getElement('remove');
            if ($remove->hasBeenPressed()) {
                $this->getDb()->delete('template', ['id = ?' => $this->id]);

                // Stupid cheat because ipl/html is not capable of multiple submit buttons
                $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
                $this->valid = true;

                return;
            }
        }

        // cancel button
        $cancel = $this->getElement('cancel');
        if ($cancel->hasBeenPressed()) {
            // Stupid cheat because ipl/html is not capable of multiple submit buttons
            $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
            $this->valid = true;

            return;
        }

        // TODO(el): Remove once ipl/html's TextareaElement sets the value as content
        foreach ($this->getElements() as $element) {
            if ($element instanceof TextareaElement && $element->hasValue()) {
                $element->setContent($element->getditActioValue());
            }
        }
    }

    public function onSuccess()
    {
        $cancel = $this->getElement('cancel');
        if (! $cancel->hasBeenPressed()) {

            $db = $this->getDb();

            $values = $this->getValues();

            $now = time() * 1000;

            $data = [
                'name'      => $values['name'],
              //  'coveron'   => $values['coveron'],
                'mtime'     => $now
            ];
            unset($values['name']);
            //unset($values['action']);

            $data['config'] = json_encode($values);

            $db->beginTransaction();

            if ($this->id === null) {
                $statement = $db->insert('template', [
                    'name'         => $data['name'],
                    'author'       => Auth::getInstance()->getUser()->getUsername(),
                    //'use_coverpage' => $data['coveron'] == null ? '0' : '1',
                    'ctime'        => $now,
                    'mtime'        => $now
                ]);
            } else {
                $statement = $db->update('template', [
                    'name'         => $data['name'],
                    //'use_coverpage' => $data['coveron'] == null ? '0' : '1',
                    'mtime'        => $now
                ], ['id = ?' => $this->id]);
            }

            $db->commitTransaction();

        }

    }
}
