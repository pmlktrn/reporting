<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Application\Hook;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Template;

use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\PreviewForm;
use Icinga\Module\Reporting\Web\Forms\ReportForm;
use Icinga\Module\Reporting\Web\Forms\ScheduleForm;
use Icinga\Module\Reporting\Web\Forms\SendForm;
use Icinga\Module\Reporting\Web\Forms\TemplateForm;
use Icinga\Web\StyleSheet;
use ipl\Html\Error;
use ipl\Html\Html;
use ipl\Html\HtmlString;
use reportingipl\Web\Url;
use reportingipl\Web\Widget\ActionBar;
use reportingipl\Web\Widget\DropdownLink;

class TemplateController extends Controller
{

    use Database;

    /** @var Template */
    protected $template;

    public function init()
    {
        $this->template = Template::fromDb($this->params->getRequired('id'));
    }

    public function indexAction()
    {
        $this->setTitle($this->template->getName());

        $this->addControl($this->assembleActions());

        try {
            $this->addContent($this->template->toHtml());
        } catch (\Exception $e) {
            $this->addContent(Error::show($e));
        }
    }

    public function previewAction()
    {
        $this->setTitle('Preview');

        $values = [
            'name'      => $this->template->getName(),
            // TODO(el): Must cast to string here because ipl/html does not support integer return values for attribute callbacks
        ];

        //  $reportlet = $this->template->getReportlets()[0];

        //$values['reportlet'] = $reportlet->getClass();

        //foreach ($reportlet->getConfig() as $name => $value) {
        //   $values[$name] = $value;
        // }

        /*$form = new TemplateForm();
        $form->setId($this->template->getId());
        $form->populate($values);
        $form->handleRequest(ServerRequest::fromGlobals());
*/

        $form = new PreviewForm();
        $this->redirectForm($form, 'reporting/templates');

        $this->addContent($form);
    }

    public function editAction()
    {
        $this->setTitle('Edit Template');

        $values = [
            'name'      => $this->template->getName()
            // TODO(el): Must cast to string here because ipl/html does not support integer return values for attribute callbacks
        ];

      //  $reportlet = $this->template->getReportlets()[0];

        //$values['reportlet'] = $reportlet->getClass();

        //foreach ($reportlet->getConfig() as $name => $value) {
         //   $values[$name] = $value;
       // }

        $form = new TemplateForm();
        $form->setId($this->template->getId());
        $form->populate($values);
        $form->handleRequest(ServerRequest::fromGlobals());

        //$this->redirectForm($form, 'reporting/templates');

        $cancel = $form->getElement('cancel');
        if (! $cancel->hasBeenPressed()) {
            $id = $this->template->getId();
            $this->redirectForm($form, "reporting/template/preview?id=$id");
        } else {
            $this->redirectForm($form, 'reporting/templates');
        }

        $this->addContent($form);
    }

    public function sendAction()
    {
        $this->setTitle('Send Template');

        $form = new SendForm();
        $form
            ->setReport($this->template)
            ->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, "reporting/template?id={$this->template->getId()}");

        $this->addContent($form);
    }

    public function downloadAction()
    {
        $type = $this->params->getRequired('type');

        $name = sprintf(
            '%s (%s) %s',
            $this->template->getName(),
            $this->template->getTimeframe()->getName(),
            date('Y-m-d H:i')
        );

        switch ($type) {
            case 'pdf':
                $pdfexport = null;

                if (Hook::has('Pdfexport')) {
                    $pdfexport = Hook::first('Pdfexport');

                    if (! $pdfexport->isSupported()) {
                        throw new \Exception("Can't export");
                    }
                }

                if (! $pdfexport) {
                    throw new \Exception("Can't export");
                }

                $html = Html::tag(
                    'html',
                    null,
                    [
                        Html::tag(
                            'head',
                            null,
                            Html::tag(
                                'style',
                                null,
                                new HtmlString(StyleSheet::forPdf())
                            )
                        ),
                        Html::tag(
                            'body',
                            null,
                            Html::tag(
                                'div',
                                ['class' => 'icinga-module module-reporting'],
                                new HtmlString($this->template->toHtml())
                            )
                        )
                    ]
                );

                /** @var Hook\PdfexportHook */
                $pdfexport->streamPdfFromHtml((string) $html, $name);
                exit;
            case 'csv':
                $response = $this->getResponse();
                $response
                    ->setHeader('Content-Type', 'text/csv')
                    ->setHeader('Cache-Control', 'no-store')
                    ->setHeader(
                        'Content-Disposition',
                        'attachment; filename=' . $name . '.csv'
                    )
                    ->appendBody($this->template->toCsv())
                    ->sendResponse();
                exit;
            case 'json':
                $response = $this->getResponse();
                $response
                    ->setHeader('Content-Type', 'application/json')
                    ->setHeader('Cache-Control', 'no-store')
                    ->setHeader(
                        'Content-Disposition',
                        'inline; filename=' . $name . '.json'
                    )
                    ->appendBody($this->template->toJson())
                    ->sendResponse();
                exit;
        }
    }

    protected function assembleActions()
    {
        $reportId = $this->template->getId();

        $download = (new DropdownLink('Download'))
            ->addLink('PDF', Url::fromPath('reporting/report/download?type=pdf', ['id' => $reportId]));

        if ($this->template->providesData()) {
            $download->addLink('CSV', Url::fromPath('reporting/report/download?type=csv', ['id' => $reportId]));
            $download->addLink('JSON', Url::fromPath('reporting/report/download?type=json', ['id' => $reportId]));
        }

        $actions = new ActionBar();

        $actions
            ->addLink('Modify', Url::fromPath('reporting/template/edit', ['id' => $reportId]), 'edit')
            ->add($download)
            ->addLink('Send', Url::fromPath('reporting/report/send', ['id' => $reportId]), 'forward');

        return $actions;
    }
}
