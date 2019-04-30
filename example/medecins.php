<?php

namespace AdimeoDataSuite\Client\Example;

use AdimeoDataSuite\Client\AdsClient;
use AdimeoDataSuite\Client\Facet\Facet;
use AdimeoDataSuite\Client\Processor\SearchResultsProcessor;


ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('xdebug.var_display_max_depth', 15);

require_once '../vendor/autoload.php';

$searchClient = new AdsClient('http://ads.adimeo.eu', 'ods_medecins.medecin', 'french');
// $results = $searchClient->search();var_dump($results);exit;
$searchClient->addFacet((new Facet('etab.lib.raw'))->setSticky());
$searchClient->addFacet(new Facet('consultation.est_prive'));
$searchClient->addFacet(new Facet('metier.raw'));

$results = $searchClient->search();

$context = $searchClient->getContext();

$resultsProcessor = new SearchResultsProcessor($searchClient, 10, 'Voir plus');
$resultsProcessor->process($results);


?><!DOCTYPE html>
<html>

<head>
    <title>Adimeo Data Suite search</title>
    <style>
        * {
            box-sizing: border-box;
        }

        form > p {
            display: inline-block;
        }

        h2 {
            margin: 0.75rem 0;
        }

        .facet ul li a.inactive:before {
            font-family: "Courier New";
            content: "[ ]";
        }

        .facet ul li a.active:before {
            font-family: "Courier New";
            content: "[X]";
        }

        .facet {
            margin-bottom: 1rem;
        }

        .search-results textarea {
            width: 100%;
            height: 100px;
        }

        .facets, .results {
            float: left;
            padding: 0 1rem;
        }

        .api-url {
            clear: both;
        }

        .facets {
            width: 300px;
        }

        .results {
            width: calc(100% - 300px);
        }

        ul, li {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .facets li {
            margin: 0.25rem 0;
        }

        .pager li {
            display: inline-block;
            margin: 1rem 1rem 1rem 0;
        }

        .search-results li {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<form method="get">
    <h2>Recherche</h2>
    <p>
        <label for="query">Query: </label>
        <input type="text" name="query" id="query" value="<?php print $context->getQuery(); ?>"/>
    </p>
    <p>
        <input type="submit" value="Search"/>
    </p>
</form>
<h2>Résultats</h2>
<div class="facets">
    <?php
    foreach ($resultsProcessor->getFacetProcessors() as $facetRender) {
        ?>
        <div class="facet facet-<?php print $facetRender->getFacetName(); ?>">
            <strong class="facet-name">Facet <?php print $facetRender->getFacetName(); ?></strong>
            <ul">
                <?php
                foreach ($facetRender->getValues() as $value) {
                    ?>
                    <li><?php print $resultsProcessor->renderLinkToHTML($value, $searchClient); ?></li>
                    <?php
                }
                ?>
            </ul>
            <?php
            if ($facetRender->hasMoreValues()) {
                ?>
                <?php print $resultsProcessor->renderLinkToHTML($facetRender->getMoreValuesLink(), $searchClient); ?>
                <?php
            }
            ?>
        </div>
        <?php
    }
    ?>
</div>
<div class="results">
    <h3><?php print $results['hits']['total']; ?> result(s) for your search</h3>
    <ul class="search-results">
        <?php
        foreach ($resultsProcessor->getResultProcessors() as $resultProcessor) {
            ?>
            <li>
                <div class="result-id"><strong>Doc ID = <?php print $resultProcessor->getId(); ?></strong></div>
                <div class="source">
                    <textarea><?php print json_encode($resultProcessor->getSource(), JSON_PRETTY_PRINT); ?></textarea>
                </div>
            </li>
            <?php
        }
        ?>
    </ul>
    <?php
    if ($resultsProcessor->getPagerProcessor() != null) {
        ?>
        <ul class="pager">
            <?php
            foreach ($resultsProcessor->getPagerProcessor()->getLinks() as $link) {
                ?>
                <li><?php print $resultsProcessor->renderLinkToHTML($link, $searchClient); ?></li>
                <?php
            }
            ?>
        </ul>
        <?php
    }
    ?>
</div>
<p class="api-url">API URL = <a href="<?php print htmlentities($searchClient->buildAPIUrl($context)); ?>"
                                target="_blank"><?php print $searchClient->buildAPIUrl($context); ?></a></p>
</body>
</html>
