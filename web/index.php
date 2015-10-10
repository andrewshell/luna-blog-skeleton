<?php
$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__.'/../vendor/autoload.php';

// Dependencies
$parsedown = new Parsedown();
$twig = new Twig_Environment(
    new Twig_Loader_Filesystem(__DIR__ . '/../content/_templates'),
    ['debug' => true]
);
$twig->addExtension(new Twig_Extension_Debug());
$twig->addExtension(new Twig_Extensions_Extension_Text());
$yaml = new Symfony\Component\Yaml\Parser();

// Parsers
$archiveTwigParser = new Luna\Parser\Twig($twig, 'archive.twig.html');
$postTwigParser = new Luna\Parser\Twig($twig, 'post.twig.html');
$layoutTwigParser = new Luna\Parser\Twig($twig, 'layout.twig.html');
$markdownParser = new Luna\Parser\Markdown($parsedown);
$yamlFrontMatterParser = new Luna\Parser\YamlFrontMatter($yaml);

// Sources
$blogSource = new Luna\Source\File(__DIR__ . '/../content/_posts', '.md');

$pageSource = new Luna\Source\File(__DIR__ . '/../content', '.md');
$pageSource->exclude('/_posts/');
$pageSource->exclude('/_templates/');

// Renderers
$blogArchiveRenderer = new Luna\Renderer\BlogArchive($blogSource);
$blogArchiveRenderer->addParser($yamlFrontMatterParser);
$blogArchiveRenderer->addParser($markdownParser);
$blogArchiveRenderer->addParser($archiveTwigParser);

$blogPostRenderer = new Luna\Renderer\BlogPost($blogSource);
$blogPostRenderer->addParser($yamlFrontMatterParser);
$blogPostRenderer->addParser($markdownParser);
$blogPostRenderer->addParser($postTwigParser);

$pageRenderer = new Luna\Renderer\Page($pageSource);
$pageRenderer->addParser($yamlFrontMatterParser);
$pageRenderer->addParser($markdownParser);
$pageRenderer->addParser($layoutTwigParser);


$aggregateRenderer = new Luna\Renderer\Aggregate();
$aggregateRenderer->addRenderer($blogArchiveRenderer);
$aggregateRenderer->addRenderer($blogPostRenderer);
$aggregateRenderer->addRenderer($pageRenderer);

echo $aggregateRenderer->render($_SERVER['REQUEST_URI']);
