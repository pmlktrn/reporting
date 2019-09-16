<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Application\Hook;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Template;
use Icinga\Module\Reporting\Web\Controller;
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

    /** @var Template */
    protected $title;

    public function init()
    {
        $this->template = Template::fromDb($this->params->getRequired('id'));
    }

    public function indexAction()
    {
        $this->setTitle('Preview: ' . $this->template->getName());


        $this->addControl($this->assembleActions());

        try {
            $this->addContent($this->template->toHtml());
        } catch (\Exception $e) {
            $this->addContent(Error::show($e));
        }
    }

    public function editAction()
    {
        $this->setTitle('Edit Template');

        $values = [
            'name'      => $this->template->getName(),
            'title'     => $this->template->getTitle(),
            'subtitle'  => $this->template->getSubtitle(),
            'company_name'  => $this->template->getCompanyName(),
            'company_logo'  => $this->template->getCompanyLogo()
            // TODO(el): Must cast to string here because ipl/html does not support integer return values for attribute callbacks
        ];

        $form = new TemplateForm();
        $form->setId($this->template->getId());
        $form->populate($values);
        $form->handleRequest(ServerRequest::fromGlobals());

        $cancel = $form->getElement('cancel');
        $remove = $form->getElement('remove');
        if (! $cancel->hasBeenPressed() && ! $remove->hasBeenPressed()) {
            $id = $this->template->getId();
            //$this->redirectForm($form, "reporting/template/edit?id=$id#!/icingaweb2/reporting/template/preview?id=$id");
            $this->redirectForm($form, "reporting/template/edit?id=$id#!/icingaweb2/reporting/template?id=$id");
        } else {
            $this->redirectForm($form, 'reporting/templates');
        }

        $this->addContent($form);
    }

//    public function sendAction()
//    {
//        $this->setTitle('Send Template');
//
//        $form = new SendForm();
//        $form
//            ->setTemplate($this->template)
//            ->handleRequest(ServerRequest::fromGlobals());
//
//        $this->redirectForm($form, "reporting/template?id={$this->template->getId()}");
//
//        $this->addContent($form);
//    }

    public function downloadAction()
    {
        $type = $this->params->getRequired('type');

        $name = sprintf(
            '%s (%s)',
            $this->template->getName(),
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
        $templateId = $this->template->getId();

        $download = (new DropdownLink('Download'))
            ->addLink('PDF', Url::fromPath('reporting/template/download?type=pdf', ['id' => $templateId]));
        $actions = new ActionBar();

        $actions
            ->add($download)
            ->addLink('Send', Url::fromPath('reporting/template/send', ['id' => $templateId]), 'forward');

        return $actions;
    }
}
