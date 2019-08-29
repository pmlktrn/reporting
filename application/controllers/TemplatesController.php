<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TemplateForm;
use Icinga\Module\Reporting\Web\ReportsTabs;
use ipl\Html\Html;
use ipl\Sql\Select;
use reportingipl\Web\Url;
use reportingipl\Web\Widget\ButtonLink;

class TemplatesController extends Controller
{
    use Database;
    use ReportsTabs;

    public function indexAction()
    {
        $this->createTabs()->activate('templates');

        $newTemplate = new ButtonLink(
            $this->translate('New Template'),
            Url::fromPath('reporting/templates/new')->getAbsoluteUrl('&'),
            'plus'
        );

        $this->addControl($newTemplate);

        $tableRows = [];

        $select = (new Select())
            ->from('template t')
            ->columns(['t.*', 'timeframe' => 't.name'])
            ->orderBy('t.mtime', SORT_DESC);

        foreach ($this->getDb()->select($select) as $template) {
            //$url = Url::fromPath('reporting/template/edit', ['id' => $template->id])->getAbsoluteUrl('&');
            //#!/icingaweb2/reporting/template?id=$id
            $id = $template->id;
            $url = Url::fromPath('reporting/template/edit', ['id' => $template->id])->getAbsoluteUrl('&');

            $tableRows[] = Html::tag('tr', ['href' => $url], [
                Html::tag('td', null, $template->name),
                Html::tag('td', null, $template->author),
                Html::tag('td', null, date('Y-m-d H:i', $template->ctime / 1000)),
                Html::tag('td', null, date('Y-m-d H:i', $template->mtime / 1000))
            ]);
        }

        if (! empty($tableRows)) {
            $table = Html::tag(
                'table',
                ['class' => 'common-table table-row-selectable'],
                [
                    Html::tag(
                        'thead',
                        null,
                        Html::tag(
                            'tr',
                            null,
                            [
                                Html::tag('th', null, 'Template'),
                                Html::tag('th', null, 'Author'),
                                Html::tag('th', null, 'Date Created'),
                                Html::tag('th', null, 'Date Modified')
                            ]
                        )
                    ),
                    Html::tag('tbody', null, $tableRows)
                ]
            );

            $this->addContent($table);
        } else {
            $this->addContent(Html::tag('p', null, 'No templates created yet.'));
        }
    }

    public function newAction()
    {
        $this->setTitle($this->translate('New Template'));

        $form = new TemplateForm();
        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/templates');

        $this->addContent($form);
    }
}
