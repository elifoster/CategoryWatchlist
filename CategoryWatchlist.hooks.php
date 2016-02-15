<?php

class CategoryWatchlistHooks {
    /**
     * Checks for differences in categories, and adds/removes the page from the user's watchlist accordingly.
     * @param $wikiPage WikiPage
     * @param $user User
     * @param $content Content
     * @param $summary string
     * @param $isMinor bool
     * @param $isWatch bool
     * @param $section int
     * @param $flags
     * @param $status Status
     */
    public static function onPreSave(&$wikiPage, &$user, &$content, &$summary, $isMinor, $isWatch, $section, &$flags, &$status) {
        $before = array();
        $dbr = wfGetDB(DB_SLAVE);
        $result = $dbr->select(
            'categorylinks',
            '*',
            [
                'cl_from' => $wikiPage->getId(),
            ],
            __METHOD__,
            [
                'ORDER BY' => 'cl_sortkey',
            ]
        );
        if ($result->numRows() == 0) {
            return;
        }
        foreach ($result as $row) {
            array_push($before, $row->cl_to);
        }
        $newText = $content->getContentHandler()->getContentText();
        preg_match('/\[\[[Cc]ategory\:(.+)[|[\]\]]/', $newText, $after);
        $add = array_diff($after, $before);
        $sub = array_diff($before, $after);

        foreach ($sub as $cat) {
            $catwatch = $dbr->select(
                'watched_categories',
                '*',
                [
                    'category' => $cat
                ],
                __METHOD__
            );
            foreach ($catwatch as $row) {
                $userIDStrings = explode(',', $row->users);
                foreach ($userIDStrings as $id) {
                    $user = User::newFromId($id);
                    $user->removeWatch($wikiPage->getTitle());
                }
            }
        }

        foreach ($add as $cat) {
            $catwatch = $dbr->select(
                'watched_categories',
                '*',
                [
                    'category' => $cat
                ],
                __METHOD__
            );
            foreach ($catwatch as $row) {
                $userIDStrings = explode(',', $row->users);
                foreach ($userIDStrings as $id) {
                    $user = User::newFromId($id);
                    $user->addWatch($wikiPage->getTitle());
                }
            }
        }
    }
}