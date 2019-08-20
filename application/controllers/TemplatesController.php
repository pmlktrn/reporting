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

        $newReport = new ButtonLink(
            $this->translate('New Template'),
            Url::fromPath('reporting/templates/new')->getAbsoluteUrl('&'),
            'plus'
        );

        $this->addControl($newReport);

        $tableRows = [];

        $select = (new Select())
            ->from('report r')
            ->columns(['r.*', 'timeframe' => 't.name'])
            ->join('timeframe t', 'r.timeframe_id = t.id')
            ->orderBy('r.mtime', SORT_DESC);

        foreach ($this->getDb()->select($select) as $report) {
            $url = Url::fromPath('reporting/report', ['id' => $report->id])->getAbsoluteUrl('&');

            $tableRows[] = Html::tag('tr', ['href' => $url], [
                Html::tag('td', null, $report->name),
                Html::tag('td', null, $report->author),
                Html::tag('td', null, date('Y-m-d H:i', $report->ctime / 1000)),
                Html::tag('td', null, date('Y-m-d H:i', $report->mtime / 1000))
            ]);
        }

        if (! empty($tableRows)) {
            $table = Html::tag(
                'table',
                ['class' => 'common-table table-row-selectable', 'data-base-target' => '_next'],
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
