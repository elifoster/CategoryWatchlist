<?php

class SpecialWatchCategory extends SpecialPage {
    public function __construct() {
        parent::__construct('WatchCategory', 'editmywatchlist');
    }

    public function execute($par) {
        $this->checkPermissions();
        $request = $this->getRequest();
        $output = $this->getOutput();
        $this->setHeaders();

        if ($request->getVal('update') != 1 || empty($request->getVal('update'))) {
            $output->addHTML($this->getUpdateForm());
        } else {
            $newCat = $request->getText('watchlist-add');
            $deleteCat = $request->getText('watchlist-del');
            $dbw = wfGetDB(DB_MASTER);

            if (!empty($newCat)) {
                $selectResult = $dbw->select(
                    'watched_categories',
                    '*',
                    [
                        'category' => $newCat,
                    ],
                    __METHOD__
                );
                if (empty($selectResult->current()->users)) {
                    $vals = array(
                        'users' => $this->getUser()->getId(),
                    );
                    if ($selectResult->numRows() == 0) {
                        $vals['category'] = $newCat;
                        $dbw->insert(
                            'watched_categories',
                            $vals,
                            __METHOD__
                        );
                    } else {
                        $dbw->update(
                            'watched_categories',
                            $vals,
                            [
                                'category' => $newCat,
                            ],
                            __METHOD__
                        );
                    }
                } else {
                    $dbw->update(
                        'watched_categories',
                        [
                            'users' => $selectResult->current()->users . ',' . $this->getUser()->getId(),
                        ],
                        [
                            'category' => $newCat,
                        ],
                        __METHOD__
                    );
                }
                $pages = $dbw->select(
                    'categorylinks',
                    '*',
                    [
                        'cl_to' => $newCat,
                    ],
                    __METHOD__,
                    [
                        'ORDER BY' => 'cl_sortkey',
                    ]
                );
                foreach ($pages as $page) {
                    $page = Article::newFromID($page->cl_from);
                    $output->addWikiText("Adding {$page->getTitle()} to watchlist");
                    $this->getUser()->addWatch($page->getTitle());
                }
            }

            if (!empty($deleteCat)) {
                $selectResult = $dbw->select(
                    'watched_categories',
                    '*',
                    [
                        'category' => $deleteCat,
                    ],
                    __METHOD__
                );
                if ($selectResult->numRows() > 0) {
                    $users = explode(',', $selectResult->current()->users);
                    $users = array_diff($users, [$this->getUser()->getId()]);
                    $userString = implode(',', $users);
                    $dbw->update(
                        'watched_categories',
                        [
                            'users' => $userString,
                        ],
                        [
                            'category' => $deleteCat,
                        ],
                        __METHOD__
                    );
                    $pages = $dbw->select(
                        'categorylinks',
                        '*',
                        [
                            'cl_to' => $deleteCat,
                        ],
                        __METHOD__,
                        [
                            'ORDER BY' => 'cl_sortkey',
                        ]
                    );
                    foreach ($pages as $page) {
                        $page = Article::newFromID($page->cl_from);
                        $output->addWikiText("Removing {$page->getTitle()} from watchlist");
                        $this->getUser()->removeWatch($page->getTitle());
                    }
                }
            }
            $output->addWikiText("'''" . wfMessage('categorywatchlist-finish')->text() .  "'''");
            $output->addHTML($this->getUpdateForm());
        }
    }

    protected function getUpdateForm() {
        global $wgScript;
        $form = "<table>";
        $form .= "<tr>
                    <td style='text-align:right; width:200px;'>
                        <label>" . wfMessage('categorywatchlist-watch')->text() . "</label>
                    </td>
                    <td>
                        <input type='text' name='watchlist-add' id='watchlist-add'>
                    </td>
                 </tr>";
        $form .= "<tr>
                    <td style='text-align:right; width:200px;'>
                        <label>" .wfMessage('categorywatchlist-del')->text() . "</label>
                    </td>
                    <td>
                        <input type='text' name='watchlist-del' id='watchlist-del'>
                    </td>
                 </tr>";
        $form .= "</table>";
        $form .= "<tr>
                    <td colspan='2'>
                        <input type='submit' value='" . wfMessage('categorywatchlist-submit')->text() . "'>
                    </td>
                 </tr>";

        $out = Xml::openElement('form', array('method' => 'get', 'action' => $wgScript, 'id' => 'ext-categorywatchlist-form'))
             . Html::hidden('title', $this->getPageTitle()->getPrefixedText())
             . Html::hidden('token', $this->getUser()->getEditToken())
             . Html::hidden('update', 1)
             . $form
             . Html::closeElement('fieldset')
             . Html::closeElement('form')
             . "\n";
        return $out;
    }
}