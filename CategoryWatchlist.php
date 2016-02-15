<?php
/**
 * CategoryWatchlist
 *
 * @file
 * @ingroup Extensions
 * @version 1.0.0
 * @author Eli Clemente Gordillo Foster <elifosterwy@gmail.com>
 */

$wgExtensionCredits['other'][] = array(
    'path' => __FILE__,
    'name' => 'CategoryWatchlist',
    'descriptionmsg' => 'categorywatchlist-desc',
    'author' => 'Eli Clemente Gordillo Foster',
    'url' => 'https://github.com/elifoster/CategoryWatchlist',
);

$wgMessageDirs['CategoryWatchlist'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['CategoryWatchlist'] = __DIR__ . '/CategoryWatchlist.i18n.php';
$wgAutoloadClasses['CategoryWatchlistHooks'] = __DIR__ . '/CategoryWatchlist.hooks.php';
$wgAutoloadClasses['SpecialWatchCategory'] = __DIR__ . '/special/SpecialWatchCategory.php';
$wgSpecialPages['WatchCategory'] = 'SpecialWatchCategory';
$wgHooks['PageContentSave'][] = 'CategoryWatchlistHooks::onPreSave';