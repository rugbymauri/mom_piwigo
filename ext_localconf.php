<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Mom.' . $_EXTKEY,
	'Piwigo',
	array(
		'Piwigo' => 'show',
		
	),
	// non-cacheable actions
	array(
		'Piwigo' => 'show',
		
	)
);
