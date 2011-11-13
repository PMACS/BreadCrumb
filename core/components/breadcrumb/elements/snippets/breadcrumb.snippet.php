<?php
/**
 * BreadCrumb
 * Copyright 2011 Benjamin Vauchel <contact@omycode.fr>
 *
 * BreadCrumb is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * BreadCrumb is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * BreadCrumb; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package breadcrumb
 * @author Benjamin Vauchel <contact@omycode.fr>
 *
 * @version Version 1.0.0 Beta-1
 * 13/11/11
 *
 * Breadcrumb is a snippet for MODx Revolution, inspired by the Jared's BreadCrumbs snippet.
 * It will create a breadcrumb navigation for the current resource or a specific resource.
 *
 * Optional properties:
 *
 * @property resourceId - (int) Resource ID whose breadcrumb is created; [Default value : current resource id].
 * @property maxCrumbs - (int) Max crumbs shown in breadcrumb. Max delimiter template can be customize with property maxCrumbTpl ; [Default value : 100].
 * @property showHidden - (bool) Show hidden resources in breadcrumb; [Default value : true].
 * @property showContainer - (bool) Show container resources in breadcrumb; [Default value : true].
 * @property showUnPub - (bool) Show unpublished resources in breadcrumb; [Default value : true].
 * @property showCurrentCrumb - (bool) Show current resource as a crumb; [Default value : true].
 * @property showBreadCrumbAtHome - (bool) Show BreadCrumb on the home page; [Default value : true].
 * @property showHomeCrumb - (bool) Show the home page as a crumb; [Default value : false].
 * @property direction - (string) Direction or breadcrumb : Left To Right (ltr) or Right To Left (rtl) for Arabic language for example; [Default value : ltr].
 *
 * Templates :
 *
 * @property containerTpl - (string) Container template for BreadCrumb; [Default value : BreadCrumbContainerTpl].
 * @property currentCrumbTpl - (string) Current crumb template for BreadCrumb; [Default value : BreadCrumbCurrentCrumbTpl].
 * @property linkCrumbTpl - (string) Default crumb template for BreadCrumb; [Default value : BreadCrumbLinkCrumbTpl].
 * @property maxCrumbTpl - (string) Max delimiter crumb template for BreadCrumb; [Default value : BreadCrumbMaxCrumbTpl].
 */

// Script Properties
$resourceId = !empty($resourceId) ? $resourceId : $modx->resource->get('id');
$maxCrumbs = !empty($maxCrumbs) ? abs(intval($maxCrumbs)) : 100;
$showHidden = isset($showHidden) ? (bool)$showHidden : true;
$showContainer = isset($showContainer) ? (bool)$showContainer : true;
$showUnPub = isset($showUnPub) ? (bool)$showUnPub : true;
$showCurrentCrumb = isset($showCurrentCrumb) ? (bool)$showCurrentCrumb : true;
$showBreadCrumbAtHome = isset($showBreadCrumbAtHome) ? (bool)$showBreadCrumbAtHome : true;
$showHomeCrumb = isset($showHomeCrumb) ? (bool)$showHomeCrumb : false;
$direction = !empty($direction) && $direction == 'rtl' ? 'rtl' : 'ltr';
$containerTpl = !empty($containerTpl) ? $containerTpl : 'BreadCrumbContainerTpl';
$currentCrumbTpl = !empty($currentCrumbTpl) ? $currentCrumbTpl : 'BreadCrumbCurrentCrumbTpl';
$linkCrumbTpl = !empty($linkCrumbTpl) ? $linkCrumbTpl : 'BreadCrumbLinkCrumbTpl';
$maxCrumbTpl = !empty($maxCrumbTpl) ? $maxCrumbTpl : 'BreadCrumbMaxCrumbTpl';

// Output variable
$output = '';

// We check if current resource is the homepage and if breadcrumb is shown for the homepage
if(!$showBreadCrumbAtHome && $modx->resource->get('id') == $modx->getOption('site_start'))
{
	return '';
}

// We get all the crumbs
$crumbs = array();
$crumbsCount = 0;
while($resourceId != 0 && $crumbsCount < $maxCrumbs)
{
	$resource = $modx->getObject('modResource', $resourceId);
	
	// We check the conditions to show crumb
	if(
		(($resourceId == $modx->getOption('site_start') && $showHomeCrumb) || $resourceId != $modx->getOption('site_start'))  // ShowHomeCrumb
		&& (($resource->get('hidemenu') && $showHidden) || !$resource->get('hidemenu'))										// ShowHidden
		&& (($resource->get('isfolder') && $showContainer) || !$resource->get('isfolder'))									// ShowContainer
		&& ((!$resource->get('published') && $showUnPub) || $resource->get('published')) 									// UnPub
		&& (($resourceId == $modx->resource->get('id') && $showCurrentCrumb) || $resourceId != $modx->resource->get('id'))  // ShowCurrent
	)
	{
		// If is LTR direction, we push resource at the beginning of the array 
		if($direction == 'ltr')
		{
		    array_unshift($crumbs, $resource); 
		}
		// Else we push it at the end
		else
		{
		    $crumbs[] = $resource;
		}
		
		$crumbsCount++;
	}
	$resourceId = $resource->get('parent');
}

// We build the output of crumbs
foreach($crumbs as $key => $resource)
{	
	// Current crumb tpl ?
	if($showCurrentCrumb && ($resource->get('id') == $modx->resource->get('id')))
	{
		$tpl = $currentCrumbTpl;
	}
	// or default crumb tpl ?
	else
	{
		$tpl = $linkCrumbTpl;
	}
	// Output
	$output .= $modx->getChunk($tpl, $resource->toArray());
}

// We add the max delimiter to the crumbs output, if the max limit was reached
if($crumbsCount == $maxCrumbs)
{
	// If is LTR direction, we push the max delimiter at the beginning of the crumbs
	if($direction == 'ltr')
	{
		$output = $modx->getChunk($maxCrumbTpl).$output;
	}
	// Else we push it at the end
	else
	{
		$output .= $modx->getChunk($maxCrumbTpl);
	}
}

// We build the breadcrumb output
$output = $modx->getChunk($containerTpl, array(
	'crumbs' => $output,
));

return $output;

?>
