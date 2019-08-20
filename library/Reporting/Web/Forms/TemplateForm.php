<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\ProvidedActions;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\DivDecorator;
use Icinga\Module\Reporting\Web\Flatpickr;
use ipl\Html\Form;
use ipl\Html\FormElement\SubmitElementInterface;
use ipl\Html\FormElement\TextareaElement;

class TemplateForm extends Form
{
    use Database;
    use DecoratedElement;
    use ProvidedActions;

    /** @var Report */
    protected $report;

    protected $id;

    public function setReport(Report $report)
    {
        $this->report = $report;

        $schedule = $report->getSchedule();

        if ($schedule !== null) {
            $this->setId($schedule->getId());

            $values = [
                    'Name'     => $schedule->getStart()->format('Y-m-d H:i'),
                    'lol' => $schedule->getFrequency(),
                    'hehe'    => $schedule->getAction()
                ] + $schedule->getConfig();

            $this->populate($values);
        }

        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    protected function assemble()
    {
        $this->setDefaultElementDecorator(new DivDecorator());

        $frequency = [
            'minutely' => 'Minutely',
            'hourly'   => 'Hourly',
            'daily'    => 'Daily',
            'weekly'   => 'Weekly',
            'monthly'  => 'Monthly'
        ];

        $this->addElement('text', 'logo', [
            'required'         => true,
            'label'            => 'Logo',
            'placeholder'      => 'Enter a name for your template',
            'data-enable-time' => true
        ]);

        $this->addElement('text', 'header', [
            'required'  => true,
            'label'     => 'Header',
            'placeholder'      => 'Enter something',
            'options'   => [null => 'Please choose'] + $frequency,
        ]);

        $this->addElement('text', 'hehe', [
            'required'  => true,
            'label'     => 'Hehe',
            'placeholder'      => 'Enter something',
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => 'autosubmit'
        ]);

        $values = $this->getValues();

        if (isset($values['action'])) {
            $config = new Form();
//            $config->populate($this->getValues());

            /** @var \Icinga\Module\Reporting\Hook\ActionHook $action */
            $action = new $values['action'];

            $action->initConfigForm($config, $this->report);

            foreach ($config->getElements() as $element) {
                $this->addElement($element);
            }
        }

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? 'Create Template' : 'Update Schedule'
        ]);

        if ($this->id !== null) {
            $this->addElement('submit', 'remove', [
                'label'          => 'Remove Schedule',
                'class'          => 'remove-button',
                'formnovalidate' => true
            ]);

            /** @var SubmitElementInterface $remove */
            $remove = $this->getElement('remove');
            if ($remove->hasBeenPressed()) {
                $this->getDb()->delete('schedule', ['id = ?' => $this->id]);

                // Stupid cheat because ipl/html is not capable of multiple submit buttons
                $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
                $this->valid = true;

                return;
            }
        }

        // TODO(el): Remove once ipl/html's TextareaElement sets the value as content
        foreach ($this->getElements() as $element) {
            if ($element instanceof TextareaElement && $element->hasValue()) {
                $element->setContent($element->getValue());
            }
        }
    }

    public function onSuccess()
    {
        $db = $this->getDb();

        $values = $this->getValues();

        $now = time() * 1000;

        $data = [
            'name'     => \DateTime::createFromFormat('Y-m-d H:i', $values['name'])->getTimestamp() * 1000,
            'lol' => $values['lol'],
            'hehe'    => $values['hehe'],
            'mtime'     => $now
        ];

        unset($values['name']);
        unset($values['lol']);
        unset($values['hehe']);

        $data['config'] = json_encode($values);

        $db->beginTransaction();

        if ($this->id === null) {
            $db->insert('schedule', $data + [
                    'author'    => Auth::getInstance()->getUser()->getUsername(),
                    'report_id' => $this->report->getId(),
                    'ctime'     => $now
                ]);
        } else {
            $db->update('schedule', $data, ['id = ?' => $this->id]);
        }

        $db->commitTransaction();
    }
}
