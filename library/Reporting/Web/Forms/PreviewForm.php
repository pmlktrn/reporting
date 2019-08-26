<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\ProvidedActions;
use Icinga\Module\Reporting\Template;
use Icinga\Module\Reporting\Web\DivDecorator;
use Icinga\Module\Reporting\Web\Flatpickr;
use ipl\Html\Form;
use ipl\Html\FormElement\SubmitElementInterface;
use ipl\Html\FormElement\TextareaElement;

class PreviewForm extends Form
{
    use Database;
    use DecoratedElement;
    use ProvidedActions;

    /** @var Template */
    protected $template;

    protected $id;

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
        /*$this->setDefaultElementDecorator(new DivDecorator());

        $this->addElement('text', 'name', [
            'required'         => true,
            'label'            => 'Tlalallalalalla Name',
            'placeholder'      => 'Type your Template Name'
        ]);

        $this->addElement('checkbox', 'coveron', [
            'label'     => 'Use Cover Page'
        ]);

        /*$this->addElement('text', 'action', [
            'label'     => 'Actionasd',
            'class'     => 'autosubmit'
        ]);
*/
   //     $values = $this->getValues();

     //   if (isset($values['action'])) {
       //     $config = new Form();
//            $config->populate($this->getValues());

            /** @var \Icinga\Module\Reporting\Hook\ActionHook $action */
         /*   $action = new $values['action'];

            $action->initConfigForm($config, $this->template);

            foreach ($config->getElements() as $element) {
                $this->addElement($element);
            }
        }

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? 'Create Template' : 'Update Template',
            //'data-base-target' => '_next'
        ]);

        $this->addElement('submit', 'cancel', [
            'label' => $this->id === null ? 'Cancel' : 'Cancel'
        ]);

        if ($this->id !== null) {
            $this->addElement('submit', 'remove', [
                'label'          => 'Remove Template',
                'class'          => 'remove-button',
                'formnovalidate' => true
            ]);
*/
            /** @var SubmitElementInterface $remove */
  /*          $remove = $this->getElement('remove');
            if ($remove->hasBeenPressed()) {
                $this->getDb()->delete('template', ['id = ?' => $this->id]);

                // Stupid cheat because ipl/html is not capable of multiple submit buttons
                $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
                $this->valid = true;

                return;
            }
        }
*/
        //if cover not null:
        /*$coveron = $this->getElement('coveron');
        if ($coveron == ) {

            $this->addElement('text', 'name', [
                'required'         => true,
                'label'            => 'lol-feld',
                'placeholder'      => 'Type Name'
            ]);
            $this->valid = true;

            return;
        }
*/
  /*      // TODO(el): Remove once ipl/html's TextareaElement sets the value as content
        foreach ($this->getElements() as $element) {
            if ($element instanceof TextareaElement && $element->hasValue()) {
                $element->setContent($element->getditActioValue());
            }
        }
    }

    public function onSuccess()
    {
        $db = $this->getDb();

        $values = $this->getValues();

        $now = time() * 1000;

        $data = [
            'name'      => $values['name'],
            'coveron'   => $values['coveron'],
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
                'use_coverpage' => $data['coveron'] == null ? 'off' : 'on',
                'ctime'        => $now,
                'mtime'        => $now
            ]);
        } else {
            $statement = $db->update('template', [
                'name'         => $data['name'],
                'use_coverpage' => $data['coveron'] == null ? 'off' : 'on',
                'mtime'        => $now
            ], ['id = ?' => $this->id]);
        }

        $db->commitTransaction();
   */
    echo "Fresh";
  }
}
